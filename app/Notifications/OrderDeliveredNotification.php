<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(protected Order $order)
    {
        //
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->line('تم استلام طلبك بنجاح')
            ->line('شكرا لاستخدامك متجرنا');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */

     public function toDatabase($notifiable)
    {
        return [
            'id' => $this->order->id,
            'total' => $this->order->total,
        ];
    }
    public function toArray(object $notifiable): array
    {
        return [
            'id' => $this->order->id,
            'total' => $this->order->total,
        ];
    }
}
