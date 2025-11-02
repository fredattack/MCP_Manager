<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserManagementControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_users_index(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->count(5)->create();

        $response = $this->actingAs($admin)->get('/admin/users');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Admin/Users/Index')
            ->has('users.data', 6));
    }

    public function test_manager_can_view_users_index(): void
    {
        $manager = User::factory()->manager()->create();

        $response = $this->actingAs($manager)->get('/admin/users');

        $response->assertStatus(200);
    }

    public function test_regular_user_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/admin/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->post('/admin/users', [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'role' => UserRole::USER->value,
            'is_active' => true,
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'name' => 'New User',
        ]);
    }

    public function test_admin_can_update_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['name' => 'Old Name']);

        $response = $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'name' => 'Updated Name',
            'email' => $user->email,
            'role' => $user->role->value,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Updated Name',
        ]);
    }

    public function test_admin_can_delete_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$user->id}");

        $response->assertStatus(200);
        $this->assertDatabaseMissing('users', [
            'id' => $user->id,
        ]);
    }

    public function test_admin_cannot_delete_themselves(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->delete("/admin/users/{$admin->id}");

        $response->assertStatus(403);
        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
        ]);
    }

    public function test_admin_can_generate_credentials(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/credentials");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'credentials' => [
                'password',
                'api_token',
                'basic_auth',
                'basic_auth_header',
            ],
        ]);

        $credentials = $response->json('credentials');
        $this->assertNotEmpty($credentials['password']);
        $this->assertNotEmpty($credentials['basic_auth']);
        $this->assertStringStartsWith('Authorization: Basic ', $credentials['basic_auth_header']);
    }

    public function test_admin_can_lock_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/lock", [
            'reason' => 'Security violation',
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_locked' => true,
            'locked_reason' => 'Security violation',
        ]);
    }

    public function test_admin_can_unlock_user(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->locked()->create();

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/unlock");

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_locked' => false,
            'locked_reason' => null,
        ]);
    }

    public function test_admin_can_change_user_role(): void
    {
        $admin = User::factory()->admin()->create();
        $user = User::factory()->create(['role' => UserRole::USER->value]);

        $response = $this->actingAs($admin)->post("/admin/users/{$user->id}/change-role", [
            'role' => UserRole::MANAGER->value,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'role' => UserRole::MANAGER->value,
        ]);
    }

    public function test_search_filters_users_by_name_and_email(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        User::factory()->create(['name' => 'Jane Smith', 'email' => 'jane@example.com']);

        $response = $this->actingAs($admin)->get('/admin/users?search=John');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 1));
    }

    public function test_role_filter_works(): void
    {
        $admin = User::factory()->admin()->create();
        User::factory()->manager()->count(2)->create();
        User::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/users?role=manager');

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->has('users.data', 2));
    }
}
