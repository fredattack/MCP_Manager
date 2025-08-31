<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Services\TodoistService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class TodoistIntegrationController extends Controller
{
    public function __construct(
        private readonly TodoistService $todoistService
    ) {}

    /**
     * Show the Todoist integration setup page
     */
    public function show(Request $request): Response
    {
        $integration = $request->user()
            ->integrationAccounts()
            ->where('type', IntegrationType::TODOIST)
            ->first();

        return Inertia::render('integrations/todoist-setup', [
            'integration' => $integration ? [
                'id' => $integration->id,
                'status' => $integration->status,
                'connected_at' => $integration->created_at,
                'meta' => $integration->meta,
            ] : null,
        ]);
    }

    /**
     * Connect Todoist account using API token
     */
    public function connect(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'api_token' => ['required', 'string', 'min:40'],
        ]);

        try {
            // Test the token by fetching user info
            $userInfo = $this->todoistService->validateToken($validated['api_token']);
            
            if (!$userInfo) {
                return redirect()->back()->withErrors([
                    'api_token' => 'Invalid API token. Please check and try again.',
                ]);
            }

            // Store or update the integration
            $integration = $request->user()
                ->integrationAccounts()
                ->updateOrCreate(
                    ['type' => IntegrationType::TODOIST],
                    [
                        'access_token' => Crypt::encryptString($validated['api_token']),
                        'status' => IntegrationStatus::ACTIVE,
                        'meta' => [
                            'user_id' => $userInfo['id'] ?? null,
                            'email' => $userInfo['email'] ?? null,
                            'full_name' => $userInfo['full_name'] ?? null,
                            'avatar_url' => $userInfo['avatar_big'] ?? null,
                        ],
                    ]
                );

            Log::info('Todoist integration connected', [
                'user_id' => $request->user()->id,
                'integration_id' => $integration->id,
            ]);

            return redirect()
                ->route('integrations.todoist')
                ->with('success', 'Todoist account connected successfully!');
        } catch (\Exception $e) {
            Log::error('Failed to connect Todoist integration', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()->back()->withErrors([
                'api_token' => 'Failed to connect to Todoist. Please try again.',
            ]);
        }
    }

    /**
     * Disconnect Todoist account
     */
    public function disconnect(Request $request): RedirectResponse
    {
        $integration = $request->user()
            ->integrationAccounts()
            ->where('type', IntegrationType::TODOIST)
            ->first();

        if ($integration) {
            $integration->update([
                'status' => IntegrationStatus::INACTIVE,
                'access_token' => null,
                'meta' => array_merge($integration->meta ?? [], [
                    'disconnected_at' => now()->toIso8601String(),
                ]),
            ]);

            Log::info('Todoist integration disconnected', [
                'user_id' => $request->user()->id,
                'integration_id' => $integration->id,
            ]);
        }

        return redirect()
            ->route('integrations.todoist.setup')
            ->with('success', 'Todoist account disconnected successfully.');
    }

    /**
     * Test the current connection
     */
    public function test(Request $request): RedirectResponse
    {
        $integration = $request->user()
            ->integrations()
            ->where('type', IntegrationType::TODOIST)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        if (!$integration) {
            return redirect()->back()->withErrors([
                'connection' => 'No active Todoist integration found.',
            ]);
        }

        try {
            $token = Crypt::decryptString($integration->access_token);
            $userInfo = $this->todoistService->validateToken($token);
            
            if ($userInfo) {
                // Update meta with latest info
                $integration->update([
                    'meta' => array_merge($integration->meta ?? [], [
                        'last_tested_at' => now()->toIso8601String(),
                        'user_id' => $userInfo['id'] ?? null,
                        'email' => $userInfo['email'] ?? null,
                        'full_name' => $userInfo['full_name'] ?? null,
                    ]),
                ]);

                return redirect()->back()->with(
                    'success', 
                    'Connection test successful! Your Todoist account is working properly.'
                );
            }

            $integration->update(['status' => IntegrationStatus::ERROR]);
            return redirect()->back()->withErrors([
                'connection' => 'Connection test failed. Please reconnect your account.',
            ]);
        } catch (\Exception $e) {
            Log::error('Todoist connection test failed', [
                'user_id' => $request->user()->id,
                'error' => $e->getMessage(),
            ]);

            $integration->update(['status' => IntegrationStatus::ERROR]);
            return redirect()->back()->withErrors([
                'connection' => 'Connection test failed. Please check your integration.',
            ]);
        }
    }
}