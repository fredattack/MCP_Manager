<?php

namespace App\Http\Controllers;

use App\Services\RealMcpServerManager;
use Illuminate\Http\Request;
use Inertia\Inertia;

class IntegrationManagerController extends Controller
{
    private RealMcpServerManager $mcpManager;
    
    public function __construct(RealMcpServerManager $mcpManager)
    {
        $this->mcpManager = $mcpManager;
    }
    
    /**
     * Display integrations dashboard
     */
    public function index()
    {
        $user = auth()->user();
        $integrations = $this->mcpManager->getAllIntegrationsStatus($user);
        
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
        $status = $this->mcpManager->getIntegrationStatus($user, $service);
        
        return Inertia::render('IntegrationManager/Configure', [
            'service' => $service,
            'status' => $status,
        ]);
    }
    
    /**
     * Store integration configuration
     */
    public function store(Request $request, string $service)
    {
        $user = auth()->user();
        
        // Validate based on service type
        $credentials = $this->validateCredentials($service, $request);
        
        try {
            $integration = $this->mcpManager->configureIntegration($user, $service, $credentials);
            
            return redirect()->route('integrations.manager.index')
                ->with('success', ucfirst($service) . ' integration configured successfully');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Test integration connection
     */
    public function test(string $service)
    {
        $user = auth()->user();
        $result = $this->mcpManager->testIntegration($user, $service);
        
        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => $result['message'],
            ]);
        }
        
        return response()->json([
            'success' => false,
            'message' => $result['message'],
        ], 400);
    }
    
    /**
     * Remove integration
     */
    public function destroy(string $service)
    {
        $user = auth()->user();
        
        if ($this->mcpManager->removeIntegration($user, $service)) {
            return redirect()->route('integrations.manager.index')
                ->with('success', ucfirst($service) . ' integration removed successfully');
        }
        
        return back()->withErrors(['error' => 'Failed to remove integration']);
    }
    
    /**
     * Validate credentials based on service type
     */
    private function validateCredentials(string $service, Request $request): array
    {
        switch ($service) {
            case 'todoist':
                $request->validate([
                    'api_token' => 'required|string|min:32',
                ]);
                return ['api_token' => $request->api_token];
                
            case 'notion':
                $request->validate([
                    'api_key' => 'required|string|starts_with:secret_',
                    'database_id' => 'nullable|string',
                ]);
                return [
                    'api_key' => $request->api_key,
                    'database_id' => $request->database_id,
                ];
                
            case 'jira':
                $request->validate([
                    'domain' => 'required|url',
                    'email' => 'required|email',
                    'api_token' => 'required|string',
                ]);
                return [
                    'domain' => $request->domain,
                    'email' => $request->email,
                    'api_token' => $request->api_token,
                ];
                
            case 'openai':
                $request->validate([
                    'api_key' => 'required|string|starts_with:sk-',
                    'model' => 'nullable|string',
                ]);
                return [
                    'api_key' => $request->api_key,
                    'model' => $request->model ?? 'gpt-4',
                ];
                
            case 'mistral':
                $request->validate([
                    'api_key' => 'required|string',
                    'model' => 'nullable|string',
                ]);
                return [
                    'api_key' => $request->api_key,
                    'model' => $request->model ?? 'mistral-medium',
                ];
                
            default:
                throw new \InvalidArgumentException('Unsupported service: ' . $service);
        }
    }
}