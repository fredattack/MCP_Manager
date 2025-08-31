<?php

namespace App\Enums;

enum IntegrationType: string
{
    case NOTION = 'notion';
    case GMAIL = 'gmail';
    case CALENDAR = 'calendar';
    case OPENAI = 'openai';
    case TODOIST = 'todoist';
    case JIRA = 'jira';
    case SENTRY = 'sentry';

    /**
     * Get the display name for the integration type.
     */
    public function displayName(): string
    {
        return match ($this) {
            self::NOTION => 'Notion',
            self::GMAIL => 'Gmail',
            self::CALENDAR => 'Google Calendar',
            self::OPENAI => 'OpenAI',
            self::TODOIST => 'Todoist',
            self::JIRA => 'JIRA',
            self::SENTRY => 'Sentry',
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
            self::CALENDAR => 'Connect to your Google Calendar',
            self::OPENAI => 'Connect to OpenAI services',
            self::TODOIST => 'Connect to TodoIst services',
            self::JIRA => 'Connect to your JIRA instance for issue tracking',
            self::SENTRY => 'Connect to Sentry for error monitoring',
        };
    }
}
