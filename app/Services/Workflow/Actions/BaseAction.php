<?php

declare(strict_types=1);

namespace App\Services\Workflow\Actions;

use App\Models\WorkflowExecution;
use App\Services\Workflow\WorkflowEngine;

abstract class BaseAction
{
    public function __construct(
        protected WorkflowEngine $engine
    ) {}

    /**
     * Execute the action
     *
     * @return array<string, mixed>
     */
    abstract public function execute(WorkflowExecution $execution): array;

    /**
     * Get the name of the action
     */
    abstract public function getName(): string;

    /**
     * Get the description of the action
     */
    abstract public function getDescription(): string;
}
