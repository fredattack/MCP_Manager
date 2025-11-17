<?php

namespace App\Http\Controllers\Settings;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Services\OrganizationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {}

    /**
     * Display a listing of user's organizations
     */
    public function index(Request $request): Response
    {
        $query = Organization::query()
            ->with(['owner', 'members.user'])
            ->withCount(['members', 'credentials', 'leases'])
            ->whereHas('members', function ($q) use ($request) {
                $q->where('user_id', $request->user()->id);
            });

        // Filters
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($role = $request->get('role')) {
            $query->whereHas('members', function ($q) use ($request, $role) {
                $q->where('user_id', $request->user()->id)
                    ->where('role', $role);
            });
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        $organizations = $query->paginate($request->integer('per_page', 15))->withQueryString();

        // Add user's role to each organization
        $organizations->getCollection()->transform(function ($org) use ($request) {
            $member = $org->members->firstWhere('user_id', $request->user()->id);
            $org->user_role = $member?->role;
            $org->user_permissions = $member?->role->permissions() ?? [];
            $org->is_owner = $member?->isOwner() ?? false;

            return $org;
        });

        // Calculate stats
        $allOrganizations = $this->organizationService->getUserOrganizations($request->user());
        $stats = [
            'total' => count($allOrganizations),
            'owner' => collect($allOrganizations)->where('is_owner', true)->count(),
            'active_members' => collect($allOrganizations)->sum('members_count'),
            'shared_credentials' => collect($allOrganizations)->sum('credentials_count'),
        ];

        return Inertia::render('settings/Organizations/Index', [
            'organizations' => $organizations,
            'stats' => $stats,
            'filters' => $request->only(['search', 'status', 'role', 'sort_by', 'sort_order']),
            'statusOptions' => array_map(
                fn ($status) => ['value' => $status->value, 'label' => $status->name],
                OrganizationStatus::cases()
            ),
            'roleOptions' => array_map(
                fn ($role) => ['value' => $role->value, 'label' => $role->displayName()],
                OrganizationRole::cases()
            ),
        ]);
    }

    /**
     * Show the form for creating a new organization
     */
    public function create(): Response
    {
        return Inertia::render('settings/Organizations/Create');
    }

    /**
     * Store a newly created organization
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:organizations,slug', 'regex:/^[a-z0-9-]+$/'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'max_members' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $organization = $this->organizationService->createOrganization(
            $validated,
            $request->user()
        );

        return redirect()
            ->route('settings.organizations.show', $organization)
            ->with('success', 'Organization created successfully');
    }

    /**
     * Display the specified organization
     */
    public function show(Request $request, Organization $organization): Response
    {
        // Check if user is member
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member) {
            abort(403, 'You are not a member of this organization');
        }

        $organization->load([
            'owner',
            'members.user',
            'members.inviter',
            'credentials' => function ($query) {
                $query->where('scope', 'organization');
            },
            'invitations' => function ($query) {
                $query->where('accepted_at', null)
                    ->where('expires_at', '>', now());
            },
            'leases' => function ($query) {
                $query->where('status', 'active')
                    ->with('user')
                    ->latest()
                    ->limit(10);
            },
        ]);

        // Calculate stats
        $stats = [
            'total_members' => $organization->members()->count(),
            'total_credentials' => $organization->credentials()->where('scope', 'organization')->count(),
            'active_leases' => $organization->leases()->where('status', 'active')->count(),
            'pending_invitations' => $organization->invitations()
                ->where('accepted_at', null)
                ->where('expires_at', '>', now())
                ->count(),
        ];

        return Inertia::render('settings/Organizations/Show', [
            'organization' => $organization,
            'stats' => $stats,
            'userRole' => $member->role,
            'userPermissions' => $member->role->permissions(),
            'isOwner' => $member->isOwner(),
            'canManageMembers' => $member->canManageMembers(),
            'canManageCredentials' => $member->canManageCredentials(),
        ]);
    }

    /**
     * Show the form for editing the specified organization
     */
    public function edit(Request $request, Organization $organization): Response
    {
        // Check permissions
        if (! $this->organizationService->canManage($request->user(), $organization)) {
            abort(403, 'You do not have permission to edit this organization');
        }

        $organization->load('owner');

        return Inertia::render('settings/Organizations/Edit', [
            'organization' => $organization,
            'statusOptions' => array_map(
                fn ($status) => ['value' => $status->value, 'label' => $status->name],
                OrganizationStatus::cases()
            ),
        ]);
    }

    /**
     * Update the specified organization
     */
    public function update(Request $request, Organization $organization): RedirectResponse
    {
        // Check permissions
        if (! $this->organizationService->canManage($request->user(), $organization)) {
            abort(403, 'You do not have permission to update this organization');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'billing_email' => ['nullable', 'email', 'max:255'],
            'max_members' => ['nullable', 'integer', 'min:1', 'max:100'],
            'status' => ['nullable', 'in:'.implode(',', array_column(OrganizationStatus::cases(), 'value'))],
        ]);

        $this->organizationService->updateOrganization(
            $organization,
            $validated,
            $request->user()
        );

        return redirect()
            ->route('settings.organizations.show', $organization)
            ->with('success', 'Organization updated successfully');
    }

    /**
     * Remove the specified organization (soft delete)
     */
    public function destroy(Request $request, Organization $organization): RedirectResponse
    {
        // Only owner can delete
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member || ! $member->isOwner()) {
            abort(403, 'Only the organization owner can delete the organization');
        }

        $this->organizationService->deleteOrganization($organization, $request->user());

        return redirect()
            ->route('settings.organizations.index')
            ->with('success', 'Organization deleted successfully');
    }
}
