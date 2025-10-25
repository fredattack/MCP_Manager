<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Enums\ExecutionStatus;
use App\Enums\WorkflowStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Workflow\CreateWorkflowRequest;
use App\Http\Requests\Workflow\ExecuteWorkflowRequest;
use App\Http\Resources\WorkflowExecutionResource;
use App\Http\Resources\WorkflowResource;
use App\Http\Resources\WorkflowStepResource;
use App\Jobs\RunWorkflowJob;
use App\Models\Workflow;
use App\Models\WorkflowExecution;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class WorkflowController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of workflows.
     */
    public function index(): AnonymousResourceCollection
    {
        $workflows = Workflow::query()
            ->where('user_id', auth()->id())
            ->with(['latestExecution'])
            ->latest()
            ->paginate(15);

        return WorkflowResource::collection($workflows);
    }

    /**
     * Store a newly created workflow.
     */
    public function store(CreateWorkflowRequest $request): WorkflowResource
    {
        $workflow = Workflow::create([
            'user_id' => auth()->id(),
            'name' => $request->input('name'),
            'description' => $request->input('description'),
            'config' => $request->input('config'),
            'status' => $request->input('status', WorkflowStatus::Draft),
        ]);

        return new WorkflowResource($workflow);
    }

    /**
     * Display the specified workflow.
     */
    public function show(Workflow $workflow): WorkflowResource
    {
        $this->authorize('view', $workflow);

        $workflow->load(['executions' => function ($query) {
            $query->latest()->limit(10);
        }]);

        return new WorkflowResource($workflow);
    }

    /**
     * Update the specified workflow.
     */
    public function update(CreateWorkflowRequest $request, Workflow $workflow): WorkflowResource
    {
        $this->authorize('update', $workflow);

        $workflow->update($request->validated());

        return new WorkflowResource($workflow);
    }

    /**
     * Remove the specified workflow.
     */
    public function destroy(Workflow $workflow): JsonResponse
    {
        $this->authorize('delete', $workflow);

        $workflow->delete();

        return response()->json(['message' => 'Workflow deleted successfully']);
    }

    /**
     * Execute a workflow.
     */
    public function execute(ExecuteWorkflowRequest $request, Workflow $workflow): WorkflowExecutionResource
    {
        $this->authorize('execute', $workflow);

        // Create execution record
        $execution = WorkflowExecution::create([
            'workflow_id' => $workflow->id,
            'user_id' => auth()->id(),
            'repository_id' => $request->input('repository_id'),
            'status' => ExecutionStatus::Pending,
        ]);

        // Dispatch job to queue
        RunWorkflowJob::dispatch($execution->id);

        return new WorkflowExecutionResource($execution->load(['workflow', 'repository']));
    }

    /**
     * Get execution status.
     */
    public function executionStatus(WorkflowExecution $execution): WorkflowExecutionResource
    {
        $this->authorize('view', $execution->workflow);

        $execution->load(['workflow', 'repository', 'steps']);

        return new WorkflowExecutionResource($execution);
    }

    /**
     * Get execution steps.
     */
    public function executionSteps(WorkflowExecution $execution): AnonymousResourceCollection
    {
        $this->authorize('view', $execution->workflow);

        $steps = $execution->steps()->orderBy('step_order')->get();

        return WorkflowStepResource::collection($steps);
    }
}
