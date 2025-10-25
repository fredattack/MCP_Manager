<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Ratchet\ConnectionInterface;
use Ratchet\MessageComponentInterface;

class WebSocketController implements MessageComponentInterface
{
    protected $clients;

    protected $userConnections;

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $this->userConnections = [];
    }

    public function onOpen(ConnectionInterface $conn)
    {
        $this->clients->attach($conn);

        // Parse query string for authentication
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $params);

        if (isset($params['user_id'])) {
            $userId = $params['user_id'];
            $this->userConnections[$userId] = $conn;

            Log::info("WebSocket connection opened for user: {$userId}");

            // Send initial connection confirmation
            $conn->send(json_encode([
                'type' => 'connection',
                'status' => 'connected',
                'message' => 'WebSocket connection established',
            ]));

            // Send any pending notifications
            $this->sendPendingNotifications($userId, $conn);
        }
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $data = json_decode($msg, true);

        if (! $data) {
            return;
        }

        switch ($data['type']) {
            case 'ping':
                $from->send(json_encode(['type' => 'pong']));
                break;

            case 'auth':
                $this->handleAuth($from, $data);
                break;

            case 'subscribe':
                $this->handleSubscribe($from, $data);
                break;

            default:
                Log::warning("Unknown WebSocket message type: {$data['type']}");
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        $this->clients->detach($conn);

        // Remove from user connections
        $userId = array_search($conn, $this->userConnections, true);
        if ($userId !== false) {
            unset($this->userConnections[$userId]);
            Log::info("WebSocket connection closed for user: {$userId}");
        }
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        Log::error("WebSocket error: {$e->getMessage()}");
        $conn->close();
    }

    /**
     * Send message to specific user
     */
    public function sendToUser($userId, array $message)
    {
        if (isset($this->userConnections[$userId])) {
            $this->userConnections[$userId]->send(json_encode($message));

            return true;
        }

        // Store message for later delivery if user is not connected
        $this->storePendingNotification($userId, $message);

        return false;
    }

    /**
     * Broadcast message to all connected clients
     */
    public function broadcast(array $message)
    {
        $payload = json_encode($message);

        foreach ($this->clients as $client) {
            $client->send($payload);
        }
    }

    /**
     * Send integration status update
     */
    public function sendIntegrationUpdate($userId, $integrationId, array $data)
    {
        $this->sendToUser($userId, [
            'type' => 'integration_update',
            'integrationId' => $integrationId,
            'data' => $data,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Send server status update
     */
    public function sendServerStatusUpdate($userId, array $status)
    {
        $this->sendToUser($userId, [
            'type' => 'server_status',
            'data' => $status,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Send alert notification
     */
    public function sendAlert($userId, $level, $message, array $context = [])
    {
        $this->sendToUser($userId, [
            'type' => 'alert',
            'level' => $level, // 'info', 'warning', 'error', 'critical'
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Send metrics update
     */
    public function sendMetricsUpdate($userId, array $metrics)
    {
        $this->sendToUser($userId, [
            'type' => 'metrics_update',
            'data' => $metrics,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Handle authentication message
     */
    private function handleAuth(ConnectionInterface $conn, array $data)
    {
        if (! isset($data['token'])) {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Authentication token required',
            ]));

            return;
        }

        // Validate token (simplified for example)
        // In production, validate against actual user sessions
        $userId = $this->validateToken($data['token']);

        if ($userId) {
            $this->userConnections[$userId] = $conn;

            $conn->send(json_encode([
                'type' => 'auth',
                'status' => 'authenticated',
                'userId' => $userId,
            ]));
        } else {
            $conn->send(json_encode([
                'type' => 'error',
                'message' => 'Invalid authentication token',
            ]));
        }
    }

    /**
     * Handle subscription to specific channels
     */
    private function handleSubscribe(ConnectionInterface $conn, array $data)
    {
        if (! isset($data['channel'])) {
            return;
        }

        // Store subscription (simplified)
        // In production, maintain proper subscription management
        Log::info("Client subscribed to channel: {$data['channel']}");

        $conn->send(json_encode([
            'type' => 'subscribed',
            'channel' => $data['channel'],
        ]));
    }

    /**
     * Store pending notification for offline users
     */
    private function storePendingNotification($userId, array $message)
    {
        $key = "pending_notifications:{$userId}";
        $notifications = Cache::get($key, []);
        $notifications[] = $message;

        // Keep only last 100 notifications
        $notifications = array_slice($notifications, -100);

        Cache::put($key, $notifications, now()->addHours(24));
    }

    /**
     * Send pending notifications when user connects
     */
    private function sendPendingNotifications($userId, ConnectionInterface $conn)
    {
        $key = "pending_notifications:{$userId}";
        $notifications = Cache::get($key, []);

        if (! empty($notifications)) {
            foreach ($notifications as $notification) {
                $conn->send(json_encode($notification));
            }

            Cache::forget($key);
        }
    }

    /**
     * Validate authentication token
     */
    private function validateToken($token)
    {
        // Simplified validation - in production, check against database/session
        // Return user ID if valid, null otherwise
        return null; // Placeholder
    }
}
