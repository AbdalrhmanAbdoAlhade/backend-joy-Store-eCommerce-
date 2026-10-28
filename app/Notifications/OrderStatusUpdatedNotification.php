<?php
// app/Notifications/OrderStatusUpdatedNotification.php
namespace App\Notifications;

use App\Models\Order;
use App\Enums\OrderStatus;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Order $order,
        public OrderStatus $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $statusLabels = [
            'confirmed' => 'تم تأكيد الطلب',
            'processing' => 'جاري تجهيز الطلب',
            'ready_to_ship' => 'الطلب جاهز للشحن',
            'shipped' => 'تم شحن الطلب',
            'out_for_delivery' => 'الطلب في الطريق إليك',
            'delivered' => 'تم توصيل الطلب',
            'cancelled' => 'تم إلغاء الطلب',
        ];

        $label = $statusLabels[$this->status->value] ?? $this->status->value;

        $mail = (new MailMessage)
            ->subject($label . ' - #' . $this->order->order_number)
            ->greeting('مرحباً ' . $notifiable->name)
            ->line($label)
            ->line('رقم الطلب: ' . $this->order->order_number);

        if ($this->order->tracking_number) {
            $mail->line('رقم التتبع: ' . $this->order->tracking_number);
            if ($this->order->tracking_url) {
                $mail->action('تتبع الشحنة', $this->order->tracking_url);
            }
        }

        return $mail->action('عرض الطلب', url('/orders/' . $this->order->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_status_updated',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->status->value,
            'tracking_number' => $this->order->tracking_number,
            'message' => 'تم تحديث حالة طلبك #' . $this->order->order_number . ' إلى ' . $this->status->value,
        ];
    }
}
