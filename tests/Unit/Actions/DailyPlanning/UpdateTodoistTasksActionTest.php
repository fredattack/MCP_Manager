<?php

namespace Tests\Unit\Actions\DailyPlanning;

use App\Actions\DailyPlanning\UpdateTodoistTasksAction;
use App\Enums\IntegrationStatus;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\TodoistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @group daily-planning
 * @group todoist
 * @group unit
 */
class UpdateTodoistTasksActionTest extends TestCase
{
    use RefreshDatabase;

    private UpdateTodoistTasksAction $action;

    private MockInterface $todoistServiceMock;

    private User $user;

    private IntegrationAccount $todoistIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->todoistServiceMock = Mockery::mock(TodoistService::class);
        $this->action = new UpdateTodoistTasksAction($this->todoistServiceMock);

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;

        /** @var IntegrationAccount $integration */
        $integration = IntegrationAccount::factory()->todoist()->create([
            'user_id' => $this->user->id,
            'status' => IntegrationStatus::ACTIVE,
        ]);
        $this->todoistIntegration = $integration;
    }

    public function test_updates_all_tasks_successfully(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        // Expect 3 schedule updates
        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->times(3)
            ->andReturn(['id' => '1']);

        $updates = [
            'type' => 'all',
        ];

        $result = $this->action->handle($this->user, $planningId, $updates);

        $this->assertTrue($result->success);
        $this->assertArrayHasKey('updates_applied', $result->data);
        $this->assertEquals(3, $result->data['updates_applied']['schedule']);
    }

    public function test_updates_partial_tasks_with_selected_types(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        // Only schedule updates should be called
        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->times(3)
            ->andReturn(['id' => '1']);

        $updates = [
            'type' => 'partial',
            'selected' => ['schedule'],
        ];

        $result = $this->action->handle($this->user, $planningId, $updates);

        $this->assertTrue($result->success);
        $this->assertEquals(3, $result->data['updates_applied']['schedule']);
        $this->assertEquals(0, $result->data['updates_applied']['duration']);
        $this->assertEquals(0, $result->data['updates_applied']['order']);
    }

    public function test_handles_none_update_type(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $updates = [
            'type' => 'none',
        ];

        $result = $this->action->handle($this->user, $planningId, $updates);

        $this->assertTrue($result->success);
        $this->assertEquals('Planning conservé sans modifications dans Todoist.', $result->data['message']);
        $this->assertEmpty($result->data['updates_applied']);
    }

    public function test_validates_update_parameters(): void
    {
        $result = $this->action->handle($this->user, 'planning_id', [
            'type' => 'invalid_type',
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(422, $result->statusCode);
        $this->assertArrayHasKey('updates.type', $result->errors);
    }

    public function test_requires_existing_planning(): void
    {
        $result = $this->action->handle($this->user, 'non_existent_planning', [
            'type' => 'all',
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(403, $result->statusCode);
        $this->assertEquals('You are not authorized to perform this action', $result->message);
    }

    public function test_requires_active_todoist_integration(): void
    {
        $this->todoistIntegration->update(['status' => IntegrationStatus::INACTIVE]);

        $result = $this->action->handle($this->user, 'planning_id', [
            'type' => 'all',
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(403, $result->statusCode);
    }

    public function test_handles_todoist_api_errors_gracefully(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        // First update succeeds, second fails
        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->once()
            ->with('1', Mockery::any())
            ->andReturn(['id' => '1']);

        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->once()
            ->with('2', Mockery::any())
            ->andThrow(new \Exception('API Error'));

        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->once()
            ->with('3', Mockery::any())
            ->andReturn(['id' => '3']);

        $result = $this->action->handle($this->user, $planningId, [
            'type' => 'all',
        ]);

        $this->assertTrue($result->success);
        $this->assertEquals(2, $result->data['updates_applied']['schedule']);
        $this->assertNotEmpty($result->data['errors']);
        $this->assertStringContainsString('Task 2', $result->data['errors'][0]);
    }

    public function test_handles_planning_not_found_error(): void
    {
        // Try to update non-existent planning
        $planningId = 'non_existent_planning';

        $result = $this->action->handle($this->user, $planningId, [
            'type' => 'all',
        ]);

        $this->assertFalse($result->success);
        $this->assertEquals(403, $result->statusCode); // Authorization fails first
    }

    public function test_updates_task_schedule_correctly(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        // Verify correct datetime format is passed
        $expectedDateTime = now()->format('Y-m-d').'T09:00:00';
        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->once()
            ->with('1', Mockery::on(function ($data) use ($expectedDateTime) {
                return isset($data['due_datetime']) &&
                       str_starts_with($data['due_datetime'], $expectedDateTime);
            }))
            ->andReturn(['id' => '1']);

        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->times(2)
            ->andReturn(['id' => '1']);

        $result = $this->action->handle($this->user, $planningId, [
            'type' => 'all',
        ]);

        $this->assertTrue($result->success);
    }

    public function test_generates_success_message_with_counts(): void
    {
        $planningId = 'planning_test123';
        $planning = $this->createPlanningData();
        Cache::put("planning:{$this->user->id}:{$planningId}", $planning, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->times(3)
            ->andReturn(['id' => '1']);

        $result = $this->action->handle($this->user, $planningId, [
            'type' => 'all',
        ]);

        $this->assertTrue($result->success);
        $this->assertStringContainsString('3 tâches', $result->data['message']);
    }

    private function createPlanningData(): array
    {
        return [
            'mit' => ['id' => '1', 'content' => 'Most important task'],
            'top_tasks' => [
                ['id' => '1', 'content' => 'Most important task'],
                ['id' => '2', 'content' => 'Task 2'],
                ['id' => '3', 'content' => 'Task 3'],
            ],
            'time_blocks' => [
                [
                    'task_id' => '1',
                    'start_time' => '09:00',
                    'end_time' => '10:00',
                    'duration' => 60,
                ],
                [
                    'task_id' => '2',
                    'start_time' => '10:15',
                    'end_time' => '10:45',
                    'duration' => 30,
                ],
            ],
            'todoist_updates' => [
                'schedule_updates' => [
                    ['task_id' => '1', 'task_name' => 'Most important task', 'time' => '09:00'],
                    ['task_id' => '2', 'task_name' => 'Task 2', 'time' => '10:15'],
                    ['task_id' => '3', 'task_name' => 'Task 3', 'time' => '10:45'],
                ],
                'duration_updates' => [],
                'order_updates' => [],
            ],
        ];
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
