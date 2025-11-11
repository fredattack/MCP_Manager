<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use App\Models\Organization;
use Illuminate\Http\Request;
use Inertia\Inertia;

/**
 * Integration Manager Controller
 *
 * Manages user and organization integration accounts.
 * Communication with MCP Server is handled via Credential Lease system.
 */
class IntegrationManagerController extends Controller
{
    /**
     * Display integrations dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get all user's integrations (personal + organization)
        $allIntegrations = IntegrationAccount::where('user_id', $user->id)->get();

        // Format for frontend
        $integrations = $this->formatIntegrationsForDashboard($allIntegrations);

        return Inertia::render('IntegrationManager/Dashboard', [
            'integrations' => $integrations,
        ]);
    }

    /**
     * Show configuration form for a specific service
     */
    public function configure(string $service)
    {
        $user = auth()->user();

        // Find existing integration for this service
        $integration = IntegrationAccount::where('user_id', $user->id)
            ->where('type', IntegrationType::from($service))
            ->first();

        // Add placeholder for existing token (security: show last 3 chars only)
        $integrationData = $integration?->toArray();
        if ($integrationData && ! empty($integrationData['access_token'])) {
            $integrationData['has_token'] = true;
            // Show last 3 characters only
            $token = $integrationData['access_token'];
            $lastThree = substr($token, -3);
            $integrationData['token_placeholder'] = str_repeat('•', max(0, strlen($token) - 3)).$lastThree;
            unset($integrationData['access_token']); // Never send full token to frontend
        }

        return Inertia::render('IntegrationManager/Configure', [
            'service' => $service,
            'integration' => $integrationData,
        ]);
    }

    /**
     * Store integration configuration
     */
    public function store(Request $request, string $service)
    {
        $user = auth()->user();
        $type = IntegrationType::from($service);

        // Check if integration already exists (for update)
        $existingIntegration = IntegrationAccount::where('user_id', $user->id)
            ->where('type', $type)
            ->first();

        // Validate based on service type
        $credentials = $this->validateCredentials($service, $request, $existingIntegration !== null);

        // Get organization if provided
        $organizationId = $request->input('organization_id');
        $organization = null;

        if ($organizationId) {
            $organization = Organization::findOrFail($organizationId);

            // Check if user is member and has permission to manage credentials
            if (! $organization->hasMember($user)) {
                return back()->withErrors(['error' => 'You are not a member of this organization']);
            }

            // TODO: Check if user has permission to manage credentials in this org
        }

        // Prepare data for update
        $updateData = [
            'meta' => $credentials['meta'] ?? [],
            'status' => IntegrationStatus::ACTIVE,
            'scope' => $organizationId ? 'organization' : 'personal',
        ];

        // Only update token if provided (user changed it)
        if (! empty($credentials['token'])) {
            $updateData['access_token'] = $credentials['token'];
        }

        // Create or update integration
        $integration = IntegrationAccount::updateOrCreate(
            [
                'user_id' => $user->id,
                'type' => $type,
                'organization_id' => $organizationId,
            ],
            $updateData
        );

        return redirect()->route('integrations.manager.index')
            ->with('success', ucfirst($service).' integration configured successfully');
    }

    /**
     * Test integration connection
     *
     * Note: Testing is done via MCP Server through Credential Lease system
     */
    public function test(string $service)
    {
        // For now, just verify the integration exists
        $user = auth()->user();
        $type = IntegrationType::from($service);

        $integration = IntegrationAccount::where('user_id', $user->id)
            ->where('type', $type)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        if (! $integration) {
            return response()->json([
                'success' => false,
                'message' => 'Integration not configured',
            ], 404);
        }

        // TODO: Implement actual test via MCP Server if needed
        return response()->json([
            'success' => true,
            'message' => ucfirst($service).' integration is configured and active',
        ]);
    }

    /**
     * Remove integration
     */
    public function destroy(string $service)
    {
        $user = auth()->user();
        $type = IntegrationType::from($service);

        $deleted = IntegrationAccount::where('user_id', $user->id)
            ->where('type', $type)
            ->delete();

        if ($deleted) {
            return redirect()->route('integrations.manager.index')
                ->with('success', ucfirst($service).' integration removed successfully');
        }

        return back()->withErrors(['error' => 'Failed to remove integration']);
    }

    /**
     * Format integrations for dashboard display
     */
    private function formatIntegrationsForDashboard($allIntegrations): array
    {
        // Define all available services
        $availableServices = ['todoist', 'notion', 'jira', 'sentry', 'confluence', 'calendar'];

        $result = [];

        foreach ($availableServices as $service) {
            // Find integration for this service
            $integration = $allIntegrations->firstWhere('type', $service);

            // Determine status
            $configured = $integration !== null;
            $status = 'not_configured';
            $lastSync = null;

            if ($integration && $integration->status === IntegrationStatus::ACTIVE) {
                $status = 'active';
                $lastSync = $integration->updated_at;
            } elseif ($configured) {
                $status = 'inactive';
            }

            $result[$service] = [
                'configured' => $configured,
                'status' => $status,
                'last_sync' => $lastSync?->toIso8601String(),
                'health' => $status === 'active' ? 'healthy' : 'unknown',
                'error' => null,
            ];
        }

        return $result;
    }

    /**
     * Validate credentials based on service type
     */
    private function validateCredentials(string $service, Request $request, bool $isUpdate = false): array
    {
        $tokenRequired = $isUpdate ? 'nullable' : 'required';

        return match ($service) {
            'todoist' => [
                'token' => $request->validate(['api_token' => "{$tokenRequired}|string|min:32"])['api_token'] ?? null,
                'meta' => [],
            ],

            'notion' => [
                'token' => $request->validate(['api_key' => "{$tokenRequired}|string|starts_with:secret_"])['api_key'] ?? null,
                'meta' => ['database_id' => $request->input('database_id')],
            ],

            'jira' => [
                'token' => $request->validate([
                    'api_token' => "{$tokenRequired}|string",
                    'domain' => 'required|url',
                    'email' => 'required|email',
                ])['api_token'] ?? null,
                'meta' => [
                    'url' => $request->input('domain'),
                    'email' => $request->input('email'),
                    'cloud' => true,
                ],
            ],

            'sentry' => [
                'token' => $request->validate(['auth_token' => "{$tokenRequired}|string"])['auth_token'] ?? null,
                'meta' => [
                    'org_slug' => $request->input('org_slug', ''),
                    'base_url' => $request->input('base_url', 'https://sentry.io/api/0'),
                ],
            ],

            'confluence' => [
                'token' => $request->validate([
                    'api_token' => "{$tokenRequired}|string",
                    'domain' => 'required|url',
                    'email' => 'required|email',
                ])['api_token'] ?? null,
                'meta' => [
                    'url' => $request->input('domain'),
                    'email' => $request->input('email'),
                ],
            ],

            'calendar' => [
                'token' => $request->validate(['client_id' => "{$tokenRequired}|string"])['client_id'] ?? null,
                'meta' => [
                    'client_id' => $request->input('client_id'),
                    'requires_oauth_flow' => true,
                ],
            ],

            default => throw new \InvalidArgumentException('Unsupported service: '.$service),
        };
    }
}
