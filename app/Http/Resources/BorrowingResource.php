<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BorrowingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"               => $this->id,
            "item"             => new ItemResource($this->whenLoaded("item")),
            "user"             => new UserResource($this->whenLoaded("user")),
            "quantity"         => $this->quantity,
            "status"           => $this->status,
            "due"              => $this->due,
            "approved_at"      => $this->approved_at,
            "approved_by"      => $this->approved_by,
            "borrowing_detail" => BorrowingDetailResource::collection($this->whenLoaded("borrowingDetails")),
            "returning"        => new ReturningResource($this->whenLoaded("returning")),
            "created_at"       => $this->created_at,
            "updated_at"       => $this->updated_at,
        ];
    }
}
