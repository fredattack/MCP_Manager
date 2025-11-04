<?php

namespace App\Http\Controllers\Settings;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationInvitation;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class OrganizationInvitationController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {}

    /**
     * List all pending invitations for an organization
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to view invitations'], 403);
        }

        $invitations = $organization->invitations()
            ->with(['inviter', 'organization'])
            ->where('accepted_at', null)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($invitation) {
                return [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'token' => $invitation->token,
                    'expires_at' => $invitation->expires_at,
                    'is_expired' => $invitation->isExpired(),
                    'invited_by' => [
                        'id' => $invitation->inviter->id,
                        'name' => $invitation->inviter->name,
                    ],
                    'created_at' => $invitation->created_at,
                ];
            });

        return response()->json(['invitations' => $invitations]);
    }

    /**
     * Create a new invitation
     */
    public function store(Request $request, Organization $organization): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to invite members'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'role' => ['required', 'in:'.implode(',', array_column(OrganizationRole::cases(), 'value'))],
        ]);

        // Check if user exists and is already a member
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && $organization->hasMember($existingUser)) {
            return response()->json([
                'error' => 'User is already a member of this organization',
            ], 422);
        }

        // Check if invitation already exists and is still valid
        $existingInvitation = OrganizationInvitation::where('organization_id', $organization->id)
            ->where('email', $validated['email'])
            ->where('accepted_at', null)
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvitation) {
            return response()->json([
                'error' => 'An invitation has already been sent to this email',
            ], 422);
        }

        // Check if can add member
        if (! $organization->canAddMember()) {
            return response()->json([
                'error' => "Organization has reached maximum member limit ({$organization->max_members})",
            ], 422);
        }

        try {
            $invitation = DB::transaction(function () use ($organization, $validated, $request) {
                $invitation = OrganizationInvitation::create([
                    'organization_id' => $organization->id,
                    'email' => $validated['email'],
                    'role' => OrganizationRole::from($validated['role']),
                    'invited_by' => $request->user()->id,
                    'expires_at' => now()->addDays(7),
                ]);

                // Log activity
                UserActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'organization_invitation_sent',
                    'entity_type' => 'OrganizationInvitation',
                    'entity_id' => $invitation->id,
                    'new_values' => [
                        'organization_name' => $organization->name,
                        'email' => $invitation->email,
                        'role' => $invitation->role->value,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                // TODO: Send invitation email
                // Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation));

                return $invitation;
            });

            return response()->json([
                'message' => 'Invitation sent successfully',
                'invitation' => [
                    'id' => $invitation->id,
                    'email' => $invitation->email,
                    'role' => $invitation->role,
                    'expires_at' => $invitation->expires_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Resend an invitation
     */
    public function resend(Request $request, Organization $organization, OrganizationInvitation $invitation): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to resend invitations'], 403);
        }

        // Verify invitation belongs to this organization
        if ($invitation->organization_id !== $organization->id) {
            return response()->json(['error' => 'Invitation does not belong to this organization'], 404);
        }

        // Check if already accepted
        if ($invitation->isAccepted()) {
            return response()->json(['error' => 'Invitation has already been accepted'], 422);
        }

        try {
            // Extend expiration
            $invitation->update(['expires_at' => now()->addDays(7)]);

            // TODO: Resend invitation email
            // Mail::to($invitation->email)->send(new OrganizationInvitationMail($invitation));

            // Log activity
            UserActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'organization_invitation_resent',
                'entity_type' => 'OrganizationInvitation',
                'entity_id' => $invitation->id,
                'new_values' => [
                    'email' => $invitation->email,
                    'new_expires_at' => $invitation->expires_at,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'message' => 'Invitation resent successfully',
                'invitation' => [
                    'id' => $invitation->id,
                    'expires_at' => $invitation->expires_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Revoke an invitation
     */
    public function destroy(Request $request, Organization $organization, OrganizationInvitation $invitation): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to revoke invitations'], 403);
        }

        // Verify invitation belongs to this organization
        if ($invitation->organization_id !== $organization->id) {
            return response()->json(['error' => 'Invitation does not belong to this organization'], 404);
        }

        try {
            // Log activity
            UserActivityLog::create([
                'user_id' => $request->user()->id,
                'action' => 'organization_invitation_revoked',
                'entity_type' => 'OrganizationInvitation',
                'entity_id' => $invitation->id,
                'old_values' => [
                    'email' => $invitation->email,
                    'role' => $invitation->role->value,
                ],
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            $invitation->delete();

            return response()->json([
                'message' => 'Invitation revoked successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Accept an invitation (public endpoint)
     */
    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = OrganizationInvitation::where('token', $token)
            ->where('accepted_at', null)
            ->firstOrFail();

        // Check if expired
        if ($invitation->isExpired()) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation has expired');
        }

        // Check if user email matches invitation
        if ($request->user()->email !== $invitation->email) {
            return redirect()->route('dashboard')
                ->with('error', 'This invitation was sent to a different email address');
        }

        // Check if already member
        if ($invitation->organization->hasMember($request->user())) {
            return redirect()->route('settings.organizations.show', $invitation->organization)
                ->with('info', 'You are already a member of this organization');
        }

        try {
            DB::transaction(function () use ($invitation, $request) {
                // Add user as member
                $this->organizationService->addMember(
                    $invitation->organization,
                    $request->user(),
                    $invitation->role,
                    $invitation->inviter
                );

                // Mark invitation as accepted
                $invitation->markAsAccepted();

                // Log activity
                UserActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'organization_invitation_accepted',
                    'entity_type' => 'OrganizationInvitation',
                    'entity_id' => $invitation->id,
                    'new_values' => [
                        'organization_id' => $invitation->organization_id,
                        'organization_name' => $invitation->organization->name,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });

            return redirect()->route('settings.organizations.show', $invitation->organization)
                ->with('success', "You have joined {$invitation->organization->name}");
        } catch (\Exception $e) {
            return redirect()->route('dashboard')
                ->with('error', $e->getMessage());
        }
    }

    /**
     * Show invitation acceptance page
     */
    public function show(string $token): Response
    {
        $invitation = OrganizationInvitation::where('token', $token)
            ->where('accepted_at', null)
            ->with(['organization', 'inviter'])
            ->firstOrFail();

        return Inertia::render('settings/Organizations/AcceptInvitation', [
            'invitation' => [
                'token' => $invitation->token,
                'email' => $invitation->email,
                'role' => $invitation->role,
                'is_expired' => $invitation->isExpired(),
                'expires_at' => $invitation->expires_at,
                'organization' => [
                    'id' => $invitation->organization->id,
                    'name' => $invitation->organization->name,
                    'slug' => $invitation->organization->slug,
                ],
                'inviter' => [
                    'name' => $invitation->inviter->name,
                ],
            ],
        ]);
    }
}
