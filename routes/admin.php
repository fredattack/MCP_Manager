<?php

use App\Http\Controllers\Admin\UserManagementController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:admin,manager'])
    ->prefix('admin')
    ->group(function () {
        Route::resource('users', UserManagementController::class);

        Route::post('users/{user}/credentials', [UserManagementController::class, 'generateCredentials'])
            ->name('users.credentials');
        Route::post('users/{user}/reset-password', [UserManagementController::class, 'resetPassword'])
            ->name('users.reset-password');
        Route::post('users/{user}/lock', [UserManagementController::class, 'lock'])
            ->name('users.lock');
        Route::post('users/{user}/unlock', [UserManagementController::class, 'unlock'])
            ->name('users.unlock');
        Route::post('users/{user}/change-role', [UserManagementController::class, 'changeRole'])
            ->name('users.change-role');
        Route::post('users/{user}/permissions', [UserManagementController::class, 'updatePermissions'])
            ->name('users.permissions');
        Route::get('users/{user}/activity-log', [UserManagementController::class, 'activityLog'])
            ->name('users.activity-log');
    });
