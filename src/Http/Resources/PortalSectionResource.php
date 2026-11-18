<?php

namespace Platform\Portal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalSectionResource extends JsonResource
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
            'type' => $this->type,
            'name' => $this->name,
            'sort_order' => (int) $this->sort_order,
            'status' => (bool) $this->status,
            'options' => $this->options ?? [],
            'tokens' => $this->tokens ?? [],
        ];
    }
}
