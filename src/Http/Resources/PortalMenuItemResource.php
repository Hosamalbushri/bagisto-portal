<?php

namespace Platform\Portal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalMenuItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $children = $this->relationLoaded('children')
            ? $this->children->filter(fn ($c) => (bool) $c->status)->sortBy('sort_order')
            : collect();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'type' => $this->type,
            'url' => method_exists($this->resource, 'getUrl') ? $this->resource->getUrl() : $this->url,
            'target' => $this->target ?? '_self',
            'icon' => $this->icon,
            'sort_order' => (int) $this->sort_order,
            'status' => (bool) $this->status,
            'children' => PortalMenuItemResource::collection($children),
        ];
    }
}
