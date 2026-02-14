<?php

namespace App\Notifications;

use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserCredentialsNotification extends Notification
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
            ->subject('Your PaintUp Account Credentials')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your account has been created successfully.')
            ->line('Here are your login credentials:')
            ->line('**Email:** ' . $this->email)
            ->line('**Temporary Password:** ' . $this->password)
            ->action('Login to PaintUp', $this->loginUrl)
            ->line('Please change your password after first login for security.')
            ->line('Thank you for joining PaintUp!');
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