<?php

namespace App\Http\Controllers\Api\Mcp;

use App\Http\Controllers\Controller;
use App\Services\CredentialResolutionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GetUserCredentialsController extends Controller
{
    public function __construct(
        private readonly CredentialResolutionService $credentialResolver
    ) {}

    public function __invoke(Request $request, int $userId): JsonResponse
    {
        $service = $request->query('service');

        if ($service) {
            $credential = $this->credentialResolver->resolveCredential($userId, $service);

            if (! $credential) {
                return response()->json([
                    'error' => 'Credential not found',
                    'service' => $service,
                ], 404);
            }

            return response()->json([
                'service' => $service,
                'credential' => $credential['data'],
                'source' => $credential['source'],
            ]);
        }

        $availableServices = $this->credentialResolver->getAvailableServices($userId);

        return response()->json([
            'user_id' => $userId,
            'available_services' => $availableServices,
            'count' => count($availableServices),
        ]);
    }
}
