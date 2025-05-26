<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\RackResource;

class ItemResource extends JsonResource
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
            "category"   => new CategoryResource($this->whenLoaded("category")),
            "name"       => $this->name,
            "stock"      => $this->stock,
            "image"      => $this->image,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
        ];
    }
}
