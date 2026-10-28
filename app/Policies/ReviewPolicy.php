<?php
// app/Policies/ReviewPolicy.php
namespace App\Policies;

use App\Models\Review;
use App\Models\User;

class ReviewPolicy
{
    public function create(User $user): bool
    {
        return true; // أي مسجل
    }

    public function moderate(User $user, Review $review): bool
    {
        return $user->can('reviews.moderate');
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->id === $review->user_id || $user->can('reviews.moderate');
    }
}
