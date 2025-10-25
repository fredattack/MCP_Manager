<?php

declare(strict_types=1);

namespace App\Enums;

enum LLMProvider: string
{
    case OpenAI = 'openai';
    case Mistral = 'mistral';
}
