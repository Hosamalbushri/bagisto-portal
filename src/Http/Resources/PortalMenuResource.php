<?php

namespace Platform\Portal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Platform\Portal\Services\PortalMenuTreeFilter;

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
            ? app(PortalMenuTreeFilter::class)->filterTree(
                $this->items->whereNull('parent_id')
            )
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
