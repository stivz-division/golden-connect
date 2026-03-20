<?php

namespace App\Notifications\Auth;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SendVerificationCodeNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly string $code,
    ) {
        $this->onQueue('mail');
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(__('auth.otp.email_subject'))
            ->greeting(__('auth.otp.email_greeting'))
            ->line(__('auth.otp.email_line', ['code' => $this->code]))
            ->line(__('auth.otp.email_warning'));
    }
}
