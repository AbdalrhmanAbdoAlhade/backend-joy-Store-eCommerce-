<?php
// app/Http/Resources/Auth/UserResource.php
namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'avatar' => $this->avatar ? asset('storage/' . $this->avatar) : null,
            'status' => $this->status,
            'loyalty_points' => $this->loyalty_points,
            'wallet_balance' => $this->when($request->user()?->id === $this->id, $this->wallet_balance),
            'email_verified_at' => $this->email_verified_at,
            'phone_verified_at' => $this->phone_verified_at,
            'roles' => $this->whenLoaded('roles', fn () => $this->getRoleNames()),
            'permissions' => $this->when($request->user()?->isEmployee(), fn () => $this->getAllPermissions()->pluck('name')),
            'default_address' => new AddressResource($this->whenLoaded('defaultAddress')),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
