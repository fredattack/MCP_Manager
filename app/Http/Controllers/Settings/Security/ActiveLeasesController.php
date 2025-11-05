<?php

namespace App\Http\Controllers\Settings\Security;

use App\Enums\IntegrationType;
use App\Http\Controllers\Controller;
use App\Models\CredentialLease;
use App\Models\Organization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ActiveLeasesController extends Controller
{
    /**
     * Display active credential leases dashboard
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        // Get user's leases with organization relationship
        $leasesQuery = CredentialLease::query()
            ->with(['organization'])
            ->where('user_id', $user->id)
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($search = $request->get('search')) {
            $leasesQuery->where(function ($query) use ($search) {
                $query->where('lease_id', 'like', "%{$search}%")
                    ->orWhere('server_id', 'like', "%{$search}%");
            });
        }

        if ($status = $request->get('status')) {
            $leasesQuery->where('status', $status);
        }

        if ($organizationFilter = $request->get('organization')) {
            if ($organizationFilter === 'personal') {
                $leasesQuery->whereNull('organization_id');
            } elseif ($organizationFilter !== 'all') {
                $leasesQuery->where('organization_id', $organizationFilter);
            }
        }

        if ($service = $request->get('service')) {
            $leasesQuery->whereJsonContains('services', $service);
        }

        $leases = $leasesQuery->get();

        // Calculate stats
        $allLeases = CredentialLease::where('user_id', $user->id)->get();

        $stats = [
            'active_leases' => $allLeases->where('status.value', 'active')->count(),
            'total_services' => $allLeases->pluck('services')
                ->flatten()
                ->unique()
                ->count(),
            'expiring_soon' => CredentialLease::where('user_id', $user->id)
                ->expiringSoon(10)
                ->count(),
            'organizations_with_leases' => $allLeases->whereNotNull('organization_id')
                ->pluck('organization_id')
                ->unique()
                ->count(),
        ];

        // Get organizations for filter dropdown
        $organizations = Organization::query()
            ->whereHas('members', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->select(['id', 'name'])
            ->get();

        // Get available services for filter dropdown
        $availableServices = array_map(
            fn ($type) => ['value' => $type->value, 'label' => $type->displayName()],
            IntegrationType::cases()
        );

        return Inertia::render('settings/Security/ActiveLeases', [
            'leases' => $leases,
            'stats' => $stats,
            'organizations' => $organizations,
            'available_services' => $availableServices,
            'filters' => $request->only(['search', 'status', 'organization', 'service']),
        ]);
    }

    /**
     * Revoke a credential lease
     */
    public function revoke(Request $request, CredentialLease $lease): RedirectResponse
    {
        $user = $request->user();

        // Check if user owns the lease
        if ($lease->user_id !== $user->id) {
            abort(403, 'You do not have permission to revoke this lease');
        }

        // Validate request
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Revoke the lease
        $reason = $validated['reason'] ?? 'Manually revoked by user';
        $lease->revoke($reason);

        return redirect()
            ->route('settings.security.active-leases')
            ->with('success', 'Lease revoked successfully');
    }
}
