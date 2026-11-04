<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;
use Symfony\Component\Finder\Finder;

class MenuSlotScanner
{
    /**
     * Regex patterns for discovering menu slots.
     */
    protected const COMPONENT_PATTERN = '/<x-portal::menu-slot\b(?![^>]*\/>)([^>]*)>(?:.*?<\/x-portal::menu-slot>)?|<x-portal::menu-slot\b([^>]*)\/>/si';

    protected const DIRECTIVE_PATTERN = '/@portalMenuSlot\s*\(\s*[\'"]([a-zA-Z0-9_\-]+)[\'"](?:\s*,\s*[\'"]([^\'"]+)[\'"])?(?:\s*,\s*[\'"]([a-zA-Z]+)[\'"])?\s*\)/i';

    /**
     * Clear cached menu slots.
     */
    public static function clearCache(?string $themePath = null): void
    {
        Cache::forget('portal_menu_slots_'.md5((string) $themePath));
    }

    /**
     * Scan layout files for menu slots.
     *
     * @param  string|null  $themePath  Optional specific theme root path
     * @param  bool  $fresh  Force fresh disk scan bypass cache
     * @return array<string, array<string, array<string, mixed>>> Grouped by location ('header', 'footer', 'other')
     */
    public function scan(?string $themePath = null, bool $fresh = false): array
    {
        $cacheKey = 'portal_menu_slots_'.md5((string) $themePath);

        if (! $fresh && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $directories = $this->getSearchDirectories($themePath);

        $slots = [
            'header' => [],
            'footer' => [],
            'other' => [],
        ];

        foreach ($directories as $dir) {
            if (! File::isDirectory($dir)) {
                continue;
            }

            $finder = new Finder;
            $finder->files()->in($dir)->name('*.blade.php');

            foreach ($finder as $file) {
                $filePath = $file->getRealPath();
                $content = $file->getContents();

                $this->extractSlotsFromContent($content, $filePath, $slots);
            }
        }

        // Merge dynamic slots from config('portal.slots', [])
        $configSlots = config('portal.slots', []);
        if (is_array($configSlots)) {
            foreach ($configSlots as $slotId => $slotConfig) {
                if (! is_string($slotId) || empty($slotId)) {
                    continue;
                }

                $location = is_array($slotConfig) ? ($slotConfig['location'] ?? 'other') : 'other';
                $group = in_array($location, ['header', 'footer'], true) ? $location : 'other';

                $slots[$group][$slotId] = [
                    'id' => $slotId,
                    'label' => is_array($slotConfig) && ! empty($slotConfig['label'])
                        ? $slotConfig['label']
                        : $this->humanizeIdentifier($slotId),
                    'location' => $location,
                    'default' => is_array($slotConfig) ? ($slotConfig['default'] ?? null) : null,
                    'file' => 'config/portal.php',
                ];
            }
        }

        Cache::put($cacheKey, $slots, 86400);

        return $slots;
    }

    /**
     * Get directories to search for layout files.
     *
     * @return array<int, string>
     */
    protected function getSearchDirectories(?string $themePath = null): array
    {
        $dirs = [];

        // 1. Current package views layout folder
        $packageViews = dirname(__DIR__).'/Resources/views/components/layouts';
        if (File::isDirectory($packageViews)) {
            $dirs[] = $packageViews;
        }

        // 2. Active Theme or Application theme directories
        if ($themePath && File::isDirectory($themePath)) {
            $dirs[] = $themePath;
        } elseif (function_exists('themes') && ($activeTheme = themes()->current())) {
            $viewPaths = method_exists($activeTheme, 'getViewPaths') ? $activeTheme->getViewPaths() : [];

            if (empty($viewPaths) && ! empty($activeTheme->viewsPath)) {
                $rawPath = $activeTheme->viewsPath;
                $viewPaths = [str_starts_with($rawPath, '/') ? base_path(substr($rawPath, 1)) : base_path($rawPath)];
            }

            foreach ($viewPaths as $vp) {
                if (File::isDirectory($vp)) {
                    $dirs[] = $vp;
                }
            }
        }

        $appThemes = resource_path('themes');
        if (File::isDirectory($appThemes)) {
            $dirs[] = $appThemes;
        }

        $appViews = resource_path('views');
        if (File::isDirectory($appViews)) {
            $dirs[] = $appViews;
        }

        return array_unique($dirs);
    }

    /**
     * Extract slots from file content.
     */
    protected function extractSlotsFromContent(string $content, string $filePath, array &$slots): void
    {
        $inferredLocation = $this->inferLocationFromPath($filePath);

        // 1. Extract from <x-portal::menu-slot ... />
        if (preg_match_all(self::COMPONENT_PATTERN, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $attributesString = ! empty($match[1]) ? $match[1] : ($match[2] ?? '');
                $attributes = $this->parseHtmlAttributes($attributesString);

                $id = $attributes['id'] ?? null;
                if (! $id) {
                    continue;
                }

                $label = $attributes['label'] ?? null;
                $location = $attributes['location'] ?? $inferredLocation;
                $default = $attributes['default'] ?? null;

                $group = in_array($location, ['header', 'footer'], true) ? $location : 'other';

                $slots[$group][$id] = [
                    'id' => $id,
                    'label' => $label ?: $this->humanizeIdentifier($id),
                    'location' => $location,
                    'default' => $default,
                    'file' => $filePath,
                ];
            }
        }

        // 2. Extract from @portalMenuSlot('slot_id', 'label', 'location')
        if (preg_match_all(self::DIRECTIVE_PATTERN, $content, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $id = $match[1];
                $label = ! empty($match[2]) ? $match[2] : $this->humanizeIdentifier($id);
                $location = ! empty($match[3]) ? $match[3] : $inferredLocation;

                $group = in_array($location, ['header', 'footer'], true) ? $location : 'other';

                $slots[$group][$id] = [
                    'id' => $id,
                    'label' => $label,
                    'location' => $location,
                    'default' => null,
                    'file' => $filePath,
                ];
            }
        }
    }

    /**
     * Infer header or footer location from file path.
     */
    protected function inferLocationFromPath(string $filePath): string
    {
        $normalized = str_replace('\\', '/', strtolower($filePath));

        if (str_contains($normalized, '/header') || str_contains($normalized, 'header.')) {
            return 'header';
        }

        if (str_contains($normalized, '/footer') || str_contains($normalized, 'footer.')) {
            return 'footer';
        }

        return 'other';
    }

    /**
     * Parse HTML attribute string into key-value array.
     *
     * @return array<string, string>
     */
    protected function parseHtmlAttributes(string $attrString): array
    {
        $attributes = [];
        $pattern = '/([a-zA-Z0-9_\-:]+)(?:=(?:"([^"]*)"|\'([^\']*)\'|([^\s>]+)))?/i';

        if (preg_match_all($pattern, $attrString, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $match) {
                $name = strtolower($match[1]);
                $val = '';

                if (isset($match[2]) && $match[2] !== '') {
                    $val = $match[2];
                } elseif (isset($match[3]) && $match[3] !== '') {
                    $val = $match[3];
                } elseif (isset($match[4])) {
                    $val = $match[4];
                }

                // Strip leading ':' if passed as Vue/Blade prop like :id="'header_main'"
                if (str_starts_with($name, ':')) {
                    $name = substr($name, 1);
                    $val = trim($val, "'\"");
                }

                $attributes[$name] = $val;
            }
        }

        return $attributes;
    }

    /**
     * Humanize a slug/snake identifier into a readable title.
     */
    protected function humanizeIdentifier(string $id): string
    {
        $words = preg_replace('/[_\-]+/', ' ', $id);

        return ucwords(trim($words));
    }
}
