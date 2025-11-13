<?php

namespace App\Services;

use App\Enums\CredentialScope;
use App\Enums\IntegrationStatus;
use App\Models\IntegrationAccount;
use App\Models\OrganizationMember;

class CredentialResolutionService
{
    /**
     * Resolve credentials for a user and service
     * Priority: Personal > Organization
     */
    public function resolveCredential(int $userId, string $service): ?array
    {
        $personal = $this->getPersonalCredential($userId, $service);

        if ($personal) {
            return $this->formatCredential($personal, CredentialScope::Personal);
        }

        $orgCredential = $this->getOrganizationCredential($userId, $service);

        if ($orgCredential) {
            return $this->formatCredential($orgCredential, CredentialScope::Organization);
        }

        return null;
    }

    /**
     * Resolve multiple services for a user
     */
    public function resolveMultipleCredentials(int $userId, array $services): array
    {
        $credentials = [];
        $sources = [];

        foreach ($services as $service) {
            $credential = $this->resolveCredential($userId, $service);

            if ($credential) {
                $credentials[$service] = $credential['data'];
                $sources[$service] = $credential['source'];
            }
        }

        return [
            'credentials' => $credentials,
            'sources' => $sources,
        ];
    }

    /**
     * Get personal credential for user
     */
    private function getPersonalCredential(int $userId, string $service): ?IntegrationAccount
    {
        return IntegrationAccount::where('user_id', $userId)
            ->where('type', $service)
            ->where('scope', CredentialScope::Personal)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();
    }

    /**
     * Get organization credential for user
     */
    private function getOrganizationCredential(int $userId, string $service): ?IntegrationAccount
    {
        $memberships = OrganizationMember::where('user_id', $userId)
            ->with('organization')
            ->get();

        foreach ($memberships as $membership) {
            if (! $membership->organization->isActive()) {
                continue;
            }

            $credential = IntegrationAccount::where('organization_id', $membership->organization_id)
                ->where('type', $service)
                ->where('scope', CredentialScope::Organization)
                ->where('status', IntegrationStatus::ACTIVE)
                ->first();

            if ($credential && $this->canAccessCredential($membership, $credential)) {
                $credential->organization_name = $membership->organization->name;

                return $credential;
            }
        }

        return null;
    }

    /**
     * Check if member can access organization credential
     */
    private function canAccessCredential(OrganizationMember $membership, IntegrationAccount $credential): bool
    {
        $sharedWith = $credential->shared_with ?? [];

        if (empty($sharedWith) || in_array('all_members', $sharedWith)) {
            return true;
        }

        if (in_array('admins_only', $sharedWith)) {
            return $membership->isAdmin();
        }

        if (in_array("user:{$membership->user_id}", $sharedWith)) {
            return true;
        }

        return false;
    }

    /**
     * Format credential with metadata
     */
    private function formatCredential(IntegrationAccount $credential, CredentialScope $scope): array
    {
        return [
            'data' => [
                'access_token' => $credential->access_token,
                'meta' => $credential->meta,
                'type' => $credential->type->value,
            ],
            'source' => [
                'scope' => $scope->value,
                'organization_id' => $credential->organization_id,
                'organization_name' => $credential->organization_name ?? null,
                'credential_id' => $credential->id,
            ],
        ];
    }

    /**
     * Get all available services for a user
     */
    public function getAvailableServices(int $userId): array
    {
        $personalServices = IntegrationAccount::where('user_id', $userId)
            ->where('scope', CredentialScope::Personal)
            ->where('status', IntegrationStatus::ACTIVE)
            ->pluck('type')
            ->map(fn ($type) => $type->value)
            ->toArray();

        $orgServices = $this->getOrganizationServices($userId);

        return array_unique(array_merge($personalServices, $orgServices));
    }

    /**
     * Get organization services accessible by user
     */
    private function getOrganizationServices(int $userId): array
    {
        $memberships = OrganizationMember::where('user_id', $userId)
            ->with('organization')
            ->get();

        $services = [];

        foreach ($memberships as $membership) {
            if (! $membership->organization->isActive()) {
                continue;
            }

            $orgCredentials = IntegrationAccount::where('organization_id', $membership->organization_id)
                ->where('scope', CredentialScope::Organization)
                ->where('status', IntegrationStatus::ACTIVE)
                ->get();

            foreach ($orgCredentials as $credential) {
                if ($this->canAccessCredential($membership, $credential)) {
                    $services[] = $credential->type->value;
                }
            }
        }

        return array_unique($services);
    }

    /**
     * Validate that user has access to all requested services
     */
    public function validateServiceAccess(int $userId, array $services): array
    {
        $available = $this->getAvailableServices($userId);
        $missing = array_diff($services, $available);

        return [
            'valid' => empty($missing),
            'available' => $available,
            'missing' => array_values($missing),
        ];
    }
}
