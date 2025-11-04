<?php

namespace App\Http\Controllers\Settings;

use App\Enums\OrganizationRole;
use App\Http\Controllers\Controller;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrganizationMemberController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {}

    /**
     * List all members of an organization
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        // Check if user is member
        $currentMember = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $currentMember) {
            return response()->json(['error' => 'You are not a member of this organization'], 403);
        }

        $members = $organization->members()
            ->with(['user', 'inviter'])
            ->orderBy('joined_at', 'desc')
            ->get()
            ->map(function ($member) {
                return [
                    'id' => $member->id,
                    'user' => [
                        'id' => $member->user->id,
                        'name' => $member->user->name,
                        'email' => $member->user->email,
                    ],
                    'role' => $member->role,
                    'permissions' => $member->role->permissions(),
                    'is_owner' => $member->isOwner(),
                    'can_manage_members' => $member->canManageMembers(),
                    'can_manage_credentials' => $member->canManageCredentials(),
                    'joined_at' => $member->joined_at,
                    'invited_by' => $member->inviter ? [
                        'id' => $member->inviter->id,
                        'name' => $member->inviter->name,
                    ] : null,
                ];
            });

        return response()->json([
            'members' => $members,
            'can_manage' => $currentMember->canManageMembers(),
        ]);
    }

    /**
     * Add a member to the organization
     */
    public function store(Request $request, Organization $organization): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to add members'], 403);
        }

        $validated = $request->validate([
            'email' => ['required', 'email', 'exists:users,email'],
            'role' => ['required', 'in:'.implode(',', array_column(OrganizationRole::cases(), 'value'))],
        ]);

        // Find user
        $user = User::where('email', $validated['email'])->firstOrFail();

        // Check if already member
        if ($organization->hasMember($user)) {
            return response()->json([
                'error' => 'User is already a member of this organization',
            ], 422);
        }

        try {
            $member = $this->organizationService->addMember(
                $organization,
                $user,
                OrganizationRole::from($validated['role']),
                $request->user()
            );

            return response()->json([
                'message' => 'Member added successfully',
                'member' => [
                    'id' => $member->id,
                    'user' => [
                        'id' => $member->user->id,
                        'name' => $member->user->name,
                        'email' => $member->user->email,
                    ],
                    'role' => $member->role,
                    'permissions' => $member->role->permissions(),
                    'joined_at' => $member->joined_at,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update a member's role
     */
    public function update(Request $request, Organization $organization, OrganizationMember $member): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to update members'], 403);
        }

        // Verify member belongs to this organization
        if ($member->organization_id !== $organization->id) {
            return response()->json(['error' => 'Member does not belong to this organization'], 404);
        }

        $validated = $request->validate([
            'role' => ['required', 'in:'.implode(',', array_column(OrganizationRole::cases(), 'value'))],
        ]);

        try {
            $updatedMember = $this->organizationService->updateMemberRole(
                $member,
                OrganizationRole::from($validated['role']),
                $request->user()
            );

            return response()->json([
                'message' => 'Member role updated successfully',
                'member' => [
                    'id' => $updatedMember->id,
                    'role' => $updatedMember->role,
                    'permissions' => $updatedMember->role->permissions(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Remove a member from the organization
     */
    public function destroy(Request $request, Organization $organization, OrganizationMember $member): JsonResponse
    {
        // Check permissions
        if (! $this->organizationService->canManageMembers($request->user(), $organization)) {
            return response()->json(['error' => 'You do not have permission to remove members'], 403);
        }

        // Verify member belongs to this organization
        if ($member->organization_id !== $organization->id) {
            return response()->json(['error' => 'Member does not belong to this organization'], 404);
        }

        // Can't remove yourself unless there's another owner/admin
        if ($member->user_id === $request->user()->id) {
            $otherAdmins = $organization->members()
                ->where('user_id', '!=', $request->user()->id)
                ->whereIn('role', [OrganizationRole::Owner, OrganizationRole::Admin])
                ->exists();

            if (! $otherAdmins) {
                return response()->json([
                    'error' => 'Cannot remove yourself as the last admin. Transfer ownership first.',
                ], 422);
            }
        }

        try {
            $this->organizationService->removeMember($member, $request->user());

            return response()->json([
                'message' => 'Member removed successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }
}
