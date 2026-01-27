<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class UserAccountCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    private ?string $temporaryPassword;

    public function __construct(?string $temporaryPassword = null)
    {
        $this->temporaryPassword = $temporaryPassword;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Welcome to DA-PMIS - Your Account Has Been Created')
            ->greeting('Welcome, '.($notifiable->full_name ?? $notifiable->username).'!')
            ->line('Your account has been created for the Department of Agriculture - Performance Management Information System (DA-PMIS).')
            ->line('')
            ->line('**Account Details:**')
            ->line('Username: '.$notifiable->username)
            ->line('Email: '.$notifiable->email)
            ->line('Role: '.($notifiable->role->name ?? 'User'));

        if ($this->temporaryPassword) {
            $mail->line('')
                ->line('**Temporary Password:** '.$this->temporaryPassword)
                ->line('')
                ->line('Please change your password immediately after logging in for security purposes.');
        }

        $mail->line('')
            ->action('Login to DA-PMIS', url('/login'))
            ->line('If you did not expect this account, please contact the administrator.');

        return $mail;
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'account_created',
            'message' => 'Welcome to DA-PMIS! Your account has been created.',
        ];
    }
}
