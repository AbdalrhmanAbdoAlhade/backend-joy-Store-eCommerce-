<?php
// app/Http/Resources/Catalog/AgeGroupResource.php
namespace App\Http\Resources\Catalog;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AgeGroupResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'min_age' => $this->min_age,
            'max_age' => $this->max_age,
        ];
    }
}
