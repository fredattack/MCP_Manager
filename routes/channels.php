<?php

declare(strict_types=1);

use App\Models\Workflow;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('workflows.{workflowId}', function ($user, $workflowId) {
    // Verify user owns this workflow
    $workflow = Workflow::find($workflowId);

    return $workflow && $workflow->user_id === $user->id;
});
