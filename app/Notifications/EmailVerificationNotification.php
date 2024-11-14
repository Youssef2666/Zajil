<?php

namespace App\Notifications;

use Ichtrojan\Otp\Otp;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Log;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    public $message;
    public $subject;
    public $fromEmail;
    public $mailer;
    private $otp;
    public $receiverEmail;
    public function __construct($receiverEmail, $otp)
    {
        $this->message = "قم باستخدام الرمز التالي لتفعيل حسابك";
        $this->subject = "تفعيل حسابك";
        $this->fromEmail = 'zajil@adimtech.com.ly';
        $this->mailer = 'adimtech.com.ly';
        $this->otp = $otp;
        $this->receiverEmail = $receiverEmail;
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
        $otp = $this->otp->generate($notifiable->email,'numeric',6,60);
        return (new MailMessage)
                    ->subject($this->subject)
                    ->greeting('Hello, '.$notifiable->name)
                    ->line($this->message)
                    ->line('Code: '.$otp->token);
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->message,
            'subject' => $this->subject,
            'fromEmail' => $this->fromEmail,
            'mailer' => $this->mailer
        ];
    }
}