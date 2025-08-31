<?php

declare(strict_types=1);

namespace App\Actions\DailyPlanning;

use App\Actions\ActionResult;
use App\Actions\BaseAction;
use App\Models\User;
use App\Services\TodoistService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class UpdateTodoistTasksAction extends BaseAction
{
    public function __construct(
        private readonly TodoistService $todoistService
    ) {}

    protected function validate(...$parameters): array
    {
        [$user, $planningId, $updates] = $parameters;

        return validator([
            'planning_id' => $planningId,
            'updates' => $updates,
        ], [
            'planning_id' => 'required|string',
            'updates' => 'required|array',
            'updates.type' => 'required|string|in:all,partial,none',
            'updates.selected' => 'required_if:updates.type,partial|array',
            'updates.selected.*' => 'string|in:schedule,duration,order,labels',
        ])->validate();
    }

    protected function authorize(...$parameters): bool
    {
        [$user, $planningId] = $parameters;

        // Check if user has active Todoist integration
        if (! $user->integrationAccounts()
            ->where('type', \App\Enums\IntegrationType::TODOIST)
            ->where('status', \App\Enums\IntegrationStatus::ACTIVE)
            ->exists()) {
            return false;
        }

        // Check if planning exists and belongs to user
        $planning = cache()->get("planning:{$user->id}:{$planningId}");

        return $planning !== null;
    }

    /**
     * @return mixed[]
     */
    protected function execute(array $validated, ...$parameters): array
    {
        [$user, $planningId] = $parameters;

        // Get the planning from cache
        $planning = cache()->get("planning:{$user->id}:{$planningId}");

        if (! $planning) {
            throw new \RuntimeException('Planning not found or expired');
        }

        $updateType = $validated['updates']['type'];

        if ($updateType === 'none') {
            return [
                'success' => true,
                'message' => 'Planning conservé sans modifications dans Todoist.',
                'updates_applied' => [],
            ];
        }

        // Determine which updates to apply
        $updatesToApply = $this->determineUpdates($updateType, $validated['updates']['selected'] ?? []);

        // Apply updates
        $results = $this->applyUpdates($user, $planning, $updatesToApply);

        return [
            'success' => true,
            'message' => $this->generateSuccessMessage($results),
            'updates_applied' => $results,
            'errors' => $results['errors'] ?? [],
        ];
    }

    protected function afterExecute($result, array $validated): void
    {
        if ($result['success'] && function_exists('activity')) {
            // Log the updates
            activity()
                ->causedBy(auth()->user())
                ->withProperties([
                    'update_type' => $validated['updates']['type'],
                    'updates_count' => count($result['updates_applied']),
                ])
                ->log('todoist_tasks_updated_from_planning');
        }
    }

    protected function handleError(\Throwable $throwable): ActionResult
    {
        if (str_contains($throwable->getMessage(), 'Planning not found')) {
            return ActionResult::error(
                'Le planning a expiré ou n\'existe pas.',
                ['expired' => true],
                404
            );
        }

        if ($throwable instanceof \App\Exceptions\IntegrationException) {
            return ActionResult::error(
                'Erreur de connexion à Todoist.',
                ['integration_error' => true],
                503
            );
        }

        return parent::handleError($throwable);
    }

    private function determineUpdates(string $type, array $selected): array
    {
        if ($type === 'all') {
            return ['schedule', 'duration', 'order', 'labels'];
        }

        return $selected;
    }

    private function applyUpdates(User $user, array $planning, array $updatesToApply): array
    {
        $results = [
            'schedule' => 0,
            'duration' => 0,
            'order' => 0,
            'labels' => 0,
            'errors' => [],
        ];

        $this->todoistService->setUser($user);
        $todoistUpdates = $planning['todoist_updates'];

        DB::beginTransaction();
        try {
            // Apply schedule updates
            if (in_array('schedule', $updatesToApply) && ! empty($todoistUpdates['schedule_updates'])) {
                foreach ($todoistUpdates['schedule_updates'] as $update) {
                    try {
                        $this->updateTaskSchedule($update['task_id'], $update['time']);
                        $results['schedule']++;
                    } catch (\Exception $e) {
                        $results['errors'][] = "Failed to update schedule for {$update['task_name']}: {$e->getMessage()}";
                    }
                }
            }

            // Apply duration updates (add to description)
            if (in_array('duration', $updatesToApply) && ! empty($todoistUpdates['duration_updates'])) {
                foreach ($todoistUpdates['duration_updates'] as $update) {
                    try {
                        $this->updateTaskDuration($update['task_id'], $update['duration']);
                        $results['duration']++;
                    } catch (\Exception $e) {
                        $results['errors'][] = "Failed to update duration for {$update['task_name']}: {$e->getMessage()}";
                    }
                }
            }

            // Apply order updates
            if (in_array('order', $updatesToApply) && ! empty($todoistUpdates['order_updates'])) {
                // Todoist doesn't have a direct order API, but we can add order to task content
                foreach ($todoistUpdates['order_updates'] as $update) {
                    try {
                        $this->updateTaskOrder($update['task_id'], $update['order']);
                        $results['order']++;
                    } catch (\Exception $e) {
                        $results['errors'][] = "Failed to update order for {$update['task_name']}: {$e->getMessage()}";
                    }
                }
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }

        return $results;
    }

    private function updateTaskSchedule(string $taskId, string $time): void
    {
        $today = now()->format('Y-m-d');
        $datetime = Carbon::parse("{$today} {$time}")->toIso8601String();

        $this->todoistService->updateTask($taskId, [
            'due_datetime' => $datetime,
        ]);
    }

    private function updateTaskDuration(string $taskId, int $duration): void
    {
        // Get current task to preserve description
        $task = $this->todoistService->getTask($taskId);
        $description = $task['description'] ?? '';

        // Remove existing duration markers
        $description = preg_replace('/\[duration:\d+\]/', '', $description);
        $description = preg_replace('/\d+\s*min/', '', $description);

        // Add new duration marker
        $description = trim($description." [duration:{$duration}]");

        $this->todoistService->updateTask($taskId, [
            'description' => $description,
        ]);
    }

    private function updateTaskOrder(string $taskId, int $order): void
    {
        // Get current task
        $task = $this->todoistService->getTask($taskId);
        $content = $task['content'] ?? '';

        // Remove existing order prefix if any
        $content = preg_replace('/^\d+\.\s*/', '', $content);

        // Add order prefix
        $content = "{$order}. {$content}";

        $this->todoistService->updateTask($taskId, [
            'content' => $content,
        ]);
    }

    private function generateSuccessMessage(array $results): string
    {
        $updates = [];

        if ($results['schedule'] > 0) {
            $updates[] = "{$results['schedule']} tâches reprogrammées";
        }
        if ($results['duration'] > 0) {
            $updates[] = "{$results['duration']} durées ajoutées";
        }
        if ($results['order'] > 0) {
            $updates[] = "{$results['order']} tâches réordonnées";
        }

        if ($updates === []) {
            return 'Aucune modification appliquée.';
        }

        $message = '✅ Modifications appliquées : '.implode(', ', $updates).'.';

        if (! empty($results['errors'])) {
            $message .= ' ⚠️ {'.count($results['errors']).'} erreurs rencontrées.';
        }

        return $message;
    }
}
