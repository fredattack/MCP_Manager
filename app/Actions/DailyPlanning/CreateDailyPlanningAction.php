<?php

declare(strict_types=1);

namespace App\Actions\DailyPlanning;

use App\Actions\ActionResult;
use App\Actions\BaseAction;
use App\Models\User;
use App\Services\DailyPlanningService;
use Illuminate\Support\Facades\Log;

class CreateDailyPlanningAction extends BaseAction
{
    public function __construct(
        private readonly DailyPlanningService $dailyPlanningService
    ) {}

    /**
     * @param mixed ...$parameters
     * @return array<string, mixed>
     */
    protected function validate(mixed ...$parameters): array
    {
        [$user, $data] = $parameters;

        if (!is_array($data)) {
            $data = [];
        }

        $validator = validator($data, [
            'date' => 'nullable|date|date_format:Y-m-d',
            'chronotype' => 'nullable|string|in:morning,evening,neutral',
            'high_energy_hours' => 'nullable|array',
            'high_energy_hours.*' => 'string|regex:/^\d{2}:\d{2}-\d{2}:\d{2}$/',
            'break_preferences' => 'nullable|array',
            'break_preferences.lunch_duration' => 'nullable|integer|min:30|max:120',
            'break_preferences.micro_break_duration' => 'nullable|integer|min:5|max:20',
            'max_tasks' => 'nullable|integer|min:3|max:10',
        ]);
        
        return $validator->validate();
    }

    /**
     * @param mixed ...$parameters
     */
    protected function authorize(mixed ...$parameters): bool
    {
        [$user] = $parameters;
        
        if (!$user instanceof User) {
            return false;
        }

        // User must have an active Todoist integration
        return $user->integrationAccounts()
            ->where('type', \App\Enums\IntegrationType::TODOIST)
            ->where('status', \App\Enums\IntegrationStatus::ACTIVE)
            ->exists();
    }

    /**
     * @param array<string, mixed> $validated
     * @param mixed ...$parameters
     * @return array<string, mixed>
     */
    protected function execute(array $validated, mixed ...$parameters): array
    {
        [$user] = $parameters;
        
        if (!$user instanceof User) {
            throw new \InvalidArgumentException('User parameter must be an instance of User');
        }

        try {
            // Generate the daily planning
            $planning = $this->dailyPlanningService->generateDailyPlanning($user, $validated);

            if (! $planning['has_tasks']) {
                return [
                    'success' => false,
                    'message' => $planning['message'],
                    'planning' => null,
                ];
            }

            // Store planning in cache for later updates
            $planningId = $this->storePlanning($user, $planning);

            return [
                'success' => true,
                'planning_id' => $planningId,
                'planning' => $planning,
                'markdown' => $this->generateMarkdown($planning),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate daily planning', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * @param mixed $result
     * @param array<string, mixed> $validated
     */
    protected function afterExecute(mixed $result, array $validated): void
    {
        if (is_array($result) && isset($result['success']) && $result['success']) {
            // Log successful planning generation
            /** @var \App\Models\User|null $authUser */
            $authUser = auth()->user();
            if ($authUser && function_exists('activity')) {
                activity()
                    ->causedBy($authUser)
                    ->withProperties([
                        'planning_id' => $result['planning_id'],
                        'task_count' => count($result['planning']['top_tasks'] ?? []),
                        'has_mit' => isset($result['planning']['mit']),
                    ])
                    ->log('daily_planning_generated');
            }
        }
    }

    protected function handleError(\Throwable $throwable): ActionResult
    {
        if ($throwable instanceof \App\Exceptions\IntegrationException) {
            return ActionResult::error(
                'Failed to connect to Todoist. Please check your integration.',
                ['integration_error' => true],
                503
            );
        }

        return parent::handleError($throwable);
    }

    /**
     * @param array<string, mixed> $planning
     */
    private function storePlanning(User $user, array $planning): string
    {
        $planningId = uniqid('planning_');

        cache()->put(
            "planning:{$user->id}:{$planningId}",
            $planning,
            now()->addHours(24)
        );

        return $planningId;
    }

    /**
     * @param array<string, mixed> $planning
     */
    private function generateMarkdown(array $planning): string
    {
        $date = now()->format('l, F j, Y');
        $markdown = "# 📅 Planning du {$date}\n\n";

        // MIT Section
        if (isset($planning['mit']) && is_array($planning['mit'])) {
            $mit = $planning['mit'];
            $content = $mit['content'] ?? '';
            $projectName = $mit['project_name'] ?? '';
            $priority = $mit['priority'] ?? '';
            $markdown .= "## 🎯 MIT du jour\n";
            $markdown .= "**{$content}** - Projet : {$projectName} - Priorité : {$priority}\n";
            $markdown .= "*Objectif : Cette tâche seule rendrait ma journée réussie*\n\n";
        }

        // Top 6 Tasks
        $markdown .= "## 📋 Top 6 Tâches (Méthode Ivy Lee)\n\n";
        $topTasks = $planning['top_tasks'] ?? [];
        if (is_array($topTasks)) {
            foreach ($topTasks as $index => $task) {
                if (is_array($task)) {
                    $num = is_int($index) ? $index + 1 : 1;
                    $content = $task['content'] ?? '';
                    $projectName = $task['project_name'] ?? '';
                    $priority = $task['priority'] ?? '';
                    $duration = $task['duration'] ?? '45';
                    $markdown .= "{$num}. **{$content}** - Projet : {$projectName} - {$priority} - ⏱️ {$duration} min\n";
                }
            }
        }
        $markdown .= "\n";

        // Time Blocks
        $markdown .= "## 🕐 Planning Time-Blocked\n\n";

        $timeBlocks = $planning['time_blocks'] ?? [];
        $morningBlocks = is_array($timeBlocks) ? array_filter($timeBlocks, fn ($b): bool => is_array($b) && ($b['period'] ?? '') === 'morning') : [];
        $afternoonBlocks = is_array($timeBlocks) ? array_filter($timeBlocks, fn ($b): bool => is_array($b) && ($b['period'] ?? '') === 'afternoon') : [];

        if ($morningBlocks !== []) {
            $markdown .= "### Matin\n";
            foreach ($morningBlocks as $morningBlock) {
                if (is_array($morningBlock)) {
                    $start = $morningBlock['start'] ?? '';
                    $end = $morningBlock['end'] ?? '';
                    $title = $morningBlock['title'] ?? '';
                    $markdown .= "- **{$start} - {$end}** : {$title}\n";
                }
            }
            $markdown .= "\n";
        }

        if ($afternoonBlocks !== []) {
            $markdown .= "### Après-midi\n";
            foreach ($afternoonBlocks as $afternoonBlock) {
                if (is_array($afternoonBlock)) {
                    $start = $afternoonBlock['start'] ?? '';
                    $end = $afternoonBlock['end'] ?? '';
                    $title = $afternoonBlock['title'] ?? '';
                    $markdown .= "- **{$start} - {$end}** : {$title}\n";
                }
            }
            $markdown .= "\n";
        }

        // Execution Rules
        $markdown .= "## 📌 Règles d'exécution\n";
        $markdown .= "1. ⛔ Ne PAS passer à la tâche suivante tant que la précédente n'est pas terminée\n";
        $markdown .= "2. 📱 Mode avion/Ne pas déranger pendant les blocs de travail\n";
        $markdown .= "3. ⏰ Respecter strictement les horaires des blocs\n";
        $markdown .= "4. 🔄 Si une tâche prend plus de temps : décaler les suivantes, pas les comprimer\n\n";

        // Additional Tasks
        if (! empty($planning['additional_tasks'])) {
            $markdown .= "## 🎪 Tâches additionnelles (si temps disponible)\n";
            foreach ($planning['additional_tasks'] as $task) {
                $markdown .= "- {$task['content']} ({$task['priority']})\n";
            }
            $markdown .= "\n";
        }

        // Success Metrics
        $markdown .= "## 📊 Métriques de succès\n";
        $markdown .= "- [ ] MIT complétée avant 12h\n";
        $markdown .= "- [ ] 6 tâches principales terminées\n";
        $markdown .= "- [ ] Respect des time blocks à 80% minimum\n";
        $markdown .= "- [ ] Pauses prises comme planifiées\n\n";

        // Alerts
        $alerts = $planning['alerts'] ?? [];
        if (is_array($alerts) && ! empty($alerts)) {
            $markdown .= "## ⚠️ Alertes\n";
            foreach ($alerts as $alert) {
                if (is_array($alert)) {
                    $severity = $alert['severity'] ?? 'medium';
                    $icon = $severity === 'high' ? '🔴' : '🟡';
                    $type = $alert['type'] ?? '';
                    $message = $alert['message'] ?? '';
                    $markdown .= "{$icon} **{$type}** : {$message}\n";
                    $details = $alert['details'] ?? [];
                    if (is_array($details) && ! empty($details)) {
                        foreach ($details as $detail) {
                            if (is_array($detail)) {
                                $task1 = $detail['task1'] ?? '';
                                $task2 = $detail['task2'] ?? '';
                                $time = $detail['time'] ?? '';
                                $markdown .= "   - {$task1} vs {$task2} à {$time}\n";
                            }
                        }
                    }
                }
            }
            $markdown .= "\n";
        }

        return $markdown;
    }
}
