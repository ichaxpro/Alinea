<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'path' => $this->path,
            'url' => asset('storage/' . $this->path),
            'type' => $this->type,
            'original_name' => $this->original_name,
            'size' => $this->size,
            'sort_order' => $this->sort_order,
        ];
    }
}
