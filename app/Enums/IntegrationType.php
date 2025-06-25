<?php

namespace App\Enums;

enum IntegrationType: string
{
    case NOTION = 'notion';
    case GMAIL = 'gmail';
    case OPENAI = 'openai';
    case TODOIST = 'todoist';

    /**
     * Get the display name for the integration type.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::NOTION => 'Notion',
            self::GMAIL => 'Gmail',
            self::OPENAI => 'OpenAI',
            self::TODOIST => 'Todoist',
        };
    }

    /**
     * Get the description for the integration type.
     */
    public function description(): string
    {
        return match ($this) {
            self::NOTION => 'Connect to your Notion workspace',
            self::GMAIL => 'Connect to your Gmail account',
            self::OPENAI => 'Connect to OpenAI services',
            self::TODOIST => 'Connect to TodoIst services',
        };
    }
}
