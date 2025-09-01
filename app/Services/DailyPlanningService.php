<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\IntegrationStatus;
use App\Enums\IntegrationType;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DailyPlanningService extends BaseService
{
    private const MAX_IVY_LEE_TASKS = 6;

    private const DEFAULT_TASK_DURATION = 45; // minutes

    private const LUNCH_DURATION = 60; // minutes

    private const MICRO_BREAK_DURATION = 10; // minutes

    private const END_BUFFER_DURATION = 30; // minutes

    public function __construct(
        private readonly TodoistService $todoistService
    ) {}

    /**
     * @param array<string, mixed> $options
     * @return array<string, mixed>
     */
    public function generateDailyPlanning(User $user, array $options = []): array
    {
        // Get today's tasks from Todoist
        $tasks = $this->getTodayTasks($user);

        if ($tasks->isEmpty()) {
            return [
                'has_tasks' => false,
                'message' => 'No tasks found for today',
            ];
        }

        // Apply prioritization rules
        $prioritizedTasks = $this->prioritizeTasks($tasks);

        // Select top 6 tasks (Ivy Lee method)
        $topTasks = $prioritizedTasks->take(self::MAX_IVY_LEE_TASKS);

        // Identify MIT
        $mit = $this->identifyMIT($topTasks);

        // Create time blocks
        $timeBlocks = $this->createTimeBlocks($topTasks, $mit);

        // Get remaining tasks
        $additionalTasks = $prioritizedTasks->skip(self::MAX_IVY_LEE_TASKS);

        // Check for conflicts and overload
        $alerts = $this->checkAlerts($topTasks, $tasks);

        $result = [
            'has_tasks' => true,
            'date' => now()->format('Y-m-d'),
            'mit' => $mit,
            'top_tasks' => $topTasks->values()->all(),
            'time_blocks' => $timeBlocks,
            'additional_tasks' => $additionalTasks->values()->all(),
            'alerts' => $alerts,
            'summary' => $this->generateSummary($topTasks, $timeBlocks),
            'todoist_updates' => $this->prepareTodoistUpdates($topTasks, $timeBlocks),
        ];
        
        \Log::info('Daily planning generated', [
            'has_tasks' => $result['has_tasks'],
            'top_tasks_count' => count($result['top_tasks']),
            'has_mit' => isset($result['mit']),
        ]);
        
        return $result;
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    private function getTodayTasks(User $user): Collection
    {
        $account = $user->integrationAccounts()
            ->where('type', IntegrationType::TODOIST)
            ->where('status', IntegrationStatus::ACTIVE)
            ->first();

        if (! $account) {
            throw new \Exception('No active Todoist integration found');
        }

        // Get tasks for today
        $tasks = $this->todoistService->setUser($user)->getTodayTasks();

        return collect($tasks)->map(fn ($task): array => [
            'id' => $task['id'],
            'content' => $task['content'],
            'project_id' => $task['project_id'] ?? null,
            'project_name' => $this->getProjectName($task['project_id'] ?? null),
            'priority' => $this->mapPriority($task['priority'] ?? 1),
            'energy' => $this->extractEnergy($task),
            'scheduled_time' => $this->extractScheduledTime($task),
            'duration' => $this->extractDuration($task),
            'description' => $task['description'] ?? '',
            'labels' => $task['labels'] ?? [],
        ]);
    }

    /**
     * @param Collection<int, array<string, mixed>> $tasks
     * @return Collection<int, array<string, mixed>>
     */
    private function prioritizeTasks(Collection $tasks): Collection
    {
        return $tasks->sort(function ($a, $b): int {
            // Rule 1: P1 always first
            if ($a['priority'] === 'P1' && $b['priority'] !== 'P1') {
                return -1;
            }
            if ($b['priority'] === 'P1' && $a['priority'] !== 'P1') {
                return 1;
            }

            // For non-P1 tasks, consider project (Hexeko priority)
            $aIsHexeko = str_contains(strtolower($a['project_name'] ?? ''), 'hexeko');
            $bIsHexeko = str_contains(strtolower($b['project_name'] ?? ''), 'hexeko');

            if ($aIsHexeko && ! $bIsHexeko) {
                return -1;
            }
            if ($bIsHexeko && ! $aIsHexeko) {
                return 1;
            }

            // Same project type, compare priorities
            $priorityOrder = ['P1' => 1, 'P2' => 2, 'P3' => 3, 'P4' => 4];
            $priorityComparison = $priorityOrder[$a['priority']] <=> $priorityOrder[$b['priority']];
            if ($priorityComparison !== 0) {
                return $priorityComparison;
            }

            // Rule 2: Energy level (high energy tasks earlier)
            $energyComparison = ($b['energy'] ?? 'medium') <=> ($a['energy'] ?? 'medium');
            if ($energyComparison !== 0) {
                return $energyComparison;
            }

            // Rule 3: Fixed time constraints
            if ($a['scheduled_time'] && ! $b['scheduled_time']) {
                return -1;
            }
            if ($b['scheduled_time'] && ! $a['scheduled_time']) {
                return 1;
            }

            // Rule 4: Tasks with defined duration before undefined
            if ($a['duration'] !== null && $b['duration'] === null) {
                return -1;
            }
            if ($b['duration'] !== null && $a['duration'] === null) {
                return 1;
            }

            return 0;
        });
    }

    /**
     * @param Collection<int, array<string, mixed>> $tasks
     * @return array<string, mixed>|null
     */
    private function identifyMIT(Collection $tasks): ?array
    {
        // MIT is typically the highest priority task with high energy requirement
        return $tasks->first(fn ($task): bool => $task['priority'] === 'P1' ||
               ($task['priority'] === 'P2' && ($task['energy'] ?? 'medium') === 'high'));
    }

    /**
     * @param Collection<int, array<string, mixed>> $tasks
     * @param array<string, mixed>|null $mit
     * @return array<int, array<string, mixed>>
     */
    private function createTimeBlocks(Collection $tasks, ?array $mit): array
    {
        $blocks = [];
        $currentTime = Carbon::parse('08:00');

        // Morning routine
        $blocks[] = $this->createBlock($currentTime, 15, '☕ Routine matinale + Revue du planning');
        $currentTime->addMinutes(15);

        // MIT block (if exists)
        if ($mit) {
            $mitDuration = $mit['duration'] ?? 90;
            $blocks[] = $this->createBlock($currentTime, $mitDuration, "🎯 {$mit['content']}", $mit['id']);
            $currentTime->addMinutes($mitDuration);

            // Break after MIT
            $blocks[] = $this->createBlock($currentTime, 15, '☕ Pause');
            $currentTime->addMinutes(15);
        }

        // Schedule remaining tasks
        $taskIndex = 0;
        foreach ($tasks as $task) {
            if ($mit && $task['id'] === $mit['id']) {
                continue; // Skip MIT as it's already scheduled
            }

            // Check if it's lunch time
            if ($currentTime->hour >= 12 && $currentTime->hour < 13) {
                $blocks[] = $this->createBlock(Carbon::parse('12:30'), self::LUNCH_DURATION, '🍽️ Pause déjeuner');
                $currentTime = Carbon::parse('13:30');
            }

            // Schedule task
            $duration = $task['duration'] ?? self::DEFAULT_TASK_DURATION;

            // Respect fixed time constraints
            if ($task['scheduled_time']) {
                $scheduledTime = Carbon::parse($task['scheduled_time']);
                if ($scheduledTime->gt($currentTime)) {
                    // Add buffer until scheduled time
                    $bufferMinutes = $scheduledTime->diffInMinutes($currentTime);
                    if ($bufferMinutes > 0) {
                        $blocks[] = $this->createBlock($currentTime, $bufferMinutes, '🔄 Buffer/Transition');
                    }
                    $currentTime = $scheduledTime;
                }
            }

            $blocks[] = $this->createBlock($currentTime, $duration, $task['content'], $task['id']);
            $currentTime->addMinutes($duration);

            // Add break after every 90 minutes of work
            if ($taskIndex > 0 && $taskIndex % 2 === 0) {
                $blocks[] = $this->createBlock($currentTime, self::MICRO_BREAK_DURATION, '☕ Pause');
                $currentTime->addMinutes(self::MICRO_BREAK_DURATION);
            }

            $taskIndex++;
        }

        // End of day buffer
        if ($currentTime->hour < 18) {
            $blocks[] = $this->createBlock($currentTime, self::END_BUFFER_DURATION, '📝 Tâches à durée indéterminée / Buffer');
        }

        return $blocks;
    }

    /**
     * @return array<string, mixed>
     */
    private function createBlock(Carbon $startTime, int $duration, string $title, ?string $taskId = null): array
    {
        $endTime = $startTime->copy()->addMinutes($duration);

        return [
            'start' => $startTime->format('H:i'),
            'end' => $endTime->format('H:i'),
            'duration' => $duration,
            'title' => $title,
            'task_id' => $taskId,
            'period' => $startTime->hour < 12 ? 'morning' : 'afternoon',
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $topTasks
     * @param Collection<int, array<string, mixed>> $allTasks
     * @return array<int, array<string, mixed>>
     */
    private function checkAlerts(Collection $topTasks, Collection $allTasks): array
    {
        $alerts = [];

        // Check for P1 overload
        $p1Count = $allTasks->where('priority', 'P1')->count();
        if ($p1Count > self::MAX_IVY_LEE_TASKS) {
            $alerts[] = [
                'type' => 'overload',
                'severity' => 'high',
                'message' => "Vous avez {$p1Count} tâches P1 aujourd'hui. Considérez de reporter certaines tâches ou de demander de l'aide.",
            ];
        }

        // Check for time conflicts
        $scheduledTasks = $topTasks->filter(fn ($task): bool => $task['scheduled_time'] !== null);
        $conflicts = $this->findTimeConflicts($scheduledTasks);
        if ($conflicts !== []) {
            $alerts[] = [
                'type' => 'conflict',
                'severity' => 'high',
                'message' => 'Conflits horaires détectés entre certaines tâches.',
                'details' => $conflicts,
            ];
        }

        // Check total duration
        $totalDuration = $topTasks->sum(fn ($task): int => $task['duration'] ?? self::DEFAULT_TASK_DURATION);
        if ($totalDuration > 480) { // 8 hours
            $alerts[] = [
                'type' => 'duration',
                'severity' => 'medium',
                'message' => 'La durée totale des tâches dépasse 8 heures. Considérez de réduire certaines estimations ou de reporter des tâches.',
            ];
        }

        return $alerts;
    }

    /**
     * @param Collection<int, array<string, mixed>> $scheduledTasks
     * @return array<int, array<string, mixed>>
     */
    private function findTimeConflicts(Collection $scheduledTasks): array
    {
        $conflicts = [];
        $tasks = $scheduledTasks->values();

        for ($i = 0; $i < $tasks->count() - 1; $i++) {
            for ($j = $i + 1; $j < $tasks->count(); $j++) {
                $task1 = $tasks[$i];
                $task2 = $tasks[$j];

                if ($task1['scheduled_time'] === $task2['scheduled_time']) {
                    $conflicts[] = [
                        'task1' => $task1['content'],
                        'task2' => $task2['content'],
                        'time' => $task1['scheduled_time'],
                    ];
                }
            }
        }

        return $conflicts;
    }

    /**
     * @param Collection<int, array<string, mixed>> $topTasks
     * @param array<int, array<string, mixed>> $timeBlocks
     * @return array<string, mixed>
     */
    private function generateSummary(Collection $topTasks, array $timeBlocks): array
    {
        $totalWorkTime = collect($timeBlocks)
            ->filter(fn ($block): bool => $block['task_id'] !== null)
            ->sum('duration');

        $totalBreakTime = collect($timeBlocks)
            ->filter(fn ($block): bool => str_contains((string) $block['title'], 'Pause') || str_contains((string) $block['title'], 'Buffer'))
            ->sum('duration');

        return [
            'total_tasks' => $topTasks->count(),
            'total_work_time' => $totalWorkTime,
            'total_break_time' => $totalBreakTime,
            'p1_tasks' => $topTasks->where('priority', 'P1')->count(),
            'hexeko_tasks' => $topTasks->filter(fn ($task): bool => str_contains(strtolower($task['project_name'] ?? ''), 'hexeko'))->count(),
        ];
    }

    /**
     * @param Collection<int, array<string, mixed>> $tasks
     * @param array<int, array<string, mixed>> $timeBlocks
     * @return array<string, array<int, array<string, mixed>>>
     */
    private function prepareTodoistUpdates(Collection $tasks, array $timeBlocks): array
    {
        $updates = [
            'schedule_updates' => [],
            'duration_updates' => [],
            'order_updates' => [],
        ];

        // Map time blocks to tasks
        $taskBlocks = collect($timeBlocks)->filter(fn ($block): bool => $block['task_id'] !== null);

        foreach ($tasks as $index => $task) {
            $block = $taskBlocks->firstWhere('task_id', $task['id']);

            if ($block) {
                // Schedule update
                $updates['schedule_updates'][] = [
                    'task_id' => $task['id'],
                    'task_name' => $task['content'],
                    'time' => $block['start'],
                ];

                // Duration update
                if ($task['duration'] === null) {
                    $updates['duration_updates'][] = [
                        'task_id' => $task['id'],
                        'task_name' => $task['content'],
                        'duration' => $block['duration'],
                    ];
                }
            }

            // Order update
            $updates['order_updates'][] = [
                'task_id' => $task['id'],
                'task_name' => $task['content'],
                'order' => $index + 1,
            ];
        }

        return $updates;
    }

    private function mapPriority(int $todoistPriority): string
    {
        return match ($todoistPriority) {
            4 => 'P1',
            3 => 'P2',
            2 => 'P3',
            default => 'P4',
        };
    }

    /**
     * @param array<string, mixed> $task
     */
    private function extractEnergy(array $task): ?string
    {
        // Look for energy tags in labels or description
        $content = strtolower(($task['description'] ?? '').' '.implode(' ', $task['labels'] ?? []));

        if (str_contains($content, 'high-energy') || str_contains($content, 'haute-energie')) {
            return 'high';
        }
        if (str_contains($content, 'low-energy') || str_contains($content, 'basse-energie')) {
            return 'low';
        }

        return 'medium';
    }

    /**
     * @param array<string, mixed> $task
     */
    private function extractScheduledTime(array $task): ?string
    {
        // Extract time from due date if it has a specific time
        if (isset($task['due']['datetime'])) {
            return Carbon::parse($task['due']['datetime'])->format('H:i');
        }

        return null;
    }

    /**
     * @param array<string, mixed> $task
     */
    private function extractDuration(array $task): ?int
    {
        // Look for duration in description (format: [duration:XX] or "XX min")
        $description = $task['description'] ?? '';

        if (preg_match('/\[duration:(\d+)\]/', $description, $matches)) {
            return (int) $matches[1];
        }

        if (preg_match('/(\d+)\s*min/', $description, $matches)) {
            return (int) $matches[1];
        }

        return null;
    }

    private function getProjectName(?string $projectId): ?string
    {
        if (! $projectId) {
            return null;
        }

        // This would need to be cached or fetched from Todoist
        // For now, return the project ID
        return "Project {$projectId}";
    }

    /**
     * @param array<string, mixed> $filters
     * @return \Illuminate\Pagination\LengthAwarePaginator<int, \Illuminate\Database\Eloquent\Model>
     */
    public function list(array $filters = []): \Illuminate\Pagination\LengthAwarePaginator
    {
        // Not applicable for this service
        throw new \BadMethodCallException('Method not implemented');
    }

    public function find(int|string $id): ?\Illuminate\Database\Eloquent\Model
    {
        // Not applicable for this service
        throw new \BadMethodCallException('Method not implemented');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): \Illuminate\Database\Eloquent\Model
    {
        // Not applicable for this service
        throw new \BadMethodCallException('Method not implemented');
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(int|string $id, array $data): \Illuminate\Database\Eloquent\Model
    {
        // Not applicable for this service
        throw new \BadMethodCallException('Method not implemented');
    }

    public function delete(int|string $id): bool
    {
        // Not applicable for this service
        throw new \BadMethodCallException('Method not implemented');
    }
}
