<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"         => $this->id,
            "item_unit"  => new ItemUnitResource($this->whenLoaded("itemUnit")),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
