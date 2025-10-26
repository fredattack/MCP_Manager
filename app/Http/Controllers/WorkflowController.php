<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Workflow;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request): Response
    {
        $workflows = Workflow::query()
            ->where('user_id', $request->user()->id)
            ->with(['latestExecution.steps'])
            ->latest()
            ->get();

        return Inertia::render('Workflows/Index', [
            'workflows' => $workflows,
        ]);
    }

    public function show(Request $request, Workflow $workflow): Response
    {
        $this->authorize('view', $workflow);

        $workflow->load(['latestExecution.steps']);

        return Inertia::render('Workflows/Show', [
            'workflow' => $workflow,
        ]);
    }
}
