<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;
    public $email;

    public function __construct($token, $email)
    {
        $this->token = $token;
        $this->email = $email;
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
     */
    public function toMail(object $notifiable): MailMessage
    {
        $resetUrl = url("https://your-frontend-app.com/reset-password?token={$this->token}&email=" . urlencode($this->email));
        return (new MailMessage)
            ->subject('إعادة تعيين كلمة المرور')
            ->line('لقد طلبت إعادة تعيين كلمة المرور.')
            ->line('رمز التحقق الخاص بك هو: ' . $this->token)
            ->action('إعادة تعيين كلمة المرور', $resetUrl)
            ->line('إذا لم تطلب هذا الإجراء، فلا حاجة لاتخاذ أي إجراء.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
