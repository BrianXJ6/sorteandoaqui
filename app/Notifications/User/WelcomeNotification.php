<?php

namespace App\Notifications\User;

use Illuminate\Notifications\Notification;

class WelcomeNotification extends Notification
{
    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @param object $notifiable
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'icon' => '<i class="bi bi-chat-right-heart fs-3 text-danger"></i>',
            'title' => __('notifications.welcome.title'),
            'message' => __('notifications.welcome.message', ['Nick' => $notifiable->nick]),
        ];
    }
}
