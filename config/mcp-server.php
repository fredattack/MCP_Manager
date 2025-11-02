<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | MCP Server Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL of your MCP Server instance. This should be the full URL
    | including protocol (https) and port if non-standard.
    |
    */
    'base_url' => env('MCP_SERVER_URL', 'https://localhost:8000'),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum time in seconds to wait for MCP Server responses.
    |
    */
    'timeout' => (int) env('MCP_SERVER_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Service Account Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials for the Laravel Manager service account in MCP Server.
    | This account should have admin privileges to manage users.
    |
    | Use either token-based authentication (recommended for system-to-system)
    | or email/password authentication.
    |
    */
    'service_account' => [
        'email' => env('MCP_SERVICE_ACCOUNT_EMAIL', 'laravel-manager@system.local'),
        'password' => env('MCP_SERVICE_ACCOUNT_PASSWORD'),
        'api_token' => env('MCP_API_TOKEN'),
        'auth_method' => env('MCP_AUTH_METHOD', 'token'), // 'token' or 'password'
    ],

    /*
    |--------------------------------------------------------------------------
    | Token Management
    |--------------------------------------------------------------------------
    |
    | Configuration for JWT token caching and refresh strategies.
    |
    */
    'tokens' => [
        // Time in seconds before expiration to trigger refresh
        'refresh_threshold' => (int) env('MCP_TOKEN_REFRESH_THRESHOLD', 300), // 5 minutes

        // Cache TTL for tokens in seconds
        'cache_ttl' => (int) env('MCP_TOKEN_CACHE_TTL', 1500), // 25 minutes

        // Enable automatic token refresh via scheduler
        'auto_refresh' => env('MCP_TOKEN_REFRESH_ENABLED', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | User Synchronization
    |--------------------------------------------------------------------------
    |
    | Configuration for user sync between Laravel and MCP Server.
    |
    */
    'sync' => [
        // Enable automatic user synchronization
        'enabled' => env('MCP_SYNC_ENABLED', true),

        // Queue name for sync jobs
        'queue' => env('MCP_SYNC_QUEUE', 'mcp-sync'),

        // Maximum sync retry attempts
        'max_retries' => 3,

        // Delay between retries in seconds
        'retry_delay' => 60,

        // Batch size for bulk operations
        'batch_size' => 50,
    ],

    /*
    |--------------------------------------------------------------------------
    | Service Proxy Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for proxying requests to external services via MCP Server.
    |
    */
    'proxy' => [
        'enabled' => env('MCP_PROXY_ENABLED', true),

        // Services available through MCP Server
        'services' => [
            'notion' => [
                'enabled' => true,
                'cache_ttl' => 300, // 5 minutes
            ],
            'jira' => [
                'enabled' => true,
                'cache_ttl' => 600, // 10 minutes
            ],
            'gmail' => [
                'enabled' => true,
                'cache_ttl' => 0, // No cache for emails
            ],
            'todoist' => [
                'enabled' => true,
                'cache_ttl' => 300,
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rate Limiting
    |--------------------------------------------------------------------------
    |
    | Rate limits should be slightly lower than MCP Server limits to avoid
    | hitting the server's rate limiter (MCP: 60/min, 1000/hour).
    |
    */
    'rate_limits' => [
        'per_minute' => (int) env('MCP_RATE_LIMIT_PER_MINUTE', 50),
        'per_hour' => (int) env('MCP_RATE_LIMIT_PER_HOUR', 900),

        // Delay between requests in milliseconds (optional)
        'request_delay_ms' => (int) env('MCP_REQUEST_DELAY_MS', 0),
    ],

    /*
    |--------------------------------------------------------------------------
    | Circuit Breaker
    |--------------------------------------------------------------------------
    |
    | Circuit breaker configuration to handle MCP Server failures gracefully.
    |
    */
    'circuit_breaker' => [
        'enabled' => env('MCP_CIRCUIT_BREAKER_ENABLED', true),

        // Number of consecutive failures before opening circuit
        'failure_threshold' => (int) env('MCP_CIRCUIT_BREAKER_THRESHOLD', 5),

        // Time in seconds before attempting to close circuit
        'timeout' => (int) env('MCP_CIRCUIT_BREAKER_TIMEOUT', 300), // 5 minutes

        // Cache key prefix
        'cache_prefix' => 'mcp_circuit_breaker',
    ],

    /*
    |--------------------------------------------------------------------------
    | Progressive Rollout
    |--------------------------------------------------------------------------
    |
    | Feature flags for gradual rollout of MCP integration.
    |
    */
    'rollout' => [
        // Percentage of users with MCP integration enabled (0-100)
        'percentage' => (int) env('MCP_ROLLOUT_PERCENTAGE', 100),

        // Specific roles allowed to use MCP features
        'allowed_roles' => array_filter(explode(',', env('MCP_ROLLOUT_ALLOWED_ROLES', 'admin,manager,user'))),

        // Specific user IDs for testing (comma-separated)
        'allowed_user_ids' => array_filter(array_map('intval', explode(',', env('MCP_ROLLOUT_ALLOWED_USER_IDS', '')))),
    ],

    /*
    |--------------------------------------------------------------------------
    | Monitoring & Alerting
    |--------------------------------------------------------------------------
    |
    | Configuration for monitoring MCP Server health and alerting on issues.
    |
    */
    'monitoring' => [
        'enabled' => env('MCP_MONITORING_ENABLED', true),

        // Alert channels
        'alerts' => [
            'email' => env('MCP_ALERT_EMAIL'),
            'slack_webhook' => env('MCP_ALERT_SLACK_WEBHOOK'),
        ],

        // Health check endpoint
        'health_check_url' => env('MCP_SERVER_URL', 'https://localhost:8000').'/health',

        // Health check interval in minutes
        'health_check_interval' => 5,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging Configuration
    |--------------------------------------------------------------------------
    |
    | Separate log channel for MCP integration debugging.
    |
    */
    'logging' => [
        // Log channel for MCP operations
        'channel' => env('MCP_LOG_CHANNEL', 'stack'),

        // Log level for MCP operations
        'level' => env('MCP_LOG_LEVEL', 'info'),

        // Log sync operations
        'log_sync' => env('MCP_LOG_SYNC', true),

        // Log API requests/responses (verbose)
        'log_requests' => env('MCP_LOG_REQUESTS', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Role Mapping
    |--------------------------------------------------------------------------
    |
    | Map Laravel roles to MCP Server roles.
    |
    */
    'role_mapping' => [
        'admin' => 'admin',
        'manager' => 'manager',
        'user' => 'user',
        'read_only' => 'readonly',
    ],

    /*
    |--------------------------------------------------------------------------
    | SSL/TLS Configuration
    |--------------------------------------------------------------------------
    |
    | SSL verification settings for MCP Server connections.
    |
    */
    'ssl' => [
        // Verify SSL certificates (should be true in production)
        'verify' => env('MCP_SSL_VERIFY', true),

        // Path to custom CA certificate (optional)
        'ca_cert' => env('MCP_SSL_CA_CERT'),
    ],
];
