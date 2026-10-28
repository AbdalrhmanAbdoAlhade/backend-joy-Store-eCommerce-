<?php
// app/Services/Payment/Drivers/CashOnDeliveryDriver.php
namespace App\Services\Payment\Drivers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Payment\PaymentGatewayInterface;

class CashOnDeliveryDriver implements PaymentGatewayInterface
{
    public function charge(Order $order, array $data = []): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'gateway' => 'cod',
            'amount' => $order->total,
            'currency' => 'EGP',
            'status' => 'pending',
            'idempotency_key' => $data['idempotency_key'] ?? null,
        ]);
    }

    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment
    {
        // COD يتم التعامل مع الاسترجاع يدوياً
        $payment->update(['status' => 'refunded']);
        return $payment;
    }

    public function handleWebhook(array $payload, array $headers = []): void
    {
        // لا يوجد webhook لـ COD
    }
}
