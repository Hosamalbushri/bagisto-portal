<?php

namespace Platform\Portal\Http\Controllers\API;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Platform\Portal\Sections\SectionTypeRegistry;

class SectionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected SectionTypeRegistry $registry
    ) {}

    /**
     * Get list of all registered portal section types and their schemas.
     */
    public function index(Request $request): JsonResponse
    {
        $sections = $this->registry->all()->map(function ($section) {
            return [
                'code' => $section->getCode(),
                'title' => $section->getTitle(),
                'description' => $section->getDescription(),
                'icon' => $section->getIcon(),
                'is_singleton' => $section->isSingleton(),
                'is_pinned' => $section->isPinned(),
                'fields' => method_exists($section, 'getFields') ? $section->getFields() : [],
                'default_options' => method_exists($section, 'getDefaultOptions') ? $section->getDefaultOptions() : [],
            ];
        })->values();

        return response()->json([
            'data' => $sections,
        ]);
    }
}
