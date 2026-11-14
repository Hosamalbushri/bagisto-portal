<?php

namespace Platform\PortalTokens;

use Platform\PortalTokens\DtcgExporter;
use Platform\PortalTokens\DtcgImporter;
use Platform\PortalTokens\TokenCompiler;
use Platform\PortalTokens\TokenDefaultResolver;
use Platform\PortalTokens\TokenDeriver;
use Platform\PortalTokens\TokenSanitizer;

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
     * Compile scoped sparse design tokens for a Page or Section.
     *
     * @param  array<string, mixed>  $sparseTokens  Structured sparse tokens delta.
     * @param  string  $scopeType                   Scope type: 'page' or 'section'.
     * @param  int  $scopeId                        Positive entity ID (page ID or section ID).
     * @return string                               Compiled scoped CSS string, or empty string if no declarations.
     *
     * @throws \InvalidArgumentException            If scope type is invalid or scope ID is not positive.
     */
    public function compileScoped(array $sparseTokens, string $scopeType, int $scopeId): string
    {
        if (! in_array($scopeType, ['page', 'section'], true)) {
            throw new \InvalidArgumentException("Invalid scope type [{$scopeType}]. Must be 'page' or 'section'.");
        }

        if ($scopeId <= 0) {
            throw new \InvalidArgumentException("Scope ID must be a positive integer, got [{$scopeId}].");
        }

        // Custom CSS is excluded from scoped compilation in v1
        unset($sparseTokens['custom_css']);

        $darkOverrides = [];

        // Check if dark tokens are passed explicitly via nested key or prefix
        if (isset($sparseTokens['dark']) && is_array($sparseTokens['dark'])) {
            $darkOverrides = $this->sanitizer->sanitizeTokens($sparseTokens['dark']);
            unset($sparseTokens['dark']);
        }

        foreach ($sparseTokens as $key => $value) {
            if (is_string($key) && str_starts_with($key, '--dark-')) {
                $actualKey = '--'.substr($key, 7);
                $darkOverrides[$actualKey] = $value;
                unset($sparseTokens[$key]);
            }
        }

        // 1. Sanitize explicit light tokens and dark overrides
        $lightTokens = $this->sanitizer->sanitizeTokens($sparseTokens);
        $darkOverrides = $this->sanitizer->sanitizeTokens($darkOverrides);

        // 2. Local derivation on eligible light token families (without injecting platform defaults)
        $derivedLightTokens = $this->deriver->derive($lightTokens);

        // 3. Local dark derivation for eligible families (--brand-primary and --brand-accent only)
        $derivedDarkTokens = $this->deriver->deriveDarkTokens($derivedLightTokens);

        // 4. Merge explicit dark overrides over derived dark values
        $finalDarkTokens = array_merge($derivedDarkTokens, $darkOverrides);

        if (empty($derivedLightTokens) && empty($finalDarkTokens)) {
            return '';
        }

        // 5. Construct scope selectors
        if ($scopeType === 'page') {
            $lightSelector = "body.portal-page-{$scopeId}";
            $darkSelector = ".dark body.portal-page-{$scopeId},\n[data-theme=\"dark\"] body.portal-page-{$scopeId}";
        } else {
            $lightSelector = "[data-section-id=\"{$scopeId}\"]";
            $darkSelector = ".dark [data-section-id=\"{$scopeId}\"],\n[data-theme=\"dark\"] [data-section-id=\"{$scopeId}\"]";
        }

        // 6. Delegate CSS formatting
        return $this->compiler->compileScoped(
            $derivedLightTokens,
            $finalDarkTokens,
            $lightSelector,
            $darkSelector
        );
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
     * Normalize sparse tokens to canonical format before persistence.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, mixed>
     */
    public function normalizeTokens(array $tokens): array
    {
        return $this->sanitizer->normalizeTokens($tokens);
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
