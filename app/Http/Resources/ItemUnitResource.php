<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemUnitResource extends JsonResource
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
            "sku"        => $this->sku,
            "status"     => $this->status,
            "item"       => new ItemResource($this->whenLoaded("item")),
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
