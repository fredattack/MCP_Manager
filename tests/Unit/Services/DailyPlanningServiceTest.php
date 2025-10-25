<?php

namespace Tests\Unit\Services;

use App\Enums\IntegrationStatus;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\DailyPlanningService;
use App\Services\TodoistService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

/**
 * @group daily-planning
 * @group unit
 */
class DailyPlanningServiceTest extends TestCase
{
    use RefreshDatabase;

    private DailyPlanningService $service;

    private MockInterface $todoistServiceMock;

    private User $user;

    private IntegrationAccount $todoistIntegration;

    protected function setUp(): void
    {
        parent::setUp();

        $this->todoistServiceMock = Mockery::mock(TodoistService::class);
        $this->service = new DailyPlanningService($this->todoistServiceMock);

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

    public function test_generates_daily_planning_with_tasks(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Important task @high-energy',
                'priority' => 4,
                'project_id' => '100',
                'labels' => ['high-energy'],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => 'Task description [duration:60]',
            ],
            [
                'id' => '2',
                'content' => 'Regular task',
                'priority' => 2,
                'project_id' => '101',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '',
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertTrue($result['has_tasks']);
        $this->assertEquals(now()->format('Y-m-d'), $result['date']);
        $this->assertNotNull($result['mit']);
        $this->assertCount(2, $result['top_tasks']);
        $this->assertArrayHasKey('time_blocks', $result);
        $this->assertArrayHasKey('summary', $result);
        $this->assertArrayHasKey('todoist_updates', $result);
    }

    public function test_returns_no_tasks_message_when_empty(): void
    {
        $this->todoistServiceMock
            ->shouldReceive('setUser')
            ->with($this->user)
            ->once()
            ->andReturnSelf();

        $this->todoistServiceMock
            ->shouldReceive('getTodayTasks')
            ->once()
            ->andReturn([]);

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertFalse($result['has_tasks']);
        $this->assertEquals('No tasks found for today', $result['message']);
    }

    public function test_throws_exception_when_no_active_integration(): void
    {
        $this->todoistIntegration->update(['status' => IntegrationStatus::INACTIVE]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No active Todoist integration found');

        $this->service->generateDailyPlanning($this->user);
    }

    public function test_applies_ivy_lee_method_limiting_to_6_tasks(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 10; $i++) {
            $tasks[] = [
                'id' => (string) $i,
                'content' => "Task $i",
                'priority' => 2,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '',
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertCount(6, $result['top_tasks']);
        $this->assertCount(4, $result['additional_tasks']);
    }

    public function test_prioritizes_p1_tasks_first(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Low priority task',
                'priority' => 1,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '2',
                'content' => 'High priority task',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '3',
                'content' => 'Medium priority task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertEquals('2', $result['mit']['id']);
        $this->assertEquals('P1', $result['mit']['priority']);
        $this->assertEquals('2', $result['top_tasks'][0]['id']);
    }

    public function test_identifies_mit_correctly(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Regular P2 task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '2',
                'content' => 'Important P1 task',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '[duration:90]',
            ],
            [
                'id' => '3',
                'content' => 'Another P1 task with less duration',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '60 min',
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

        $result = $this->service->generateDailyPlanning($this->user);

        // MIT should be the P1 task with longer duration
        $this->assertEquals('2', $result['mit']['id']);
    }

    public function test_creates_time_blocks_with_breaks(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Task 1',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '[duration:90]',
            ],
            [
                'id' => '2',
                'content' => 'Task 2',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '60 min',
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

        $result = $this->service->generateDailyPlanning($this->user);

        $timeBlocks = $result['time_blocks'];
        $this->assertNotEmpty($timeBlocks);

        // Check for morning routine
        $this->assertStringContainsString('Routine matinale', $timeBlocks[0]['title']);

        // Check for breaks
        $hasBreak = false;
        foreach ($timeBlocks as $block) {
            if (! isset($block['task_id']) || $block['task_id'] === null) {
                if (str_contains($block['title'], 'Pause') || str_contains($block['title'], '☕')) {
                    $hasBreak = true;
                    break;
                }
            }
        }
        $this->assertTrue($hasBreak);
    }

    public function test_generates_alerts_for_p1_overload(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 8; $i++) {
            $tasks[] = [
                'id' => (string) $i,
                'content' => "P1 Task $i",
                'priority' => 4, // P1
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertNotEmpty($result['alerts']);

        $hasOverloadAlert = false;
        foreach ($result['alerts'] as $alert) {
            if ($alert['type'] === 'overload') {
                $hasOverloadAlert = true;
                $this->assertEquals('high', $alert['severity']);
                break;
            }
        }
        $this->assertTrue($hasOverloadAlert);
    }

    public function test_generates_alerts_for_duration_overload(): void
    {
        $tasks = [];
        for ($i = 1; $i <= 6; $i++) {
            $tasks[] = [
                'id' => (string) $i,
                'content' => "Task $i",
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '90 min', // Duration in description
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertNotEmpty($result['alerts']);

        $hasDurationAlert = false;
        foreach ($result['alerts'] as $alert) {
            if ($alert['type'] === 'duration') {
                $hasDurationAlert = true;
                $this->assertEquals('medium', $alert['severity']);
                break;
            }
        }
        $this->assertTrue($hasDurationAlert);
    }

    public function test_extracts_energy_levels_from_labels(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'High energy task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => ['high-energy'],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '2',
                'content' => 'Low energy task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => ['low-energy'],
                'due' => ['date' => now()->format('Y-m-d')],
            ],
            [
                'id' => '3',
                'content' => 'Default energy task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
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

        $result = $this->service->generateDailyPlanning($this->user);

        // Find tasks by energy level since order might vary
        $highEnergyTask = collect($result['top_tasks'])->firstWhere('energy', 'high');
        $lowEnergyTask = collect($result['top_tasks'])->firstWhere('energy', 'low');
        $mediumEnergyTask = collect($result['top_tasks'])->firstWhere('energy', 'medium');

        $this->assertNotNull($highEnergyTask);
        $this->assertNotNull($lowEnergyTask);
        $this->assertNotNull($mediumEnergyTask);
        $this->assertEquals('1', $highEnergyTask['id']);
        $this->assertEquals('2', $lowEnergyTask['id']);
        $this->assertEquals('3', $mediumEnergyTask['id']);
    }

    public function test_respects_scheduled_times(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Scheduled task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => [
                    'date' => now()->format('Y-m-d'),
                    'datetime' => now()->setTime(14, 30)->toIso8601String(),
                ],
            ],
            [
                'id' => '2',
                'content' => 'Unscheduled task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
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

        $result = $this->service->generateDailyPlanning($this->user);

        $this->assertEquals('14:30', $result['top_tasks'][0]['scheduled_time']);
        $this->assertNull($result['top_tasks'][1]['scheduled_time']);
    }

    public function test_generates_summary_statistics(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'P1 task',
                'priority' => 4,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '60 min',
            ],
            [
                'id' => '2',
                'content' => 'P2 task',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'duration' => ['amount' => 45, 'unit' => 'minute'],
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

        $result = $this->service->generateDailyPlanning($this->user);

        $summary = $result['summary'];
        $this->assertEquals(2, $summary['total_tasks']);
        $this->assertGreaterThanOrEqual(105, $summary['total_work_time']); // Should include work time
        $this->assertGreaterThan(0, $summary['total_break_time']);
        $this->assertEquals(1, $summary['p1_tasks']);
        $this->assertEquals(0, $summary['hexeko_tasks']); // No Hexeko project name
    }

    public function test_prepares_todoist_updates(): void
    {
        $tasks = [
            [
                'id' => '1',
                'content' => 'Task to update',
                'priority' => 3,
                'project_id' => '100',
                'labels' => [],
                'due' => ['date' => now()->format('Y-m-d')],
                'description' => '60 min',
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

        $result = $this->service->generateDailyPlanning($this->user);

        $updates = $result['todoist_updates'];
        $this->assertArrayHasKey('schedule_updates', $updates);
        $this->assertArrayHasKey('duration_updates', $updates);
        $this->assertArrayHasKey('order_updates', $updates);

        $this->assertNotEmpty($updates['schedule_updates']);
        $this->assertEquals('1', $updates['schedule_updates'][0]['task_id']);
        $this->assertNotEmpty($updates['schedule_updates'][0]['time']);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
