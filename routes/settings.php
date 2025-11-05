<?php

use App\Http\Controllers\Settings\OrganizationController;
use App\Http\Controllers\Settings\OrganizationCredentialController;
use App\Http\Controllers\Settings\OrganizationInvitationController;
use App\Http\Controllers\Settings\OrganizationMemberController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\Security\ActiveLeasesController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', 'settings/profile');

    // Profile Settings
    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Password Settings
    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    // Appearance Settings
    Route::get('settings/appearance', function () {
        return Inertia::render('settings/appearance');
    })->name('appearance');

    // Security Settings - Active Leases
    Route::prefix('settings/security')->name('settings.security.')->group(function () {
        Route::get('/active-leases', [ActiveLeasesController::class, 'index'])->name('active-leases');
        Route::delete('/active-leases/{lease}/revoke', [ActiveLeasesController::class, 'revoke'])->name('active-leases.revoke');
    });

    // Organization Management
    Route::prefix('settings/organizations')->name('settings.organizations.')->group(function () {
        // Main CRUD routes
        Route::get('/', [OrganizationController::class, 'index'])->name('index');
        Route::get('/create', [OrganizationController::class, 'create'])->name('create');
        Route::post('/', [OrganizationController::class, 'store'])->name('store');
        Route::get('/{organization}', [OrganizationController::class, 'show'])->name('show');
        Route::get('/{organization}/edit', [OrganizationController::class, 'edit'])->name('edit');
        Route::put('/{organization}', [OrganizationController::class, 'update'])->name('update');
        Route::delete('/{organization}', [OrganizationController::class, 'destroy'])->name('destroy');

        // Member Management API routes
        Route::prefix('{organization}/members')->name('members.')->group(function () {
            Route::get('/', [OrganizationMemberController::class, 'index'])->name('index');
            Route::post('/', [OrganizationMemberController::class, 'store'])->name('store');
            Route::put('/{member}', [OrganizationMemberController::class, 'update'])->name('update');
            Route::delete('/{member}', [OrganizationMemberController::class, 'destroy'])->name('destroy');
        });

        // Invitation Management API routes
        Route::prefix('{organization}/invitations')->name('invitations.')->group(function () {
            Route::get('/', [OrganizationInvitationController::class, 'index'])->name('index');
            Route::post('/', [OrganizationInvitationController::class, 'store'])->name('store');
            Route::post('/{invitation}/resend', [OrganizationInvitationController::class, 'resend'])->name('resend');
            Route::delete('/{invitation}', [OrganizationInvitationController::class, 'destroy'])->name('destroy');
        });

        // Credential Management API routes
        Route::prefix('{organization}/credentials')->name('credentials.')->group(function () {
            Route::get('/', [OrganizationCredentialController::class, 'index'])->name('index');
            Route::post('/', [OrganizationCredentialController::class, 'store'])->name('store');
            Route::put('/{credential}', [OrganizationCredentialController::class, 'update'])->name('update');
            Route::delete('/{credential}', [OrganizationCredentialController::class, 'destroy'])->name('destroy');
        });
    });

    // Organization Invitation Acceptance (public with auth)
    Route::get('/invitations/{token}', [OrganizationInvitationController::class, 'show'])->name('invitations.show');
    Route::post('/invitations/{token}/accept', [OrganizationInvitationController::class, 'accept'])->name('invitations.accept');
});
