<?php
// app/Notifications/OrderConfirmedNotification.php
namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تأكيد الطلب #' . $this->order->order_number)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('تم استلام طلبك بنجاح.')
            ->line('رقم الطلب: ' . $this->order->order_number)
            ->line('الإجمالي: ' . number_format($this->order->total, 2) . ' ج.م')
            ->action('تتبع الطلب', url('/orders/' . $this->order->id))
            ->line('شكراً لتسوقك معنا!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_confirmed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total' => $this->order->total,
            'message' => 'تم تأكيد طلبك #' . $this->order->order_number,
        ];
    }
}
