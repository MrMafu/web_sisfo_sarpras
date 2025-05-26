<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReturningResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"                => $this->id,
            "borrowing"         => new BorrowingResource($this->whenLoaded("borrowing")),
            "returned_quantity" => $this->returned_quantity,
            "status"            => $this->status,
            "handled_by"        => $this->handled_by,
            "returned_at"       => $this->returned_at,
            "created_at"        => $this->created_at,
            "updated_at"        => $this->updated_at,
        ];
    }
}
