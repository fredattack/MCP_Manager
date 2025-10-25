<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'mcp' => [
        'server_url' => env('MCP_SERVER_URL', 'http://localhost:9978'),
        'email' => env('MCP_SERVER_EMAIL'),
        'password' => env('MCP_SERVER_PASSWORD'),
        'jwt_token' => env('MCP_SERVER_JWT_TOKEN'),
        'user' => env('MCP_SERVER_USER'),
        'token' => env('MCP_API_TOKEN'),
        'default_page_id' => env('MCP_DEFAULT_PAGE_ID'),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI', env('APP_URL').'/integrations/google/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URI', env('APP_URL').'/api/git/github/oauth/callback'),
        'webhook_secret' => env('WEBHOOK_SECRET_GITHUB'),
    ],

    'gitlab' => [
        'client_id' => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect' => env('GITLAB_REDIRECT_URI', env('APP_URL').'/api/git/gitlab/oauth/callback'),
        'webhook_secret' => env('WEBHOOK_SECRET_GITLAB'),
    ],

    'git' => [
        'clone_storage' => env('GIT_CLONE_STORAGE', 'local'),
        'repo_max_size_mb' => env('REPO_MAX_SIZE_MB', 2048),
    ],

];
