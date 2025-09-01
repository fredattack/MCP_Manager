<?php

namespace App\Http\Controllers;

use App\Models\McpIntegration;
use App\Services\McpServerManager;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class McpIntegrationController extends Controller
{
    public function __construct(
        private McpServerManager $mcpManager
    ) {}

    /**
     * Display the integrations dashboard
     */
    public function index(): Response|RedirectResponse
    {
        // Check if MCP server is configured in environment
        if (config('services.mcp.server_url') && config('services.mcp.jwt_token')) {
            // Redirect to new integration manager
            return redirect()->route('integrations.manager.index');
        }
        
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;
        
        if (!$server) {
            return Inertia::render('Mcp/NoServerConfigured');
        }

        // Get integrations from database
        $localIntegrations = McpIntegration::where('user_id', $user->id)
            ->with('mcpServer')
            ->get()
            ->map(fn($i) => $i->getStatusDetails());

        // Try to get real-time status from MCP server
        $remoteStatus = [];
        try {
            $remoteStatus = $this->mcpManager->getIntegrationsStatus($user);
        } catch (\Exception $e) {
            Log::warning('Could not fetch integrations status from MCP server', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // Merge local and remote data
        $integrations = $this->mergeIntegrationData($localIntegrations, $remoteStatus);

        // Get server status
        $serverStatus = $this->mcpManager->getServerStatus($user);

        return Inertia::render('Mcp/Dashboard', [
            'integrations' => $integrations,
            'serverStatus' => $serverStatus,
        ]);
    }

    /**
     * Show configuration form for a specific service
     */
    public function configure(string $service): Response
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $integration = McpIntegration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        return Inertia::render('Mcp/ConfigureIntegration', [
            'service' => $service,
            'integration' => $integration ? [
                'id' => $integration->id,
                'enabled' => $integration->enabled,
                'status' => $integration->status,
                'credentials_valid' => $integration->credentials_valid,
                'last_sync' => $integration->last_sync_at?->toIso8601String(),
                'error_message' => $integration->error_message,
            ] : null,
            'serviceConfig' => $this->getServiceConfig($service),
        ]);
    }

    /**
     * Store integration configuration
     */
    public function store(Request $request, string $service): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $server = $user->mcpServer;

        if (!$server || !$server->isActive()) {
            return back()->withErrors(['error' => 'MCP server is not connected']);
        }

        // Validate based on service requirements
        $credentials = $this->validateServiceCredentials($request, $service);

        try {
            DB::beginTransaction();

            // Configure integration via MCP server
            $this->mcpManager->configureIntegration($user, $service, $credentials);

            // Create or update local integration record
            $integration = McpIntegration::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'service_name' => $service,
                ],
                [
                    'mcp_server_id' => $server->id,
                    'enabled' => true,
                    'status' => 'connecting',
                    'credentials_valid' => true,
                    'config' => [
                        'configured_at' => now()->toIso8601String(),
                    ],
                ]
            );

            DB::commit();

            return redirect()->route('mcp.dashboard')
                ->with('success', "Integration '{$service}' configured successfully");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to configure integration', [
                'user_id' => $user->id,
                'service' => $service,
                'error' => $e->getMessage(),
            ]);

            return back()->withErrors(['error' => 'Failed to configure integration: ' . $e->getMessage()]);
        }
    }

    /**
     * Test integration connectivity
     */
    public function test(string $service): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $result = $this->mcpManager->testIntegration($user, $service);

        // Update integration status based on test result
        if ($result['success']) {
            McpIntegration::where('user_id', $user->id)
                ->where('service_name', $service)
                ->first()
                ?->markAsSynced();
        } else {
            McpIntegration::where('user_id', $user->id)
                ->where('service_name', $service)
                ->first()
                ?->markAsFailed($result['error'] ?? 'Test failed');
        }

        return response()->json($result);
    }

    /**
     * Toggle integration enabled status
     */
    public function toggle(string $service): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $integration = McpIntegration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if (!$integration) {
            return response()->json([
                'success' => false,
                'error' => 'Integration not found',
            ], 404);
        }

        $integration->enabled = !$integration->enabled;
        $integration->status = $integration->enabled ? 'connecting' : 'inactive';
        $integration->save();

        // Notify MCP server about the change
        try {
            $this->mcpManager->configureIntegration(
                $user,
                $service,
                ['enabled' => $integration->enabled]
            );
        } catch (\Exception $e) {
            Log::warning('Failed to notify MCP server about integration toggle', [
                'service' => $service,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'enabled' => $integration->enabled,
        ]);
    }

    /**
     * Get real-time integration status
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        
        try {
            $integrations = $this->mcpManager->getIntegrationsStatus($user);
            return response()->json($integrations);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to get integrations status',
            ], 500);
        }
    }

    /**
     * Delete integration configuration
     */
    public function destroy(string $service): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();
        $integration = McpIntegration::where('user_id', $user->id)
            ->where('service_name', $service)
            ->first();

        if ($integration) {
            $integration->delete();
        }

        return redirect()->route('mcp.dashboard')
            ->with('success', "Integration '{$service}' removed");
    }

    /**
     * Merge local and remote integration data
     */
    private function mergeIntegrationData(mixed $local, array $remote): array
    {
        $merged = [];
        $localByService = collect($local)->keyBy('name');

        foreach ($remote as $remoteIntegration) {
            $serviceName = $remoteIntegration['service'] ?? $remoteIntegration['name'];
            $localData = $localByService->get($serviceName);

            $merged[] = array_merge(
                $localData ? $localData->toArray() : [],
                $remoteIntegration,
                ['source' => 'remote']
            );
        }

        // Add local integrations not in remote
        foreach ($localByService as $name => $localIntegration) {
            if (!collect($merged)->where('name', $name)->count()) {
                $merged[] = array_merge(
                    $localIntegration->toArray(),
                    ['source' => 'local']
                );
            }
        }

        return $merged;
    }

    /**
     * Get service-specific configuration requirements
     */
    private function getServiceConfig(string $service): array
    {
        $configs = [
            'todoist' => [
                'name' => 'Todoist',
                'fields' => [
                    ['name' => 'api_token', 'type' => 'password', 'label' => 'API Token', 'required' => true],
                ],
            ],
            'notion' => [
                'name' => 'Notion',
                'fields' => [
                    ['name' => 'api_key', 'type' => 'password', 'label' => 'Integration Token', 'required' => true],
                ],
            ],
            'jira' => [
                'name' => 'Jira',
                'fields' => [
                    ['name' => 'domain', 'type' => 'text', 'label' => 'Domain (e.g., company.atlassian.net)', 'required' => true],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                    ['name' => 'api_token', 'type' => 'password', 'label' => 'API Token', 'required' => true],
                ],
            ],
            'sentry' => [
                'name' => 'Sentry',
                'fields' => [
                    ['name' => 'organization', 'type' => 'text', 'label' => 'Organization Slug', 'required' => true],
                    ['name' => 'auth_token', 'type' => 'password', 'label' => 'Auth Token', 'required' => true],
                ],
            ],
            'confluence' => [
                'name' => 'Confluence',
                'fields' => [
                    ['name' => 'domain', 'type' => 'text', 'label' => 'Domain', 'required' => true],
                    ['name' => 'email', 'type' => 'email', 'label' => 'Email', 'required' => true],
                    ['name' => 'api_token', 'type' => 'password', 'label' => 'API Token', 'required' => true],
                ],
            ],
        ];

        return $configs[$service] ?? [
            'name' => ucfirst($service),
            'fields' => [
                ['name' => 'api_key', 'type' => 'password', 'label' => 'API Key', 'required' => true],
            ],
        ];
    }

    /**
     * Validate service-specific credentials
     */
    private function validateServiceCredentials(Request $request, string $service): array
    {
        $config = $this->getServiceConfig($service);
        $rules = [];
        $credentials = [];

        foreach ($config['fields'] as $field) {
            $fieldName = $field['name'];
            $rules[$fieldName] = $field['required'] ? 'required|string' : 'nullable|string';
        }

        $validated = $request->validate($rules);

        foreach ($config['fields'] as $field) {
            $fieldName = $field['name'];
            if (isset($validated[$fieldName])) {
                $credentials[$fieldName] = $validated[$fieldName];
            }
        }

        return $credentials;
    }
}
