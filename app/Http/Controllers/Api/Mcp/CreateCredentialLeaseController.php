<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\CredentialLease;
use App\Models\UserActivityLog;
use App\Services\CredentialResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CreateCredentialLeaseController extends Controller
{
    public function __construct(
        private readonly CredentialResolutionService $credentialResolver
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'services' => 'required|array|min:1',
            'services.*' => 'required|string',
            'ttl' => 'integer|min:60|max:86400',
            'server_id' => 'nullable|string|max:100',
            'client_info' => 'nullable|string',
        ]);

        $userId = $validated['user_id'];
        $services = $validated['services'];
        $ttl = $validated['ttl'] ?? 3600;

        $accessValidation = $this->credentialResolver->validateServiceAccess($userId, $services);

        if (! $accessValidation['valid']) {
            return response()->json([
                'error' => 'Missing credentials for requested services',
                'missing_services' => $accessValidation['missing'],
                'available_services' => $accessValidation['available'],
            ], 422);
        }

        $resolved = $this->credentialResolver->resolveMultipleCredentials($userId, $services);

        $primaryOrgId = $this->getPrimaryOrganizationId($resolved['sources']);
        $credentialScope = $this->determineCredentialScope($resolved['sources']);

        $lease = CredentialLease::create([
            'user_id' => $userId,
            'organization_id' => $primaryOrgId,
            'server_id' => $validated['server_id'] ?? null,
            'services' => $services,
            'credential_scope' => $credentialScope,
            'included_org_credentials' => $resolved['sources'],
            'expires_at' => now()->addSeconds($ttl),
            'renewable' => true,
            'status' => 'active',
            'client_info' => $validated['client_info'] ?? null,
            'client_ip' => $request->ip(),
        ]);

        $lease->setEncryptedCredentials($resolved['credentials']);
        $lease->save();

        $this->logLeaseCreation($request, $lease, $resolved['sources']);

        return response()->json([
            'lease_id' => $lease->lease_id,
            'credentials' => $resolved['credentials'],
            'credential_sources' => $resolved['sources'],
            'expires_at' => $lease->expires_at->toIso8601String(),
            'renewable' => $lease->renewable,
            'max_renewals' => $lease->max_renewals,
        ], 201);
    }

    private function getPrimaryOrganizationId(array $sources): ?int
    {
        foreach ($sources as $source) {
            if ($source['scope'] === 'organization' && $source['organization_id']) {
                return $source['organization_id'];
            }
        }

        return null;
    }

    private function determineCredentialScope(array $sources): string
    {
        $hasPersonal = false;
        $hasOrganization = false;

        foreach ($sources as $source) {
            if ($source['scope'] === 'personal') {
                $hasPersonal = true;
            }

            if ($source['scope'] === 'organization') {
                $hasOrganization = true;
            }
        }

        if ($hasPersonal && $hasOrganization) {
            return 'mixed';
        }

        if ($hasOrganization) {
            return 'organization';
        }

        return 'personal';
    }

    private function logLeaseCreation(Request $request, CredentialLease $lease, array $sources): void
    {
        UserActivityLog::create([
            'user_id' => $lease->user_id,
            'action' => 'lease_created',
            'entity_type' => 'CredentialLease',
            'entity_id' => $lease->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'lease_id' => $lease->lease_id,
                'services' => $lease->services,
                'credential_sources' => $sources,
                'server_id' => $lease->server_id,
                'expires_at' => $lease->expires_at->toIso8601String(),
                'ttl_seconds' => $lease->expires_at->diffInSeconds(now()),
            ],
        ]);
    }
}
