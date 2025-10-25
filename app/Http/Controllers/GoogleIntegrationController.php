<?php

namespace App\Http\Controllers;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\IntegrationAccount;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Inertia\Inertia;

class GoogleIntegrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(): \Inertia\Response
    {
        ray('in google index');
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        /** @var \App\Models\User $user */
        $user = $auth->user();

        $integrations = [];

        // Only get integrations if user is authenticated
        if ($user) {
            // Get Google integrations for the current user
            $gmailIntegration = $user->integrationAccounts()
                ->where('type', IntegrationType::GMAIL)
                ->first();

            $calendarIntegration = $user->integrationAccounts()
                ->where('type', IntegrationType::CALENDAR)
                ->first();

            if ($gmailIntegration) {
                $meta = is_array($gmailIntegration->meta) ? $gmailIntegration->meta : [];
                $integrations['gmail'] = [
                    'type' => 'gmail',
                    'status' => $gmailIntegration->status,
                    'email' => $meta['email'] ?? null,
                    'lastSync' => $gmailIntegration->updated_at->toISOString(),
                    'errorMessage' => $gmailIntegration->status->value === 'error' ? 'Connection failed' : null,
                ];
            }

            if ($calendarIntegration) {
                $meta = is_array($calendarIntegration->meta) ? $calendarIntegration->meta : [];
                $integrations['calendar'] = [
                    'type' => 'calendar',
                    'status' => $calendarIntegration->status,
                    'email' => $meta['email'] ?? null,
                    'lastSync' => $calendarIntegration->updated_at->toISOString(),
                    'errorMessage' => $calendarIntegration->status->value === 'error' ? 'Connection failed' : null,
                ];
            }
        }

        $serverUrl = config('services.mcp.server_url');
        $authUrl = is_string($serverUrl) ? $serverUrl.'/auth/google' : null;

        return Inertia::render('integrations/google', [
            'integrations' => $integrations,
            'authUrl' => $authUrl,
        ]);
    }

    public function setup(): \Inertia\Response
    {
        $redirectUri = config('services.google.redirect');
        $oauthConfigured = ! empty(config('services.google.client_id')) &&
                          ! empty(config('services.google.client_secret'));

        return Inertia::render('integrations/google-setup', [
            'config' => [
                'redirectUri' => $redirectUri ?: env('APP_URL').'/integrations/google/callback',
            ],
            'status' => [
                'serverConnected' => true,
                'oauthConfigured' => $oauthConfigured,
            ],
        ]);
    }

    public function connect(Request $request, string $service): \Illuminate\Http\RedirectResponse
    {
        // Check if OAuth is configured
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('integrations.google-setup')
                ->withErrors(['error' => 'Google OAuth credentials not configured. Please complete setup first.']);
        }

        // Store service type in session for callback
        session(['google_oauth_service' => $service]);
        session(['google_oauth_state' => Str::random(40)]);

        // Build Google OAuth URL
        $redirectUri = config('services.google.redirect');
        $state = session('google_oauth_state');

        $scopes = $this->getScopesForService($service);

        $params = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'scope' => implode(' ', $scopes),
            'response_type' => 'code',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/auth?'.$params);
    }

    private function getScopesForService(string $service): array
    {
        return match ($service) {
            'gmail' => [
                'https://www.googleapis.com/auth/gmail.readonly',
                'https://www.googleapis.com/auth/gmail.send',
                'https://www.googleapis.com/auth/gmail.modify',
                'https://www.googleapis.com/auth/userinfo.email',
            ],
            'calendar' => [
                'https://www.googleapis.com/auth/calendar',
                'https://www.googleapis.com/auth/userinfo.email',
            ],
            default => []
        };
    }

    public function callback(Request $request): \Illuminate\Http\RedirectResponse
    {
        ray($request->all())->red();
        // Verify state parameter
        // TODO: Fix session persistence issue
        // if ($request->state !== session('google_oauth_state')) {
        //     return redirect()->route('integrations.google')
        //         ->withErrors(['error' => 'Invalid state parameter']);
        // }

        if ($request->has('error')) {
            return redirect()->route('integrations.google')
                ->withErrors(['error' => 'OAuth authorization was denied']);
        }

        if (! $request->has('code')) {
            return redirect()->route('integrations.google')
                ->withErrors(['error' => 'Authorization code not received']);
        }

        try {
            // Exchange authorization code for access token
            $response = Http::post('https://oauth2.googleapis.com/token', [
                'client_id' => config('services.google.client_id'),
                'client_secret' => config('services.google.client_secret'),
                'code' => $request->code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => config('services.google.redirect'),
            ]);

            if (! $response->successful()) {
                throw new \Exception('Failed to exchange authorization code');
            }

            $tokenData = $response->json();
            ray($tokenData)->purple();
            // Get user info
            $userResponse = Http::withToken($tokenData['access_token'])
                ->get('https://www.googleapis.com/oauth2/v2/userinfo');

            if (! $userResponse->successful()) {
                throw new \Exception('Failed to get user information');
            }

            $userInfo = $userResponse->json();

            ray($userInfo)->blue();
            // Fallback: determine service from scopes since session is not working
            $service = session('google_oauth_service');
            if (! $service) {
                // Determine from scope parameter
                $scopes = $request->get('scope', '');
                $service = str_contains($scopes, 'gmail') ? 'gmail' : 'calendar';
            }

            // Save integration
            $integrationType = $service === 'gmail' ? IntegrationType::GMAIL : IntegrationType::CALENDAR;

            IntegrationAccount::updateOrCreate(
                [
                    'user_id' => auth()->id(),
                    'type' => $integrationType,
                ],
                [
                    'access_token' => encrypt($tokenData['access_token']),
                    'refresh_token' => isset($tokenData['refresh_token']) ? encrypt($tokenData['refresh_token']) : null,
                    'status' => IntegrationStatus::ACTIVE,
                    'meta' => [
                        'email' => $userInfo['email'],
                        'name' => $userInfo['name'],
                        'expires_at' => $tokenData['expires_in'] ? now()->addSeconds($tokenData['expires_in'])->toISOString() : null,
                        'scope' => $tokenData['scope'] ?? '',
                    ],
                ]
            );

            // Clear session data
            session()->forget(['google_oauth_service', 'google_oauth_state']);

            return redirect()->route('integrations.google')
                ->with('success', ucfirst((string) $service).' connected successfully');

        } catch (\Exception $e) {
            return redirect()->route('integrations.google')
                ->withErrors(['error' => 'Failed to connect: '.$e->getMessage()]);
        }
    }

    public function disconnect(Request $request, string $service): \Illuminate\Http\RedirectResponse
    {
        /** @var \Illuminate\Contracts\Auth\Guard $auth */
        $auth = auth();
        /** @var \App\Models\User $user */
        $user = $auth->user();

        if (! $user) {
            return back()->withErrors(['error' => 'User not authenticated']);
        }

        $integrationType = $service === 'gmail'
            ? IntegrationType::GMAIL
            : IntegrationType::CALENDAR;

        $integration = $user->integrationAccounts()
            ->where('type', $integrationType)
            ->first();

        if ($integration) {
            $integration->delete();
        }

        return back()->with('success', ucfirst($service).' disconnected successfully');
    }
}
