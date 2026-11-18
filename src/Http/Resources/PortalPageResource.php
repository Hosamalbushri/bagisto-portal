<?php

namespace Platform\Portal\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PortalPageResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $publishedSections = $this->sections
            ? $this->sections->filter(fn ($s) => (bool) $s->status)->sortBy('sort_order')
            : collect();

        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'template' => $this->template,
            'theme_code' => $this->theme_code,
            'description' => $this->description,
            'meta' => [
                'title' => $this->meta_title ?: $this->title,
                'keywords' => $this->meta_keywords,
                'description' => $this->meta_description ?: $this->description,
            ],
            'status' => (bool) $this->status,
            'tokens' => $this->tokens ?? [],
            'sections' => PortalSectionResource::collection($publishedSections),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
