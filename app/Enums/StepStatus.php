<?php

declare(strict_types=1);

namespace App\Enums;

enum StepStatus: string
{
    case Pending = 'pending';
    case Running = 'running';
    case Completed = 'completed';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
