<?php
// app/Services/Payment/PaymentGatewayInterface.php
namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;

interface PaymentGatewayInterface
{
    public function charge(Order $order, array $data = []): Payment;

    public function refund(Payment $payment, float $amount, ?string $reason = null): Payment;

    public function handleWebhook(array $payload, array $headers = []): void;
}
