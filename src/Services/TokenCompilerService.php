<?php

namespace Platform\Portal\Services;

use Platform\Portal\Services\Tokens\DtcgExporter;
use Platform\Portal\Services\Tokens\DtcgImporter;
use Platform\Portal\Services\Tokens\TokenCompiler;
use Platform\Portal\Services\Tokens\TokenDefaultResolver;
use Platform\Portal\Services\Tokens\TokenDeriver;
use Platform\Portal\Services\Tokens\TokenSanitizer;

class TokenCompilerService
{
    protected TokenCompiler $compiler;

    protected TokenSanitizer $sanitizer;

    protected TokenDeriver $deriver;

    protected DtcgExporter $exporter;

    protected DtcgImporter $importer;

    protected TokenDefaultResolver $resolver;

    public function __construct(
        ?TokenCompiler $compiler = null,
        ?TokenSanitizer $sanitizer = null,
        ?TokenDeriver $deriver = null,
        ?DtcgExporter $exporter = null,
        ?DtcgImporter $importer = null,
        ?TokenDefaultResolver $resolver = null
    ) {
        $this->sanitizer = $sanitizer ?? (function_exists('app') && app()->bound(TokenSanitizer::class) ? app(TokenSanitizer::class) : new TokenSanitizer);
        $this->resolver = $resolver ?? (function_exists('app') && app()->bound(TokenDefaultResolver::class) ? app(TokenDefaultResolver::class) : new TokenDefaultResolver);
        $this->deriver = $deriver ?? (function_exists('app') && app()->bound(TokenDeriver::class) ? app(TokenDeriver::class) : new TokenDeriver($this->resolver));
        $this->compiler = $compiler ?? (function_exists('app') && app()->bound(TokenCompiler::class) ? app(TokenCompiler::class) : new TokenCompiler);
        $this->exporter = $exporter ?? (function_exists('app') && app()->bound(DtcgExporter::class) ? app(DtcgExporter::class) : new DtcgExporter);
        $this->importer = $importer ?? (function_exists('app') && app()->bound(DtcgImporter::class) ? app(DtcgImporter::class) : new DtcgImporter($this->sanitizer));
    }

    /**
     * Compile CSS variables string from raw tokens and defaults.
     */
    public function compile(array $customTokens = [], ?string $themeCode = null, ?string $customCss = null): string
    {
        $customCss = $customCss ?? ($customTokens['custom_css'] ?? null);
        unset($customTokens['custom_css']);

        $darkOverrides = [];

        // Check if dark tokens are passed explicitly via nested key or prefix
        if (isset($customTokens['dark']) && is_array($customTokens['dark'])) {
            $darkOverrides = $this->sanitizer->sanitizeTokens($customTokens['dark']);
            unset($customTokens['dark']);
        }

        foreach ($customTokens as $key => $value) {
            if (str_starts_with($key, '--dark-')) {
                $actualKey = '--'.substr($key, 7);
                $darkOverrides[$actualKey] = $value;
                unset($customTokens[$key]);
            }
        }

        // 1. Sanitize
        $tokens = $this->sanitizer->sanitizeTokens($customTokens);

        // 2. Derive missing contextual tokens (hover, light, contrast, shadows, radii)
        $tokens = $this->deriver->derive($tokens);

        // 3. Resolve missing defaults (static & contextual fallbacks)
        $tokens = $this->resolver->resolveDefaults($tokens);

        // 4. Derive dark tokens for brand colors & merge explicit dark overrides
        $derivedDarkTokens = $this->deriver->deriveDarkTokens($tokens);
        $darkTokens = array_merge($derivedDarkTokens, $darkOverrides);

        $cleanCustomCss = $this->sanitizer->sanitizeCustomCss($customCss);

        if (empty($tokens) && empty($darkTokens) && empty($cleanCustomCss)) {
            return '';
        }

        // 5. Compile
        return $this->compiler->compile($tokens, $darkTokens, $cleanCustomCss);
    }

    /**
     * Derive contextual variations (hover, light, contrast, shadows, radii) from primary tokens.
     */
    public function deriveTokens(array $tokens): array
    {
        return $this->deriver->deriveTokens($tokens);
    }

    /**
     * Derive dark-mode tokens for brand colors and contextual variations.
     */
    public function deriveDarkTokens(array $tokens): array
    {
        return $this->deriver->deriveDarkTokens($tokens);
    }

    /**
     * Generate full 10-shade tonal palette from a base color.
     */
    public function generateTonalPalette(string $baseHex): array
    {
        return $this->deriver->generateTonalPalette($baseHex);
    }

    /**
     * Sanitize tokens to prevent CSS injection.
     */
    public function sanitizeTokens(array $tokens): array
    {
        return $this->sanitizer->sanitizeTokens($tokens);
    }

    /**
     * Sanitize user-provided Custom CSS (Additional CSS).
     */
    public function sanitizeCustomCss(?string $css): string
    {
        return $this->sanitizer->sanitizeCustomCss($css);
    }

    /**
     * Retrieve all available presets from configuration.
     */
    public function getPresets(): array
    {
        return config('design-tokens.presets', []);
    }

    /**
     * Retrieve a specific preset by key.
     */
    public function getPreset(string $key): ?array
    {
        return config("design-tokens.presets.{$key}");
    }

    /**
     * Export tokens in W3C Design Tokens Community Group (DTCG) specification format.
     */
    public function exportDtcg(array $tokens): array
    {
        return $this->exporter->export($tokens);
    }

    /**
     * Import tokens from W3C DTCG format into internal token map.
     */
    public function importDtcg(array $json): array
    {
        return $this->importer->import($json);
    }

    /**
     * Adjust hex color brightness (+1.0 to -1.0).
     */
    public function adjustBrightness(string $hex, float $percent): string
    {
        return $this->deriver->adjustBrightness($hex, $percent);
    }

    /**
     * Convert Hex to rgba() string.
     */
    public function hexToRgba(string $hex, float $alpha = 1.0): string
    {
        return $this->deriver->hexToRgba($hex, $alpha);
    }

    /**
     * Generate Google Fonts embed URL for any Google fonts specified in tokens.
     */
    public function getGoogleFontsUrl(array $tokens): ?string
    {
        return $this->compiler->getGoogleFontsUrl($tokens);
    }

    /**
     * Calculate relative luminance for contrast calculations.
     */
    public function calculateLuminance(string $hex): float
    {
        return $this->deriver->calculateLuminance($hex);
    }

    /**
     * Calculate WCAG 2.1 contrast ratio between two hex colors.
     */
    public function calculateContrastRatio(string $colorA, string $colorB): float
    {
        return $this->deriver->calculateContrastRatio($colorA, $colorB);
    }

    /**
     * Calculate relative luminance to determine contrasting text color (white or dark).
     */
    public function getContrastTextColor(string $hex): string
    {
        return $this->deriver->getContrastTextColor($hex);
    }
}
