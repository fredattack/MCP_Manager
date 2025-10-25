<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\TodoistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Inertia\Testing\AssertableInertia;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @extends TestCase
 */

/**
 * @group daily-planning
 * @group feature
 */
class DailyPlanningControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private IntegrationAccount $todoistIntegration;

    private MockInterface $todoistServiceMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;

        /** @var IntegrationAccount $integration */
        $integration = IntegrationAccount::factory()->todoist()->create([
            'user_id' => $this->user->id,
        ]);
        $this->todoistIntegration = $integration;

        $this->todoistServiceMock = $this->mock(TodoistService::class);
    }

    public function test_index_requires_authentication(): void
    {
        $response = $this->get(route('daily-planning.index'));

        $response->assertRedirect(route('login'));
    }

    public function test_index_requires_active_todoist_integration(): void
    {
        $this->todoistIntegration->update(['status' => 'inactive']);

        $response = $this->actingAs($this->user)
            ->get(route('daily-planning.index'));

        $response->assertRedirect(route('integrations.todoist.setup'));
        $response->assertSessionHas('warning', 'Please connect your Todoist account first.');
    }

    public function test_index_returns_daily_planning_page(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('daily-planning.index'));

        $response->assertOk();
        /** @phpstan-ignore-next-line */
        $response->assertInertia(fn (AssertableInertia $page) => $page
            ->component('daily-planning')
        );
    }

    public function test_generate_creates_new_daily_plan(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'High priority task',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 60, 'unit' => 'minute'],
            ],
            [
                'id' => '2',
                'content' => 'Medium priority task',
                'priority' => 3,
                'project_id' => '101',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 30, 'unit' => 'minute'],
            ],
        ];

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn($tasks);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        /** @var array{data: array{planning: array<string, mixed>, planning_id: string}} $responseJson */
        $responseJson = $response->json();
        $responseData = $responseJson['data'];
        $this->assertNotNull($responseData);
        $this->assertArrayHasKey('planning', $responseData);
        $this->assertArrayHasKey('planning_id', $responseData);

        $planning = $responseData['planning'];
        $this->assertArrayHasKey('mit', $planning);
        $this->assertArrayHasKey('top_tasks', $planning);
        $this->assertArrayHasKey('time_blocks', $planning);
        $this->assertArrayHasKey('summary', $planning);
    }

    public function test_generate_handles_todoist_api_error(): void
    {
        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andThrow(new \Exception('Todoist API error'));

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'An error occurred',
        ]);
    }

    public function test_update_tasks_updates_todoist_tasks(): void
    {
        $planningId = 'planning_test123';
        $planData = [
            'mit' => ['id' => '1', 'content' => 'Most important task'],
            'top_tasks' => [
                ['id' => '1', 'content' => 'Most important task'],
                ['id' => '2', 'content' => 'Other task 1'],
                ['id' => '3', 'content' => 'Other task 2'],
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
                    ['task_id' => '2', 'task_name' => 'Other task 1', 'time' => '10:15'],
                    ['task_id' => '3', 'task_name' => 'Other task 2', 'time' => '10:45'],
                ],
                'duration_updates' => [],
                'order_updates' => [],
            ],
        ];

        Cache::put("planning:{$this->user->id}:{$planningId}", $planData, now()->addDay());

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('updateTask')
            ->times(3)
            ->andReturn(['id' => '1']);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.update-tasks'), [
                'planning_id' => $planningId,
                'updates' => [
                    'type' => 'all',
                ],
            ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);
    }

    public function test_update_tasks_requires_existing_plan(): void
    {
        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.update-tasks'), [
                'planning_id' => 'non_existent_planning',
                'updates' => [
                    'type' => 'none',
                ],
            ]);

        $response->assertStatus(403);
        $response->assertJson([
            'success' => false,
            'message' => 'You are not authorized to perform this action',
        ]);
    }

    public function test_generate_applies_ivy_lee_method(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 10; $i++) {
            $tasks[] = [
                'id' => (string) $i,
                'content' => "Task $i",
                'priority' => 1,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 30, 'unit' => 'minute'],
            ];
        }

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn($tasks);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $planning = $response->json('data.planning');
        $totalTasks = count($planning['top_tasks']);
        $this->assertLessThanOrEqual(6, $totalTasks, 'Ivy Lee method should limit to 6 tasks');
    }

    public function test_generate_prioritizes_p1_tasks_first(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Low priority task',
                'priority' => 1,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 30, 'unit' => 'minute'],
            ],
            [
                'id' => '2',
                'content' => 'High priority task',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 30, 'unit' => 'minute'],
            ],
        ];

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn($tasks);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertOk();
        $planning = $response->json('data.planning');
        $this->assertEquals('2', $planning['mit']['id'], 'P1 task should be MIT');
    }

    public function test_generate_includes_breaks_in_time_blocks(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Task 1',
                'priority' => 2,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 90, 'unit' => 'minute'],
            ],
            [
                'id' => '2',
                'content' => 'Task 2',
                'priority' => 2,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 60, 'unit' => 'minute'],
            ],
        ];

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn($tasks);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertOk();
        $planning = $response->json('data.planning');
        $timeBlocks = $planning['time_blocks'];

        $hasBreak = false;
        foreach ($timeBlocks as $block) {
            if (! isset($block['task_id']) || ($block['task_id'] === null && (str_contains($block['title'], 'Pause') || str_contains($block['title'], '☕')))) {
                $hasBreak = true;
                break;
            }
        }

        $this->assertTrue($hasBreak, 'Time blocks should include breaks');
    }

    public function test_generate_detects_time_conflicts(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 10; $i++) {
            $tasks[] = [
                'id' => (string) $i,
                'content' => "Task $i",
                'priority' => 4, // P1 tasks to ensure they're all included
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 120, 'unit' => 'minute'], // 2 hours each
            ];
        }

        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn($tasks);

        $response = $this->actingAs($this->user)
            ->post(route('daily-planning.generate'));

        $response->assertOk();
        $planning = $response->json('data.planning');
        $this->assertNotEmpty($planning['alerts']);

        $hasDurationAlert = false;
        foreach ($planning['alerts'] as $alert) {
            if ($alert['type'] === 'duration' || $alert['type'] === 'overload') {
                $hasDurationAlert = true;
                break;
            }
        }

        $this->assertTrue($hasDurationAlert, 'Should detect duration overload when total duration exceeds work hours');
    }
}
