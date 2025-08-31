<?php

namespace Tests\Unit\Actions\DailyPlanning;

use App\Actions\DailyPlanning\CreateDailyPlanningAction;
use App\Enums\IntegrationStatus;
use App\Models\IntegrationAccount;
use App\Models\User;
use App\Services\DailyPlanningService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class CreateDailyPlanningActionTest extends TestCase
{
    use RefreshDatabase;

    private CreateDailyPlanningAction $action;

    private MockInterface $dailyPlanningServiceMock;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->dailyPlanningServiceMock = Mockery::mock(DailyPlanningService::class);
        $this->action = new CreateDailyPlanningAction($this->dailyPlanningServiceMock);

        /** @var User $user */
        $user = User::factory()->create();
        $this->user = $user;

        /** @var IntegrationAccount $integration */
        $integration = IntegrationAccount::factory()->todoist()->create([
            'user_id' => $this->user->id,
            'status' => IntegrationStatus::ACTIVE,
        ]);
    }

    public function test_creates_daily_planning_successfully(): void
    {
        $planningData = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => ['id' => '1', 'content' => 'Important task'],
            'top_tasks' => [
                ['id' => '1', 'content' => 'Important task'],
                ['id' => '2', 'content' => 'Another task'],
            ],
            'time_blocks' => [],
            'additional_tasks' => [],
            'alerts' => [],
            'summary' => [
                'total_tasks' => 2,
                'total_work_time' => 90,
                'total_break_time' => 30,
                'p1_tasks' => 1,
                'hexeko_tasks' => 0,
            ],
            'todoist_updates' => [
                'schedule_updates' => [],
                'duration_updates' => [],
                'order_updates' => [],
            ],
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->with($this->user, [])
            ->once()
            ->andReturn($planningData);

        $result = $this->action->handle($this->user, []);

        $this->assertTrue($result->success);
        $this->assertArrayHasKey('planning_id', $result->data);
        $this->assertArrayHasKey('planning', $result->data);
        $this->assertArrayHasKey('markdown', $result->data);
        $this->assertEquals($planningData, $result->data['planning']);

        // Verify planning is cached
        $planningId = $result->data['planning_id'];
        $cachedPlan = Cache::get("planning:{$this->user->id}:{$planningId}");
        $this->assertNotNull($cachedPlan);
        $this->assertEquals($planningData, $cachedPlan);
    }

    public function test_handles_no_tasks_found(): void
    {
        $planningData = [
            'has_tasks' => false,
            'message' => 'No tasks found for today',
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->with($this->user, [])
            ->once()
            ->andReturn($planningData);

        $result = $this->action->handle($this->user, []);

        $this->assertTrue($result->success);
        $this->assertFalse($result->data['success']);
        $this->assertEquals('No tasks found for today', $result->data['message']);
        $this->assertNull($result->data['planning']);
    }

    public function test_validates_input_parameters(): void
    {
        $invalidData = [
            'date' => 'invalid-date',
            'chronotype' => 'invalid-type',
            'max_tasks' => 20,
        ];

        $result = $this->action->handle($this->user, $invalidData);

        $this->assertFalse($result->success);
        $this->assertEquals(422, $result->statusCode);
        $this->assertArrayHasKey('date', $result->errors);
        $this->assertArrayHasKey('chronotype', $result->errors);
        $this->assertArrayHasKey('max_tasks', $result->errors);
    }

    public function test_accepts_valid_preferences(): void
    {
        $preferences = [
            'chronotype' => 'morning',
            'high_energy_hours' => ['08:00-10:00', '14:00-16:00'],
            'break_preferences' => [
                'lunch_duration' => 60,
                'micro_break_duration' => 10,
            ],
            'max_tasks' => 6,
        ];

        $planningData = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => null,
            'top_tasks' => [],
            'time_blocks' => [],
            'additional_tasks' => [],
            'alerts' => [],
            'summary' => [],
            'todoist_updates' => [],
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->with($this->user, $preferences)
            ->once()
            ->andReturn($planningData);

        $result = $this->action->handle($this->user, $preferences);

        $this->assertTrue($result->success);
    }

    public function test_handles_service_exception(): void
    {
        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->once()
            ->andThrow(new \Exception('Service error'));

        $result = $this->action->handle($this->user, []);

        $this->assertFalse($result->success);
        $this->assertEquals(400, $result->statusCode);
        $this->assertEquals('An error occurred', $result->message);
    }

    public function test_generates_markdown_output(): void
    {
        $planningData = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => [
                'id' => '1',
                'content' => 'Complete important report',
                'project_name' => 'Work Project',
                'priority' => 'P1',
                'duration' => 90,
            ],
            'top_tasks' => [
                [
                    'id' => '1',
                    'content' => 'Complete important report',
                    'project_name' => 'Work Project',
                    'priority' => 'P1',
                    'duration' => 90,
                ],
                [
                    'id' => '2',
                    'content' => 'Review code',
                    'project_name' => 'Dev Project',
                    'priority' => 'P2',
                    'duration' => 45,
                ],
            ],
            'time_blocks' => [
                [
                    'start' => '08:00',
                    'end' => '08:15',
                    'duration' => 15,
                    'title' => '☕ Routine matinale + Revue du planning',
                    'task_id' => null,
                    'period' => 'morning',
                ],
                [
                    'start' => '08:15',
                    'end' => '09:45',
                    'duration' => 90,
                    'title' => '🎯 Complete important report',
                    'task_id' => '1',
                    'period' => 'morning',
                ],
            ],
            'additional_tasks' => [],
            'alerts' => [],
            'summary' => [
                'total_tasks' => 2,
                'total_work_time' => 135,
                'total_break_time' => 15,
            ],
            'todoist_updates' => [
                'schedule_updates' => [],
                'duration_updates' => [],
                'order_updates' => [],
            ],
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->once()
            ->andReturn($planningData);

        $result = $this->action->handle($this->user, []);

        $this->assertTrue($result->success);
        $markdown = $result->data['markdown'];

        // Check markdown contains key elements
        $this->assertStringContainsString('# 📅 Planning du', $markdown);
        $this->assertStringContainsString('## 🎯 MIT du jour', $markdown);
        $this->assertStringContainsString('Complete important report', $markdown);
        $this->assertStringContainsString('## 📋 Top 6 Tâches (Méthode Ivy Lee)', $markdown);
        $this->assertStringContainsString('## 🕐 Planning Time-Blocked', $markdown);
        $this->assertStringContainsString('08:00 - 08:15', $markdown);
        $this->assertStringContainsString('08:15 - 09:45', $markdown);
    }

    public function test_stores_planning_with_unique_id(): void
    {
        $planningData = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => null,
            'top_tasks' => [],
            'time_blocks' => [],
            'additional_tasks' => [],
            'alerts' => [],
            'summary' => [],
            'todoist_updates' => [],
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->twice()
            ->andReturn($planningData);

        $result1 = $this->action->handle($this->user, []);
        $result2 = $this->action->handle($this->user, []);

        $this->assertNotEquals(
            $result1->data['planning_id'],
            $result2->data['planning_id'],
            'Each planning should have a unique ID'
        );

        // Both should be cached
        $this->assertNotNull(Cache::get("planning:{$this->user->id}:{$result1->data['planning_id']}"));
        $this->assertNotNull(Cache::get("planning:{$this->user->id}:{$result2->data['planning_id']}"));
    }

    public function test_planning_cache_expires_after_24_hours(): void
    {
        $planningData = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => null,
            'top_tasks' => [],
            'time_blocks' => [],
            'additional_tasks' => [],
            'alerts' => [],
            'summary' => [],
            'todoist_updates' => [],
        ];

        $this->dailyPlanningServiceMock
            ->shouldReceive('generateDailyPlanning')
            ->once()
            ->andReturn($planningData);

        $result = $this->action->handle($this->user, []);
        $planningId = $result->data['planning_id'];
        $cacheKey = "planning:{$this->user->id}:{$planningId}";

        // Cache should exist
        $this->assertNotNull(Cache::get($cacheKey));

        // Simulate 25 hours passing
        $this->travel(25)->hours();

        // Cache should have expired
        $this->assertNull(Cache::get($cacheKey));
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
