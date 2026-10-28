<?php
// app/Notifications/LowStockNotification.php
namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LowStockNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public ProductVariant $variant) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('تنبيه: مخزون منخفض - ' . $this->variant->sku)
            ->line('المنتج: ' . $this->variant->product->name)
            ->line('SKU: ' . $this->variant->sku)
            ->line('المخزون المتاح: ' . $this->variant->available_stock)
            ->line('حد التنبيه: ' . $this->variant->low_stock_threshold)
            ->action('عرض المخزون', url('/admin/inventory'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock',
            'variant_id' => $this->variant->id,
            'product_name' => $this->variant->product->name,
            'sku' => $this->variant->sku,
            'available_stock' => $this->variant->available_stock,
            'message' => 'مخزون منخفض للمنتج: ' . $this->variant->product->name,
        ];
    }
}
