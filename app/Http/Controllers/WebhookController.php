<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\GitProvider;
use App\Services\Git\WebhookEventHandler;
use App\Services\Git\WebhookSignatureVerifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookSignatureVerifier $signatureVerifier,
        private readonly WebhookEventHandler $eventHandler
    ) {}

    /**
     * Handle GitHub webhooks.
     *
     * POST /webhooks/github
     */
    public function github(Request $request): JsonResponse
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Hub-Signature-256');
        $event = $request->header('X-GitHub-Event');
        $deliveryId = $request->header('X-GitHub-Delivery');

        // Verify signature
        if (! $this->signatureVerifier->verifyGitHub($payload, $signature ?? '')) {
            Log::warning('GitHub webhook signature verification failed', [
                'event' => $event,
                'delivery_id' => $deliveryId,
            ]);

            return response()->json(['error' => 'Invalid signature'], 403);
        }

        // Check timestamp (prevent replay attacks)
        $timestamp = $request->header('X-GitHub-Hook-Installation-Target-ID');
        if (! $this->signatureVerifier->isRecentTimestamp($timestamp)) {
            Log::warning('GitHub webhook timestamp too old', [
                'event' => $event,
                'delivery_id' => $deliveryId,
            ]);

            return response()->json(['error' => 'Request too old'], 403);
        }

        // Deduplication check
        if ($this->isDuplicate('github', $deliveryId)) {
            Log::info('GitHub webhook duplicate detected', [
                'event' => $event,
                'delivery_id' => $deliveryId,
            ]);

            return response()->json(['message' => 'Duplicate event'], 200);
        }

        // Parse payload
        $data = json_decode($payload, true);

        if ($data === null) {
            Log::error('GitHub webhook invalid JSON payload', [
                'event' => $event,
            ]);

            return response()->json(['error' => 'Invalid JSON'], 400);
        }

        // Handle event
        try {
            $this->handleGitHubEvent($event, $data);

            // Mark as processed
            $this->markAsProcessed('github', $deliveryId);

            return response()->json(['message' => 'Webhook processed'], 200);
        } catch (\Exception $e) {
            Log::error('GitHub webhook processing failed', [
                'event' => $event,
                'delivery_id' => $deliveryId,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle GitLab webhooks.
     *
     * POST /webhooks/gitlab
     */
    public function gitlab(Request $request): JsonResponse
    {
        $token = $request->header('X-Gitlab-Token');
        $event = $request->header('X-Gitlab-Event');
        $eventUuid = $request->header('X-Gitlab-Event-UUID');

        // Verify token
        if (! $this->signatureVerifier->verifyGitLab($token ?? '')) {
            Log::warning('GitLab webhook token verification failed', [
                'event' => $event,
                'uuid' => $eventUuid,
            ]);

            return response()->json(['error' => 'Invalid token'], 403);
        }

        // Deduplication check
        if ($this->isDuplicate('gitlab', $eventUuid)) {
            Log::info('GitLab webhook duplicate detected', [
                'event' => $event,
                'uuid' => $eventUuid,
            ]);

            return response()->json(['message' => 'Duplicate event'], 200);
        }

        // Parse payload
        $payload = $request->all();

        // Handle event
        try {
            $this->handleGitLabEvent($event, $payload);

            // Mark as processed
            $this->markAsProcessed('gitlab', $eventUuid);

            return response()->json(['message' => 'Webhook processed'], 200);
        } catch (\Exception $e) {
            Log::error('GitLab webhook processing failed', [
                'event' => $event,
                'uuid' => $eventUuid,
                'error' => $e->getMessage(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle GitHub event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleGitHubEvent(?string $event, array $payload): void
    {
        match ($event) {
            'push' => $this->eventHandler->handlePush(GitProvider::GITHUB, $payload),
            'pull_request' => $this->eventHandler->handlePullRequest(GitProvider::GITHUB, $payload),
            default => Log::info('GitHub event not handled', ['event' => $event]),
        };
    }

    /**
     * Handle GitLab event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleGitLabEvent(?string $event, array $payload): void
    {
        match ($event) {
            'Push Hook' => $this->eventHandler->handlePush(GitProvider::GITLAB, $payload),
            'Merge Request Hook' => $this->eventHandler->handlePullRequest(GitProvider::GITLAB, $payload),
            default => Log::info('GitLab event not handled', ['event' => $event]),
        };
    }

    /**
     * Check if event is duplicate.
     */
    private function isDuplicate(string $provider, ?string $id): bool
    {
        if ($id === null) {
            return false;
        }

        $cacheKey = "webhook:{$provider}:{$id}";

        return Cache::has($cacheKey);
    }

    /**
     * Mark event as processed (10 min TTL for deduplication).
     */
    private function markAsProcessed(string $provider, ?string $id): void
    {
        if ($id === null) {
            return;
        }

        $cacheKey = "webhook:{$provider}:{$id}";
        Cache::put($cacheKey, true, 600); // 10 minutes
    }
}
