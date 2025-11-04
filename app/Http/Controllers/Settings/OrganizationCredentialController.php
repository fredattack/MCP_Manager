<?php

namespace App\Http\Controllers\Settings;

use App\Enums\CredentialScope;
use App\Enums\IntegrationStatus;
use App\Http\Controllers\Controller;
use App\Models\IntegrationAccount;
use App\Models\Organization;
use App\Models\UserActivityLog;
use App\Services\OrganizationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;

class OrganizationCredentialController extends Controller
{
    public function __construct(
        protected OrganizationService $organizationService
    ) {}

    /**
     * List all credentials for an organization
     */
    public function index(Request $request, Organization $organization): JsonResponse
    {
        // Check if user is member
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member) {
            return response()->json(['error' => 'You are not a member of this organization'], 403);
        }

        $credentials = $organization->credentials()
            ->where('scope', CredentialScope::Organization)
            ->with('createdBy')
            ->get()
            ->map(function ($credential) use ($member) {
                // Determine if user can access this credential
                $sharedWith = $credential->shared_with ?? [];
                $canAccess = $this->canAccessCredential($member, $credential);

                return [
                    'id' => $credential->id,
                    'type' => $credential->type,
                    'status' => $credential->status,
                    'scope' => $credential->scope,
                    'shared_with' => $sharedWith,
                    'can_access' => $canAccess,
                    'created_by' => $credential->createdBy ? [
                        'id' => $credential->createdBy->id,
                        'name' => $credential->createdBy->name,
                    ] : null,
                    'created_at' => $credential->created_at,
                    'updated_at' => $credential->updated_at,
                ];
            });

        return response()->json([
            'credentials' => $credentials,
            'can_manage' => $member->canManageCredentials(),
        ]);
    }

    /**
     * Create a new organization credential
     */
    public function store(Request $request, Organization $organization): JsonResponse
    {
        // Check permissions
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member || ! $member->canManageCredentials()) {
            return response()->json(['error' => 'You do not have permission to add credentials'], 403);
        }

        $validated = $request->validate([
            'type' => ['required', 'string', 'max:50'],
            'access_token' => ['required', 'string'],
            'meta' => ['nullable', 'array'],
            'shared_with' => ['required', 'array'],
            'shared_with.*' => ['string'],
        ]);

        try {
            $credential = DB::transaction(function () use ($organization, $validated, $request) {
                $credential = IntegrationAccount::create([
                    'organization_id' => $organization->id,
                    'user_id' => null, // No user_id for organization credentials
                    'type' => $validated['type'],
                    'access_token' => Crypt::encryptString($validated['access_token']),
                    'meta' => $validated['meta'] ?? [],
                    'status' => IntegrationStatus::Active,
                    'scope' => CredentialScope::Organization,
                    'shared_with' => $validated['shared_with'],
                    'created_by' => $request->user()->id,
                ]);

                // Log activity
                UserActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'org_credential_created',
                    'entity_type' => 'IntegrationAccount',
                    'entity_id' => $credential->id,
                    'new_values' => [
                        'organization_id' => $organization->id,
                        'organization_name' => $organization->name,
                        'type' => $credential->type,
                        'shared_with' => $validated['shared_with'],
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                return $credential;
            });

            return response()->json([
                'message' => 'Credential created successfully',
                'credential' => [
                    'id' => $credential->id,
                    'type' => $credential->type,
                    'status' => $credential->status,
                    'scope' => $credential->scope,
                    'shared_with' => $credential->shared_with,
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Update an organization credential (sharing settings)
     */
    public function update(Request $request, Organization $organization, IntegrationAccount $credential): JsonResponse
    {
        // Check permissions
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member || ! $member->canManageCredentials()) {
            return response()->json(['error' => 'You do not have permission to update credentials'], 403);
        }

        // Verify credential belongs to this organization
        if ($credential->organization_id !== $organization->id) {
            return response()->json(['error' => 'Credential does not belong to this organization'], 404);
        }

        $validated = $request->validate([
            'shared_with' => ['required', 'array'],
            'shared_with.*' => ['string'],
            'status' => ['nullable', 'in:active,inactive,error'],
            'access_token' => ['nullable', 'string'],
            'meta' => ['nullable', 'array'],
        ]);

        try {
            DB::transaction(function () use ($credential, $validated, $request) {
                $oldValues = [
                    'shared_with' => $credential->shared_with,
                    'status' => $credential->status->value,
                ];

                $updateData = [
                    'shared_with' => $validated['shared_with'],
                ];

                if (isset($validated['status'])) {
                    $updateData['status'] = $validated['status'];
                }

                if (isset($validated['access_token'])) {
                    $updateData['access_token'] = Crypt::encryptString($validated['access_token']);
                }

                if (isset($validated['meta'])) {
                    $updateData['meta'] = $validated['meta'];
                }

                $credential->update($updateData);

                // Log activity
                UserActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'org_credential_updated',
                    'entity_type' => 'IntegrationAccount',
                    'entity_id' => $credential->id,
                    'old_values' => $oldValues,
                    'new_values' => [
                        'shared_with' => $validated['shared_with'],
                        'status' => $credential->status->value,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);
            });

            return response()->json([
                'message' => 'Credential updated successfully',
                'credential' => [
                    'id' => $credential->id,
                    'shared_with' => $credential->shared_with,
                    'status' => $credential->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Delete an organization credential
     */
    public function destroy(Request $request, Organization $organization, IntegrationAccount $credential): JsonResponse
    {
        // Check permissions
        $member = $organization->members()->where('user_id', $request->user()->id)->first();

        if (! $member || ! $member->canManageCredentials()) {
            return response()->json(['error' => 'You do not have permission to delete credentials'], 403);
        }

        // Verify credential belongs to this organization
        if ($credential->organization_id !== $organization->id) {
            return response()->json(['error' => 'Credential does not belong to this organization'], 404);
        }

        try {
            DB::transaction(function () use ($credential, $request, $organization) {
                // Revoke all active leases using this credential
                $organization->leases()
                    ->where('status', 'active')
                    ->each(function ($lease) {
                        $credentials = $lease->getDecryptedCredentials();
                        if (in_array($credential->type, array_keys($credentials))) {
                            $lease->revoke('Organization credential deleted');
                        }
                    });

                // Log activity
                UserActivityLog::create([
                    'user_id' => $request->user()->id,
                    'action' => 'org_credential_deleted',
                    'entity_type' => 'IntegrationAccount',
                    'entity_id' => $credential->id,
                    'old_values' => [
                        'type' => $credential->type,
                        'shared_with' => $credential->shared_with,
                    ],
                    'ip_address' => $request->ip(),
                    'user_agent' => $request->userAgent(),
                ]);

                $credential->delete();
            });

            return response()->json([
                'message' => 'Credential deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        }
    }

    /**
     * Check if member can access a credential
     */
    private function canAccessCredential($member, IntegrationAccount $credential): bool
    {
        $sharedWith = $credential->shared_with ?? [];

        // All members can access
        if (in_array('all_members', $sharedWith)) {
            return true;
        }

        // Admin-only access
        if (in_array('admins_only', $sharedWith)) {
            return $member->isAdmin() || $member->isOwner();
        }

        // Specific user access
        if (in_array("user:{$member->user_id}", $sharedWith)) {
            return true;
        }

        // Owner always has access
        if ($member->isOwner()) {
            return true;
        }

        return false;
    }
}
