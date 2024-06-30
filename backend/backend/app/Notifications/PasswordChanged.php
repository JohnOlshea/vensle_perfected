<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Notification;

class PasswordChanged extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct()
    {
        //
    }

    public function id($notifiable)
    {
        return Str::uuid()->toString();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
    public function toMail(object $notifiable): MailMessage
    {
	return (new MailMessage)
            ->line('Your password has been changed successfully.')
            ->line('If you did not perform this action, please contact us immediately.');
    }
     */
    public function toDatabase($notifiable)
    {
        return [
            'message' => 'Your password has been changed successfully.',
        ];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Your password has been changed successfully.',
        ];
    }

}
