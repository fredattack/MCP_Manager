<?php

declare(strict_types=1);

namespace App\Services\Git;

use App\Enums\GitProvider;

class WebhookSignatureVerifier
{
    /**
     * Verify GitHub webhook signature.
     */
    public function verifyGitHub(string $payload, string $signature): bool
    {
        $secret = config('services.github.webhook_secret');

        if (empty($secret)) {
            throw new \RuntimeException('GitHub webhook secret not configured');
        }

        // GitHub sends: sha256=hash
        if (! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $hash = substr($signature, 7);
        $expectedHash = hash_hmac('sha256', $payload, $secret);

        return hash_equals($expectedHash, $hash);
    }

    /**
     * Verify GitLab webhook signature.
     */
    public function verifyGitLab(string $token): bool
    {
        $secret = config('services.gitlab.webhook_secret');

        if (empty($secret)) {
            throw new \RuntimeException('GitLab webhook secret not configured');
        }

        return hash_equals($secret, $token);
    }

    /**
     * Verify webhook signature for any provider.
     */
    public function verify(GitProvider $provider, string $payload, ?string $signature, ?string $token): bool
    {
        return match ($provider) {
            GitProvider::GITHUB => $signature !== null && $this->verifyGitHub($payload, $signature),
            GitProvider::GITLAB => $token !== null && $this->verifyGitLab($token),
        };
    }

    /**
     * Check if webhook timestamp is recent (within 5 minutes).
     */
    public function isRecentTimestamp(?string $timestamp): bool
    {
        if ($timestamp === null) {
            return true; // No timestamp header, skip check
        }

        $webhookTime = (int) $timestamp;
        $currentTime = time();
        $maxAge = 300; // 5 minutes

        return abs($currentTime - $webhookTime) <= $maxAge;
    }
}
