<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Enum;

class IntegrationsController extends Controller
{
    /**
     * Get all integration accounts for the authenticated user.
     */
    public function index(): JsonResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json(['message' => 'Not authenticated', 'guard' => Auth::getDefaultDriver(), 'session_id' => session()->getId()], 401);
        }

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        $integrations = $user->integrationAccounts;

        return response()->json($integrations);
    }

    /**
     * Store a new integration account.
     */
    public function store(Request $request): JsonResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        $validated = $request->validate([
            'type' => ['required', new Enum(IntegrationType::class)],
            'access_token' => 'required|string',
            'meta' => 'nullable|array',
        ]);

        /** @var \App\Models\User|null $user */
        $user = Auth::user();
        if (! $user) {
            return response()->json(['message' => 'User not found'], 401);
        }

        // Check if user already has an active integration of this type
        $existingIntegration = $user->integrationAccounts()
            ->where('type', $validated['type'])
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        if ($existingIntegration) {
            return response()->json([
                'message' => 'You already have an active integration of this type.',
            ], 422);
        }

        $integrationAccount = $user->integrationAccounts()->create([
            'type' => $validated['type'],
            'access_token' => $validated['access_token'],
            'meta' => $validated['meta'] ?? null,
            'status' => IntegrationStatus::ACTIVE,
        ]);

        return response()->json($integrationAccount, 201);
    }

    /**
     * Update an existing integration account.
     */
    public function update(Request $request, IntegrationAccount $integrationAccount): JsonResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Ensure the integration belongs to the authenticated user
        if ($integrationAccount->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validated = $request->validate([
            'access_token' => 'sometimes|required|string',
            'meta' => 'nullable|array',
            'status' => ['sometimes', 'required', new Enum(IntegrationStatus::class)],
        ]);

        $integrationAccount->update($validated);

        return response()->json($integrationAccount);
    }

    /**
     * Delete an integration account.
     */
    public function destroy(IntegrationAccount $integrationAccount): JsonResponse
    {
        // Check if user is authenticated
        if (! Auth::check()) {
            return response()->json(['message' => 'Not authenticated'], 401);
        }

        // Ensure the integration belongs to the authenticated user
        if ($integrationAccount->user_id !== Auth::id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $integrationAccount->delete();

        return response()->json(['message' => 'Integration deleted successfully']);
    }
}
