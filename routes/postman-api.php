<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Postman-friendly API Routes
|--------------------------------------------------------------------------
|
| Ces routes sont conçues pour faciliter les tests avec Postman.
| Elles n'ont PAS de protection CSRF car elles sont dans le middleware 'api'.
|
| IMPORTANT: Ces routes utilisent l'authentification par session (cookies).
| Postman stockera automatiquement le cookie de session après le login.
|
*/

// Health check (no auth required)
Route::get('postman/health', function () {
    return response()->json([
        'status' => 'ok',
        'app' => config('app.name'),
        'environment' => app()->environment(),
        'timestamp' => now()->toIso8601String(),
        'php_version' => PHP_VERSION,
        'laravel_version' => app()->version(),
    ]);
})->name('postman.health');

// CSRF Token endpoint for web routes testing
Route::get('postman/csrf-token', function () {
    return response()->json([
        'csrf_token' => csrf_token(),
        'message' => 'CSRF token generated. Use this in X-XSRF-TOKEN header for web routes.',
        'instructions' => [
            '1. Call this endpoint first',
            '2. Copy the csrf_token value',
            '3. Add header: X-XSRF-TOKEN: <token>',
            '4. Make your POST/PUT/PATCH/DELETE request',
        ],
    ]);
})->middleware('web')->name('postman.csrf');

// Authentication endpoints (no CSRF required)
Route::prefix('postman/auth')->group(function () {
    // Register (no CSRF because we're in 'api' middleware)
    Route::post('register', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'min:8'],
            'password_confirmation' => ['required', 'same:password'],
        ]);

        $user = \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => \Illuminate\Support\Facades\Hash::make($request->password),
        ]);

        // Email verification event
        event(new \Illuminate\Auth\Events\Registered($user));

        // Auto-login after registration
        \Illuminate\Support\Facades\Auth::login($user);

        return response()->json([
            'message' => 'User registered and logged in successfully',
            'user' => $user->only(['id', 'name', 'email', 'created_at']),
            'note' => 'Session cookie has been set. Postman will use it automatically for subsequent requests.',
        ], 201);
    })->name('postman.register');

    // Login (no CSRF - uses stateful API auth)
    Route::post('login', function (\Illuminate\Http\Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
            'remember' => ['boolean'],
        ]);

        $remember = $request->boolean('remember', false);

        if (! \Illuminate\Support\Facades\Auth::attempt(
            $request->only('email', 'password'),
            $remember
        )) {
            return response()->json([
                'message' => 'Invalid credentials',
                'errors' => [
                    'email' => ['These credentials do not match our records.'],
                ],
            ], 401);
        }

        $user = \Illuminate\Support\Facades\Auth::user();

        return response()->json([
            'message' => 'Login successful',
            'user' => $user->only(['id', 'name', 'email']),
            'note' => 'User authenticated. Use existing API endpoints with auth middleware.',
        ]);
    })->name('postman.login');

    // Get current user
    Route::get('user', function (\Illuminate\Http\Request $request) {
        if (! auth()->check()) {
            return response()->json([
                'message' => 'Not authenticated',
                'user' => null,
            ], 401);
        }

        return response()->json([
            'user' => auth()->user(),
            'authenticated' => true,
        ]);
    })->middleware(['web'])->name('postman.user');

    // Logout
    Route::post('logout', function (\Illuminate\Http\Request $request) {
        if (! auth()->check()) {
            return response()->json([
                'message' => 'Not authenticated',
            ], 401);
        }

        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    })->middleware(['web'])->name('postman.logout');
});

// Quick test endpoints (authenticated)
Route::middleware(['web', 'auth'])->prefix('postman/test')->group(function () {
    Route::get('ping', function () {
        return response()->json([
            'message' => 'pong',
            'user' => auth()->user()->only(['id', 'name', 'email']),
            'timestamp' => now()->toIso8601String(),
        ]);
    });

    Route::get('integrations', function () {
        return response()->json([
            'integrations' => auth()->user()->integrationAccounts,
        ]);
    });
});