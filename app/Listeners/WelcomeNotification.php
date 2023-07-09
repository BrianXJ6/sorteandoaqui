<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use App\Notifications\User\WelcomeNotification as UserWelcomeNotification;

class WelcomeNotification
{
    /**
     * Handle the event.
     *
     * @param \Illuminate\Auth\Events\Registered $event
     *
     * @return void
     */
    public function handle(Registered $event): void
    {
        /** @var \App\Models\User */
        $user = $event->user;
        $user->notify(new UserWelcomeNotification);
    }
}
