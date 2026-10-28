<?php
// app/Policies/ProductPolicy.php
namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(?User $user): bool
    {
        return true; // عام
    }

    public function view(?User $user, Product $product): bool
    {
        return $product->status === \App\Enums\ProductStatus::Active || $user?->can('products.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('products.manage');
    }

    public function update(User $user, Product $product): bool
    {
        return $user->can('products.manage');
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->can('products.manage');
    }
}
