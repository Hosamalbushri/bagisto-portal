<?php

namespace Platform\Portal\Themes;

use Illuminate\Contracts\Support\Arrayable;

class ThemeManifest implements Arrayable
{
    /**
     * Create a new ThemeManifest instance.
     */
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $author = null,
        public readonly ?string $version = '1.0.0',
        public readonly ?string $description = null,
        public readonly ?string $assetsPath = null,
        public readonly ?string $viewsPath = null,
        public readonly ?string $viewsNamespace = 'portal',
        public readonly ?string $parent = 'portal',
        public readonly array $vite = [],
        public readonly array $customize = [],
        public readonly bool $isPackage = false
    ) {}

    /**
     * Create a manifest instance from a raw array configuration.
     */
    public static function fromArray(string $code, array $data, bool $isPackage = false): self
    {
        return new self(
            code: $code,
            name: $data['name'] ?? ucfirst($code),
            author: $data['author'] ?? null,
            version: $data['version'] ?? '1.0.0',
            description: $data['description'] ?? null,
            assetsPath: $data['assets_path'] ?? "public/themes/portal/{$code}",
            viewsPath: $data['views_path'] ?? ($isPackage ? "packages/{$code}/src/Resources/views" : "resources/themes/{$code}/views"),
            viewsNamespace: $data['views_namespace'] ?? 'portal',
            parent: $data['parent'] ?? 'portal',
            vite: $data['vite'] ?? [],
            customize: $data['customize'] ?? [],
            isPackage: $isPackage
        );
    }

    /**
     * Resolve the source path for custom design tokens / CSS overrides.
     */
    public function getCssPath(): ?string
    {
        $candidatePaths = [];

        // 1. In-App Theme Path
        $candidatePaths[] = resource_path("themes/{$this->code}/assets/css/app.css");

        // 2. Package assets directory if registered in vite config
        if (! empty($this->vite['package_assets_directory'])) {
            $candidatePaths[] = base_path(trim($this->vite['package_assets_directory'], '/').'/css/app.css');
        }

        // 3. Views path parent directory check (for standalone packages)
        if (! empty($this->viewsPath)) {
            $cleanViews = trim($this->viewsPath, '/');
            $candidatePaths[] = base_path($cleanViews.'/../assets/css/app.css');
            $candidatePaths[] = base_path($cleanViews.'/../../assets/css/app.css');
            $candidatePaths[] = base_path($cleanViews.'/../Resources/assets/css/app.css');
        }

        // 4. Fallback search inside packages/ for matching theme code
        $candidatePaths[] = base_path("packages/*/{$this->code}/src/Resources/assets/css/app.css");

        foreach ($candidatePaths as $pattern) {
            if (str_contains($pattern, '*')) {
                $matches = glob($pattern);
                if (! empty($matches) && file_exists($matches[0])) {
                    return $matches[0];
                }
            } elseif (file_exists($pattern)) {
                return $pattern;
            }
        }

        return null;
    }

    /**
     * Retrieve the CSS contents if available.
     */
    public function getCssContents(): ?string
    {
        $path = $this->getCssPath();

        return ($path && file_exists($path)) ? file_get_contents($path) : null;
    }

    /**
     * Check if the theme has compiled Vite assets or active hot reloading.
     */
    public function hasBuiltAssets(): bool
    {
        if (empty($this->vite['build_directory'])) {
            return false;
        }

        $manifestPath = public_path($this->vite['build_directory'].'/manifest.json');
        $hotFilePath = ! empty($this->vite['hot_file']) ? public_path($this->vite['hot_file']) : null;

        return file_exists($manifestPath) || ($hotFilePath && file_exists($hotFilePath));
    }

    /**
     * Convert manifest to Bagisto-compatible themes.shop config array.
     */
    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'author' => $this->author,
            'version' => $this->version,
            'description' => $this->description,
            'assets_path' => $this->assetsPath,
            'views_path' => $this->viewsPath,
            'views_namespace' => $this->viewsNamespace,
            'parent' => $this->parent,
            'vite' => $this->vite,
            'customize' => $this->customize,
        ];
    }
}
