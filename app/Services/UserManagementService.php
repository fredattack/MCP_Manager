<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserActivityLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserManagementService
{
    public function createUser(array $data, ?User $performedBy = null): User
    {
        $password = $data['password'] ?? $this->generateSecurePassword();

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($password),
            'role' => $data['role'] ?? UserRole::USER->value,
            'permissions' => $data['permissions'] ?? null,
            'is_active' => $data['is_active'] ?? true,
            'notes' => $data['notes'] ?? null,
            'created_by' => $performedBy?->id,
        ]);

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'created',
            'new_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'ip_address' => $ipAddress,
        ]);

        return $user;
    }

    public function updateUser(User $user, array $data, ?User $performedBy = null): User
    {
        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];

        $user->update($data);

        $newValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->role->value,
            'is_active' => $user->is_active,
        ];

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'updated',
            'old_values' => $oldValues,
            'new_values' => $newValues,
            'ip_address' => $ipAddress,
        ]);

        return $user;
    }

    public function deleteUser(User $user, ?User $performedBy = null): bool
    {
        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'deleted',
            'old_values' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role->value,
            ],
            'ip_address' => $ipAddress,
        ]);

        return (bool) $user->delete();
    }

    /**
     * @return array{password:string,api_token:string,basic_auth:string,basic_auth_header:string}
     */
    public function generateCredentials(User $user, ?User $performedBy = null): array
    {
        $password = $this->generateSecurePassword();
        $apiToken = hash('sha256', Str::random(60));

        $user->update([
            'password' => Hash::make($password),
            'api_token' => $apiToken,
        ]);

        $basicAuth = base64_encode("{$user->email}:{$password}");

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'credentials_generated',
            'description' => 'New credentials generated',
            'ip_address' => $ipAddress,
        ]);

        return [
            'password' => $password,
            'api_token' => $apiToken,
            'basic_auth' => $basicAuth,
            'basic_auth_header' => "Authorization: Basic {$basicAuth}",
        ];
    }

    public function resetPassword(User $user, ?string $newPassword = null, ?User $performedBy = null): string
    {
        $password = $newPassword ?? $this->generateSecurePassword();

        $user->update([
            'password' => Hash::make($password),
        ]);

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'password_reset',
            'description' => $performedBy ? 'Password reset by admin' : 'Password reset by user',
            'ip_address' => $ipAddress,
        ]);

        return $password;
    }

    public function generateSecurePassword(int $length = 16): string
    {
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $special = '!@#$%^&*()-_=+[]{}';

        $password = '';
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $special[random_int(0, strlen($special) - 1)];

        $allChars = $uppercase.$lowercase.$numbers.$special;

        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        return str_shuffle($password);
    }

    public function changeRole(User $user, UserRole $newRole, ?User $performedBy = null): User
    {
        $oldRole = $user->role;

        $user->update(['role' => $newRole]);

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'role_changed',
            'old_values' => ['role' => $oldRole->value],
            'new_values' => ['role' => $newRole->value],
            'ip_address' => $ipAddress,
        ]);

        return $user;
    }

    /**
     * @param  array<int, string>  $permissions
     */
    public function updatePermissions(User $user, array $permissions, ?User $performedBy = null): User
    {
        $oldPermissions = $user->permissions ?? [];

        $user->update(['permissions' => $permissions]);

        $ipAddress = request()?->ip();

        UserActivityLog::create([
            'user_id' => $user->id,
            'performed_by' => $performedBy?->id,
            'action' => 'permissions_updated',
            'old_values' => ['permissions' => $oldPermissions],
            'new_values' => ['permissions' => $permissions],
            'ip_address' => $ipAddress,
        ]);

        return $user;
    }
}
