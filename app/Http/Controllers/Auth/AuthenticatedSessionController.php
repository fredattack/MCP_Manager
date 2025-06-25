<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Show the login page.
     */
    public function create(Request $request): Response
    {
        return Inertia::render('auth/login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Generate API token for the user
        $user = auth()->user();
        $user->api_token = \Illuminate\Support\Str::random(60);
        $user->save();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Generate and return an API token for the authenticated user.
     */
    public function apiToken(Request $request): \Illuminate\Http\JsonResponse
    {
        $user = auth()->user();

        if (! $user->api_token) {
            $user->api_token = \Illuminate\Support\Str::random(60);
            $user->save();
        }

        return response()->json(['api_token' => $user->api_token]);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        // Use the default guard instead of explicitly specifying 'web'
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
