<?php
// app/Notifications/ProductBackInStockNotification.php
namespace App\Notifications;

use App\Models\ProductVariant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductBackInStockNotification extends Notification implements ShouldQueue
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
            ->subject('المنتج عاد للمخزون!')
            ->greeting('مرحباً ' . $notifiable->name)
            ->line('المنتج الذي طلبت التنبيه عليه عاد للمخزون:')
            ->line($this->variant->product->name)
            ->action('اشتري الآن', url('/products/' . $this->variant->product->slug));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'back_in_stock',
            'product_id' => $this->variant->product_id,
            'product_name' => $this->variant->product->name,
            'slug' => $this->variant->product->slug,
            'message' => 'المنتج ' . $this->variant->product->name . ' عاد للمخزون',
        ];
    }
}
