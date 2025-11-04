<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Models\CredentialLease;
use App\Models\UserActivityLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RevokeCredentialLeaseController extends Controller
{
    public function __invoke(Request $request, string $leaseId): JsonResponse
    {
        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        $lease = CredentialLease::where('lease_id', $leaseId)->firstOrFail();

        $reason = $validated['reason'] ?? 'Manual revocation';
        $revoked = $lease->revoke($reason);

        if (! $revoked) {
            return response()->json([
                'error' => 'Failed to revoke lease',
            ], 500);
        }

        $this->logLeaseRevocation($request, $lease, $reason);

        return response()->json([
            'success' => true,
            'lease_id' => $lease->lease_id,
            'revoked_at' => $lease->revoked_at->toIso8601String(),
            'reason' => $lease->revocation_reason,
        ]);
    }

    private function logLeaseRevocation(Request $request, CredentialLease $lease, string $reason): void
    {
        UserActivityLog::create([
            'user_id' => $lease->user_id,
            'action' => 'lease_revoked',
            'entity_type' => 'CredentialLease',
            'entity_id' => $lease->id,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'new_values' => [
                'lease_id' => $lease->lease_id,
                'revoked_at' => $lease->revoked_at->toIso8601String(),
                'reason' => $reason,
            ],
        ]);
    }
}
