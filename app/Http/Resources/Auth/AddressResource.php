<?php
// app/Http/Resources/Auth/AddressResource.php
namespace App\Http\Resources\Auth;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'label' => $this->label,
            'governorate' => $this->governorate,
            'city' => $this->city,
            'area' => $this->area,
            'street' => $this->street,
            'building' => $this->building,
            'floor' => $this->floor,
            'apartment' => $this->apartment,
            'landmark' => $this->landmark,
            'phone' => $this->phone,
            'is_default' => $this->is_default,
            'full_address' => $this->fullAddress(),
        ];
    }
}
