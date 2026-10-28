<?php
// tests/Feature/Order/OrderStateMachineTest.php
namespace Tests\Feature\Order;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\User;
use App\Services\Order\OrderStateMachine;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStateMachineTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_transition(): void
    {
        $order = Order::factory()->create(['status' => OrderStatus::Pending]);
        $admin = User::factory()->create(['is_admin' => true]);

        $service = app(OrderStateMachine::class);
        $updated = $service->transition($order, OrderStatus::Confirmed, $admin);

        $this->assertEquals(OrderStatus::Confirmed, $updated->status);
        $this->assertDatabaseHas('order_status_histories', [
            'order_id' => $order->id,
            'to_status' => 'confirmed',
        ]);
    }

    public function test_invalid_transition_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $order = Order::factory()->create(['status' => OrderStatus::Delivered]);
        $service = app(OrderStateMachine::class);

        $service->transition($order, OrderStatus::Pending);
    }
}
