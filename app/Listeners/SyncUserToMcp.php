<?php

namespace App\Listeners;

use App\Events\UserCreatedInManager;
use App\Events\UserDeletedInManager;
use App\Events\UserUpdatedInManager;
use App\Jobs\SyncUserToMcpServer;

class SyncUserToMcp
{
    public function handle(UserCreatedInManager|UserUpdatedInManager|UserDeletedInManager $event): void
    {
        if (! $this->shouldSync()) {
            return;
        }

        $action = match (get_class($event)) {
            UserCreatedInManager::class => 'create',
            UserUpdatedInManager::class => 'update',
            UserDeletedInManager::class => 'delete',
        };

        if ($this->isUserInRollout($event->user)) {
            SyncUserToMcpServer::dispatch($event->user, $action);
        }
    }

    private function shouldSync(): bool
    {
        return config('mcp-server.sync.enabled', true);
    }

    private function isUserInRollout($user): bool
    {
        $rollout = config('mcp-server.rollout', []);

        if (empty($rollout['percentage']) || $rollout['percentage'] <= 0) {
            return false;
        }

        if ($rollout['percentage'] >= 100) {
            return true;
        }

        if (in_array($user->id, $rollout['allowed_user_ids'] ?? [])) {
            return true;
        }

        if (in_array($user->role->value, $rollout['allowed_roles'] ?? [])) {
            return true;
        }

        $hash = crc32($user->email);
        $percentage = ($hash % 100);

        return $percentage < $rollout['percentage'];
    }
}
