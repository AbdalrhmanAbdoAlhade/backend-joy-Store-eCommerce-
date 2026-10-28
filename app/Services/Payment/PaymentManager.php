<?php
// app/Services/Payment/PaymentManager.php
namespace App\Services\Payment;

use App\Services\Payment\Drivers\CashOnDeliveryDriver;
use App\Services\Payment\Drivers\PaymobDriver;
use InvalidArgumentException;

class PaymentManager
{
    protected array $drivers = [];

    public function driver(string $name): PaymentGatewayInterface
    {
        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        $driver = match ($name) {
            'cod' => new CashOnDeliveryDriver(),
            'paymob' => new PaymobDriver(),
            default => throw new InvalidArgumentException("Payment driver [{$name}] not supported."),
        };

        return $this->drivers[$name] = $driver;
    }
}
