<?php

namespace Platform\Portal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalMenuResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $rootItems = $this->items
            ? $this->items->whereNull('parent_id')->filter(fn ($i) => (bool) $i->status)->sortBy('sort_order')
            : collect();

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'status' => (bool) $this->status,
            'items' => PortalMenuItemResource::collection($rootItems),
        ];
    }
}
