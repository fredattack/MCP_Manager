<?php

namespace App\Services;

use App\Enums\OrganizationRole;
use App\Enums\OrganizationStatus;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrganizationService
{
    /**
     * Create a new organization
     */
    public function createOrganization(array $data, User $owner): Organization
    {
        return DB::transaction(function () use ($data, $owner) {
            // Generate slug if not provided
            if (! isset($data['slug'])) {
                $data['slug'] = $this->generateSlug($data['name']);
            }

            // Create organization
            $organization = Organization::create([
                'name' => $data['name'],
                'slug' => $data['slug'],
                'owner_id' => $owner->id,
                'billing_email' => $data['billing_email'] ?? $owner->email,
                'status' => OrganizationStatus::Active,
                'max_members' => $data['max_members'] ?? 5,
                'settings' => $data['settings'] ?? [],
            ]);

            // Add owner as member
            OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $owner->id,
                'role' => OrganizationRole::Owner,
                'permissions' => [],
                'joined_at' => now(),
            ]);

            // Log activity
            UserActivityLog::create([
                'user_id' => $owner->id,
                'action' => 'organization_created',
                'entity_type' => 'Organization',
                'entity_id' => $organization->id,
                'new_values' => [
                    'name' => $organization->name,
                    'slug' => $organization->slug,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $organization->load('owner', 'members');
        });
    }

    /**
     * Update an organization
     */
    public function updateOrganization(Organization $organization, array $data, User $user): Organization
    {
        return DB::transaction(function () use ($organization, $data, $user) {
            $oldValues = $organization->only(['name', 'billing_email', 'max_members', 'status']);

            $organization->update([
                'name' => $data['name'] ?? $organization->name,
                'billing_email' => $data['billing_email'] ?? $organization->billing_email,
                'max_members' => $data['max_members'] ?? $organization->max_members,
                'status' => $data['status'] ?? $organization->status,
                'settings' => $data['settings'] ?? $organization->settings,
            ]);

            // Log activity
            UserActivityLog::create([
                'user_id' => $user->id,
                'action' => 'organization_updated',
                'entity_type' => 'Organization',
                'entity_id' => $organization->id,
                'old_values' => $oldValues,
                'new_values' => $organization->only(['name', 'billing_email', 'max_members', 'status']),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $organization->fresh(['owner', 'members']);
        });
    }

    /**
     * Delete (soft delete) an organization
     */
    public function deleteOrganization(Organization $organization, User $user): bool
    {
        return DB::transaction(function () use ($organization, $user) {
            // Soft delete by setting status
            $organization->update(['status' => OrganizationStatus::Deleted]);

            // Log activity
            UserActivityLog::create([
                'user_id' => $user->id,
                'action' => 'organization_deleted',
                'entity_type' => 'Organization',
                'entity_id' => $organization->id,
                'old_values' => ['status' => OrganizationStatus::Active->value],
                'new_values' => ['status' => OrganizationStatus::Deleted->value],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return true;
        });
    }

    /**
     * Add a member to an organization
     */
    public function addMember(Organization $organization, User $user, OrganizationRole $role, User $invitedBy): OrganizationMember
    {
        return DB::transaction(function () use ($organization, $user, $role, $invitedBy) {
            // Check if can add member
            if (! $organization->canAddMember()) {
                throw new \Exception("Organization has reached maximum member limit ({$organization->max_members})");
            }

            // Create member
            $member = OrganizationMember::create([
                'organization_id' => $organization->id,
                'user_id' => $user->id,
                'role' => $role,
                'permissions' => [],
                'invited_by' => $invitedBy->id,
                'joined_at' => now(),
            ]);

            // Log activity
            UserActivityLog::create([
                'user_id' => $invitedBy->id,
                'target_user_id' => $user->id,
                'action' => 'member_added_to_org',
                'entity_type' => 'OrganizationMember',
                'entity_id' => $member->id,
                'new_values' => [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'user_name' => $user->name,
                    'role' => $role->value,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $member->load('user', 'organization', 'inviter');
        });
    }

    /**
     * Remove a member from an organization
     */
    public function removeMember(OrganizationMember $member, User $removedBy): bool
    {
        return DB::transaction(function () use ($member, $removedBy) {
            $organization = $member->organization;
            $user = $member->user;

            // Cannot remove owner
            if ($member->isOwner()) {
                throw new \Exception('Cannot remove organization owner');
            }

            // Revoke all active leases for this user in this organization
            $organization->leases()
                ->where('user_id', $user->id)
                ->where('status', \App\Enums\LeaseStatus::Active)
                ->each(function ($lease) {
                    $lease->revoke('Member removed from organization');
                });

            // Log activity
            UserActivityLog::create([
                'user_id' => $removedBy->id,
                'target_user_id' => $user->id,
                'action' => 'member_removed_from_org',
                'entity_type' => 'OrganizationMember',
                'entity_id' => $member->id,
                'old_values' => [
                    'organization_id' => $organization->id,
                    'organization_name' => $organization->name,
                    'user_name' => $user->name,
                    'role' => $member->role->value,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Delete member
            return $member->delete();
        });
    }

    /**
     * Update member role
     */
    public function updateMemberRole(OrganizationMember $member, OrganizationRole $newRole, User $updatedBy): OrganizationMember
    {
        return DB::transaction(function () use ($member, $newRole, $updatedBy) {
            // Cannot change owner role
            if ($member->isOwner()) {
                throw new \Exception('Cannot change owner role');
            }

            $oldRole = $member->role;
            $member->update(['role' => $newRole]);

            // Log activity
            UserActivityLog::create([
                'user_id' => $updatedBy->id,
                'target_user_id' => $member->user_id,
                'action' => 'member_role_updated',
                'entity_type' => 'OrganizationMember',
                'entity_id' => $member->id,
                'old_values' => ['role' => $oldRole->value],
                'new_values' => ['role' => $newRole->value],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            return $member->fresh(['user', 'organization']);
        });
    }

    /**
     * Get user's organizations with stats
     */
    public function getUserOrganizations(User $user): array
    {
        $memberships = OrganizationMember::where('user_id', $user->id)
            ->with(['organization' => function ($query) {
                $query->where('status', '!=', OrganizationStatus::Deleted)
                    ->withCount(['members', 'credentials', 'leases']);
            }])
            ->get();

        return $memberships->map(function ($membership) {
            $org = $membership->organization;

            return [
                'id' => $org->id,
                'name' => $org->name,
                'slug' => $org->slug,
                'status' => $org->status,
                'role' => $membership->role,
                'permissions' => $membership->role->permissions(),
                'is_owner' => $membership->isOwner(),
                'members_count' => $org->members_count,
                'credentials_count' => $org->credentials_count,
                'leases_count' => $org->leases_count,
                'max_members' => $org->max_members,
                'billing_email' => $org->billing_email,
                'created_at' => $org->created_at,
            ];
        })->toArray();
    }

    /**
     * Generate unique slug from name
     */
    private function generateSlug(string $name): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (Organization::where('slug', $slug)->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    /**
     * Check if user can manage organization
     */
    public function canManage(User $user, Organization $organization): bool
    {
        $member = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        return $member && ($member->isOwner() || $member->isAdmin());
    }

    /**
     * Check if user can manage members
     */
    public function canManageMembers(User $user, Organization $organization): bool
    {
        $member = OrganizationMember::where('organization_id', $organization->id)
            ->where('user_id', $user->id)
            ->first();

        return $member && $member->canManageMembers();
    }
}
