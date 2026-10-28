<?php
// app/Policies/ReturnRequestPolicy.php
namespace App\Policies;

use App\Models\ReturnRequest;
use App\Models\User;

class ReturnRequestPolicy
{
    public function create(User $user): bool
    {
        return true;
    }

    public function view(User $user, ReturnRequest $return): bool
    {
        return $user->id === $return->user_id || $user->can('returns.manage');
    }

    public function manage(User $user, ReturnRequest $return): bool
    {
        return $user->can('returns.manage');
    }
}
