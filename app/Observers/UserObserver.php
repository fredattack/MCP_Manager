<?php

namespace App\Observers;

use App\Events\UserCreatedInManager;
use App\Events\UserDeletedInManager;
use App\Events\UserUpdatedInManager;
use App\Models\User;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        event(new UserCreatedInManager($user));
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        event(new UserUpdatedInManager($user));
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        event(new UserDeletedInManager($user));
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        // When a user is restored, treat it as an update
        event(new UserUpdatedInManager($user));
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        event(new UserDeletedInManager($user));
    }
}
