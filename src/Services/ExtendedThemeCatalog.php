<?php

namespace Platform\Portal\Services;

use Illuminate\Support\Collection;
use Webkul\Theme\ThemeCatalog;

class ExtendedThemeCatalog extends ThemeCatalog
{
    /**
     * Build a single catalog row out of the registered config and the catalog entry,
     * falling back to the theme's own configuration for author, version, description,
     * and screenshots when the theme is not listed in Bagisto's default remote catalog.
     */
    protected function build(string $code, ?array $config, ?array $entry, Collection $channels): array
    {
        $row = parent::build($code, $config, $entry, $channels);

        if (is_array($config)) {
            $row['author'] = $row['author'] ?? $config['author'] ?? null;
            $row['version'] = $row['version'] ?? $config['version'] ?? null;
            $row['url'] = $row['url'] ?? $config['url'] ?? null;
            $row['demo_url'] = $row['demo_url'] ?? $config['demo_url'] ?? null;
            $row['screenshot'] = $row['screenshot'] ?? $this->screenshotUrl($config['screenshot'] ?? null);
            $row['rating'] = $row['rating'] ?? $config['rating'] ?? null;
            $row['tags'] = ! empty($row['tags']) ? $row['tags'] : ($config['tags'] ?? []);
            $row['description'] = $row['description'] ?? $config['description'] ?? null;
        }

        return $row;
    }
}
