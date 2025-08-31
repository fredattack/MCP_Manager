<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\DailyPlanning\CreateDailyPlanningAction;
use App\Actions\DailyPlanning\UpdateTodoistTasksAction;
use App\Enums\IntegrationType;
use App\Http\Middleware\HasActiveIntegration;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Inertia\Inertia;
use Inertia\Response;

class DailyPlanningController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(HasActiveIntegration::class.':'.IntegrationType::TODOIST->value),
        ];
    }

    public function index(): Response
    {
        return Inertia::render('daily-planning', [
            'today' => now()->format('Y-m-d'),
        ]);
    }

    public function generate(Request $request, CreateDailyPlanningAction $createDailyPlanningAction)
    {
        $actionResult = $createDailyPlanningAction->handle(auth()->user(), $request->all());

        return $actionResult->toResponse();
    }

    public function updateTasks(Request $request, UpdateTodoistTasksAction $updateTodoistTasksAction)
    {
        $actionResult = $updateTodoistTasksAction->handle(
            auth()->user(),
            $request->input('planning_id'),
            $request->input('updates', [])
        );

        return $actionResult->toResponse();
    }
}
