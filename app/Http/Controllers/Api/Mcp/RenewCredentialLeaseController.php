<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\CredentialLease;
use App\Models\UserActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RenewCredentialLeaseController extends Controller
{
    public function __invoke(Request $request, string $leaseId): JsonResponse
    {
        $validated = $request->validate([
            'ttl' => 'integer|min:60|max:86400',
        ]);

        $lease = CredentialLease::where('lease_id', $leaseId)->firstOrFail();

        if (! $lease->canRenew()) {
            return response()->json([
                'error' => 'Lease cannot be renewed',
                'reason' => $this->getRenewalBlockReason($lease),
                'status' => $lease->status->value,
                'renewal_count' => $lease->renewal_count,
                'max_renewals' => $lease->max_renewals,
            ], 403);
        }

        $ttl = $validated['ttl'] ?? 3600;
        $renewed = $lease->renew($ttl);

        if (! $renewed) {
            return response()->json([
                'error' => 'Failed to renew lease',
            ], 500);
        }

        $this->logLeaseRenewal($request, $lease);

        return response()->json([
            'lease_id' => $lease->lease_id,
            'expires_at' => $lease->expires_at->toIso8601String(),
            'renewal_count' => $lease->renewal_count,
            'max_renewals' => $lease->max_renewals,
            'renewals_remaining' => $lease->max_renewals - $lease->renewal_count,
        ]);
    }

    private function getRenewalBlockReason(CredentialLease $lease): string
    {
        if (! $lease->renewable) {
            return 'Lease is not renewable';
        }

        if ($lease->status->value !== 'active') {
            return "Lease status is {$lease->status->value}";
        }

        if ($lease->renewal_count >= $lease->max_renewals) {
            return 'Maximum renewals reached';
        }

        if ($lease->isExpired()) {
            return 'Lease has expired';
        }

        return 'Unknown reason';
    }

    private function logLeaseRenewal(Request $request, CredentialLease $lease): void
    {
        UserActivityLog::create([
            'user_id' => $lease->user_id,
            'action' => 'lease_renewed',
            'entity_type' => 'CredentialLease',
            'entity_id' => $lease->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'lease_id' => $lease->lease_id,
                'new_expires_at' => $lease->expires_at->toIso8601String(),
                'renewal_count' => $lease->renewal_count,
            ],
        ]);
    }
}
