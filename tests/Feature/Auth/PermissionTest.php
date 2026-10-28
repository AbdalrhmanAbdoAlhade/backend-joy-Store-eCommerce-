<?php
// tests/Feature/Auth/PermissionTest.php
namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_with_permission_can_update_order_status(): void
    {
        Permission::create(['name' => 'orders.update_status', 'guard_name' => 'web']);
        $role = Role::create(['name' => 'employee', 'guard_name' => 'web']);
        $role->givePermissionTo('orders.update_status');

        $employee = User::factory()->create();
        $employee->assignRole('employee');

        $this->assertTrue($employee->can('orders.update_status'));
    }

    public function test_regular_user_cannot_access_admin_routes(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->getJson('/api/v1/admin/users');

        $response->assertStatus(403);
    }
}
