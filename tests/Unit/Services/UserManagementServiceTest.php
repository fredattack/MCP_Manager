<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Enums\UserRole;
use App\Models\User;
use App\Models\UserActivityLog;
use App\Services\UserManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementServiceTest extends TestCase
{
    use RefreshDatabase;

    private UserManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new UserManagementService;
    }

    public function test_create_user_logs_activity_and_sets_defaults(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);

        $user = $this->service->createUser([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
            'role' => UserRole::MANAGER->value,
            'permissions' => ['users.view'],
            'notes' => 'Created for testing',
        ], $admin);

        $this->assertEquals('Manager User', $user->name);
        $this->assertEquals('manager@example.com', $user->email);
        $this->assertTrue($user->role === UserRole::MANAGER);
        $this->assertEquals(['users.view'], $user->permissions);
        $this->assertEquals($admin->id, $user->created_by);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'performed_by' => $admin->id,
            'action' => 'created',
        ]);
    }

    public function test_generate_credentials_updates_user_and_returns_basic_auth_bundle(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $user = User::factory()->create(['email' => 'user@example.com']);

        $credentials = $this->service->generateCredentials($user, $admin);

        $user->refresh();

        $this->assertArrayHasKey('password', $credentials);
        $this->assertArrayHasKey('api_token', $credentials);
        $this->assertArrayHasKey('basic_auth', $credentials);
        $this->assertArrayHasKey('basic_auth_header', $credentials);

        $this->assertTrue(Hash::check($credentials['password'], $user->password));
        $this->assertSame($credentials['api_token'], $user->api_token);
        $this->assertEquals(base64_encode($user->email.':'.$credentials['password']), $credentials['basic_auth']);
        $this->assertEquals('Authorization: Basic '.$credentials['basic_auth'], $credentials['basic_auth_header']);

        $this->assertDatabaseHas('user_activity_logs', [
            'user_id' => $user->id,
            'performed_by' => $admin->id,
            'action' => 'credentials_generated',
        ]);
    }

    public function test_update_permissions_records_audit_trail(): void
    {
        $admin = User::factory()->create(['role' => UserRole::ADMIN->value]);
        $user = User::factory()->create();

        $updated = $this->service->updatePermissions($user, ['users.view', 'users.edit'], $admin);

        $this->assertEquals(['users.view', 'users.edit'], $updated->permissions);

        $log = UserActivityLog::where('user_id', $user->id)
            ->where('action', 'permissions_updated')
            ->first();

        $this->assertNotNull($log);
        $this->assertEquals($admin->id, $log->performed_by);
        $this->assertEquals(['permissions' => ['users.view', 'users.edit']], $log->new_values);
    }
}
