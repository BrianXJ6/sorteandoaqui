<?php

namespace App\Notifications\User;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPasswordNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new Reset Password Notification instance.
     *
     * @param string $token
     */
    public function __construct(private string $token)
    {
        //
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     *
     * @var array
     */
    public $backoff = [20, 60];

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     *
     * @var int
     */
    public $maxExceptions = 3;

    /**
     * Get the notification's delivery channels.
     *
     * @param object $notifiable
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param object $notifiable
     *
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url(route('web.user.password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ]));

        return (new MailMessage)
            ->subject(__('notifications.reset_pass.subject'))
            ->greeting(__('notifications.reset_pass.greeting', ['Nick' => $notifiable->nick]))
            ->line(__('notifications.reset_pass.message01'))
            ->action(__('notifications.reset_pass.button'), $url)
            ->line(__('notifications.reset_pass.message02', ['count' => config('auth.passwords.users.expire')]));
    }
}
