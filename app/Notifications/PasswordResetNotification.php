<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PasswordResetNotification extends Notification
{
    /**
     * Create a new notification instance.
     */
    public function __construct(
        public string $email,
        public string $password,
        public string $loginUrl
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Password Reset - PaintUp')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your password has been reset by an administrator.')
            ->line('Here are your new login credentials:')
            ->line('**Email:** ' . $this->email)
            ->line('**New Password:** ' . $this->password)
            ->action('Login to PaintUp', $this->loginUrl)
            ->line('Please change your password after logging in for security.')
            ->line('If you did not request this change, please contact support immediately.')
            ->line('Thank you!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'email' => $this->email,
            'login_url' => $this->loginUrl,
        ];
    }
}