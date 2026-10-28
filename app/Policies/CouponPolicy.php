<?php
// app/Policies/CouponPolicy.php
namespace App\Policies;

use App\Models\User;

class CouponPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('coupons.view') || $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('admin');
    }
}
