<?php

declare(strict_types=1);

namespace Tests\Feature\Workflow;

use App\Enums\WorkflowStatus;
use App\Models\User;
use App\Models\Workflow;
use App\Services\Workflow\Actions\AnalyzeRepositoryAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * @group workflow
 * @group feature
 * @group sprint2
 */
class WorkflowApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_can_list_workflows(): void
    {
        Workflow::factory()->count(3)->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson('/api/workflows');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'status',
                        'created_at',
                    ],
                ],
            ]);
    }

    public function test_can_create_workflow(): void
    {
        $workflowData = [
            'name' => 'Test Workflow',
            'description' => 'A test workflow',
            'config' => [
                'action_class' => AnalyzeRepositoryAction::class,
            ],
            'status' => WorkflowStatus::Active->value,
        ];

        $response = $this->actingAs($this->user, 'web')
            ->postJson('/api/workflows', $workflowData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'name' => 'Test Workflow',
                'description' => 'A test workflow',
            ]);

        $this->assertDatabaseHas('workflows', [
            'name' => 'Test Workflow',
            'user_id' => $this->user->id,
        ]);
    }

    public function test_can_show_workflow(): void
    {
        $workflow = Workflow::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson("/api/workflows/{$workflow->id}");

        $response->assertStatus(200)
            ->assertJsonPath('data.id', $workflow->id)
            ->assertJsonPath('data.name', $workflow->name);
    }

    public function test_can_update_workflow(): void
    {
        $workflow = Workflow::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')
            ->putJson("/api/workflows/{$workflow->id}", [
                'name' => 'Updated Workflow',
                'description' => 'Updated description',
                'config' => $workflow->config,
                'status' => $workflow->status->value,
            ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'Updated Workflow');

        $this->assertDatabaseHas('workflows', [
            'id' => $workflow->id,
            'name' => 'Updated Workflow',
        ]);
    }

    public function test_can_delete_workflow(): void
    {
        $workflow = Workflow::factory()->create(['user_id' => $this->user->id]);

        $response = $this->actingAs($this->user, 'web')
            ->deleteJson("/api/workflows/{$workflow->id}");

        $response->assertStatus(200);

        $this->assertDatabaseMissing('workflows', [
            'id' => $workflow->id,
        ]);
    }

    public function test_cannot_access_other_users_workflow(): void
    {
        $otherUser = User::factory()->create();
        $workflow = Workflow::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->actingAs($this->user, 'web')
            ->getJson("/api/workflows/{$workflow->id}");

        $response->assertStatus(403);
    }

    public function test_validation_fails_without_name(): void
    {
        $response = $this->actingAs($this->user, 'web')
            ->postJson('/api/workflows', [
                'description' => 'Missing name',
            ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['name']);
    }
}
