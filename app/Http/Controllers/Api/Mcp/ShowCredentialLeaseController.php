<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\CredentialLease;
use Illuminate\Http\JsonResponse;

class ShowCredentialLeaseController extends Controller
{
    public function __invoke(string $leaseId): JsonResponse
    {
        $lease = CredentialLease::where('lease_id', $leaseId)
            ->with(['user:id,name,email', 'organization:id,name'])
            ->firstOrFail();

        return response()->json([
            'lease_id' => $lease->lease_id,
            'user_id' => $lease->user_id,
            'user_email' => $lease->user->email,
            'organization' => $lease->organization ? [
                'id' => $lease->organization->id,
                'name' => $lease->organization->name,
            ] : null,
            'server_id' => $lease->server_id,
            'services' => $lease->services,
            'credential_scope' => $lease->credential_scope,
            'expires_at' => $lease->expires_at->toIso8601String(),
            'status' => $lease->status->value,
            'renewable' => $lease->renewable,
            'renewal_count' => $lease->renewal_count,
            'max_renewals' => $lease->max_renewals,
            'renewals_remaining' => $lease->max_renewals - $lease->renewal_count,
            'is_expired' => $lease->isExpired(),
            'is_active' => $lease->isActive(),
            'can_renew' => $lease->canRenew(),
            'created_at' => $lease->created_at->toIso8601String(),
            'last_renewed_at' => $lease->last_renewed_at?->toIso8601String(),
            'revoked_at' => $lease->revoked_at?->toIso8601String(),
            'revocation_reason' => $lease->revocation_reason,
        ]);
    }
}
