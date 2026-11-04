<?php

namespace Platform\Portal\Services;

class TokenCompilerService
{
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
            $darkOverrides = $this->sanitizeTokens($customTokens['dark']);
            unset($customTokens['dark']);
        }

        foreach ($customTokens as $key => $value) {
            if (str_starts_with($key, '--dark-')) {
                $actualKey = '--'.substr($key, 7);
                $darkOverrides[$actualKey] = $value;
                unset($customTokens[$key]);
            }
        }

        $tokens = $this->sanitizeTokens($customTokens);

        // Derive missing contextual tokens (hover, light, contrast, shadows, radii)
        $tokens = $this->deriveTokens($tokens);

        if (empty($tokens) && empty($darkOverrides) && empty($customCss)) {
            return '';
        }

        $rootCss = ":root {\n";
        $darkCss = ".dark,\n[data-theme=\"dark\"] {\n";

        // 1. Output Root Tokens
        foreach ($tokens as $name => $value) {
            $rootCss .= "  {$name}: {$value};\n";

            // If it's a primary brand color, keep it vibrant in dark mode with dark-specific hover/light/glow
            if ($name === '--brand-primary') {
                $darkCss .= "  --brand-primary: {$value};\n";
                $darkCss .= "  --brand-primary-hover: {$this->adjustBrightness($value, 0.15)};\n";
                $darkCss .= "  --brand-primary-light: {$this->hexToRgba($value, 0.18)};\n";
                $darkCss .= "  --on-brand-primary: {$this->getContrastTextColor($value)};\n";
                $darkCss .= "  --shadow-glow: 0 0 35px {$this->hexToRgba($value, 0.40)};\n";
                $darkCss .= "  --border-focus: {$this->adjustBrightness($value, 0.15)};\n";
            } elseif ($name === '--brand-accent') {
                $darkCss .= "  --brand-accent: {$value};\n";
                $darkCss .= "  --brand-accent-hover: {$this->adjustBrightness($value, 0.15)};\n";
                $darkCss .= "  --brand-accent-light: {$this->hexToRgba($value, 0.18)};\n";
                $darkCss .= "  --on-brand-accent: {$this->getContrastTextColor($value)};\n";
                $darkCss .= "  --shadow-glow-accent: 0 0 30px {$this->hexToRgba($value, 0.35)};\n";
            }
        }

        // 2. Output Explicit Dark Overrides
        foreach ($darkOverrides as $darkName => $darkVal) {
            $darkCss .= "  {$darkName}: {$darkVal};\n";
        }

        $rootCss .= "}\n";
        $darkCss .= "}\n";

        $fontsUrl = $this->getGoogleFontsUrl($tokens);
        $fontImport = $fontsUrl ? "@import url('{$fontsUrl}');\n\n" : '';

        // Global container utility class and universal token bindings
        $layoutUtilities = "\n.portal-container {\n  width: 100%;\n  max-width: var(--container-max-width, 1280px);\n  margin-inline: auto;\n  padding-inline: var(--page-padding-x, 1rem);\n}\n"
            .".rounded-card, .portal-card, [data-portal-card], .glass-card, [data-spotlight] {\n  border-radius: var(--card-radius, 1rem) !important;\n}\n"
            .".rounded-btn {\n  border-radius: var(--btn-radius, 0.5rem) !important;\n}\n"
            .".portal-header, header[data-portal-header], header.portal-header {\n  background-color: var(--header-bg, var(--bg-surface)) !important;\n  color: var(--header-text, var(--text-primary)) !important;\n  min-height: var(--header-height, 4rem);\n}\n"
            .".portal-footer, footer[data-portal-footer], footer.portal-footer {\n  background-color: var(--footer-bg, var(--bg-surface-elevated)) !important;\n  color: var(--footer-text, var(--text-secondary)) !important;\n}\n"
            .".btn-primary, .primary-button, [data-portal-btn=\"primary\"], button.primary-button, a.primary-button {\n  border-radius: var(--btn-radius, 0.5rem) !important;\n  background-color: var(--brand-primary) !important;\n  color: var(--on-brand-primary, #ffffff) !important;\n}\n"
            .".btn-primary:hover, .primary-button:hover, button.primary-button:hover, a.primary-button:hover {\n  background-color: var(--brand-primary-hover) !important;\n}\n"
            .".btn-accent, [data-portal-btn=\"accent\"] {\n  border-radius: var(--btn-radius, 0.5rem) !important;\n  background-color: var(--brand-accent) !important;\n  color: var(--on-brand-accent, #090d16) !important;\n}\n"
            .".btn-accent:hover {\n  background-color: var(--brand-accent-hover) !important;\n}\n"
            .".btn-secondary, .secondary-button, [data-portal-btn=\"secondary\"], button.secondary-button, a.secondary-button, .btn-outline, .btn-ghost {\n  border-radius: var(--btn-radius, 0.5rem) !important;\n}\n"
            ."input[type=\"text\"], input[type=\"email\"], input[type=\"password\"], input[type=\"number\"], input[type=\"search\"], input[type=\"tel\"], input[type=\"url\"], select, select.custom-select, textarea {\n  border-radius: var(--btn-radius, 0.5rem) !important;\n}\n"
            ."[dir=\"rtl\"], [dir=\"rtl\"] * {\n  font-family: var(--font-arabic, 'Cairo', system-ui, sans-serif);\n}\n"
            ."[dir=\"ltr\"] {\n  font-family: var(--font-latin, 'Inter', system-ui, sans-serif);\n}\n";

        $compiled = $fontImport.$rootCss."\n".$darkCss.$layoutUtilities;

        $cleanCustomCss = $this->sanitizeCustomCss($customCss);
        if (filled($cleanCustomCss)) {
            $compiled .= "\n/* Additional Custom CSS */\n".$cleanCustomCss."\n";
        }

        return $compiled;
    }

    /**
     * Derive contextual variations (hover, light, contrast, shadows, radii) from primary tokens.
     */
    public function deriveTokens(array $tokens): array
    {
        // 1. Derive Brand Primary variations & Tonal Palette
        if (isset($tokens['--brand-primary'])) {
            $primary = $tokens['--brand-primary'];

            if (! isset($tokens['--brand-primary-hover'])) {
                $tokens['--brand-primary-hover'] = $this->adjustBrightness($primary, -0.15);
            }

            if (! isset($tokens['--brand-primary-light'])) {
                $tokens['--brand-primary-light'] = $this->hexToRgba($primary, 0.12);
            }

            if (! isset($tokens['--on-brand-primary'])) {
                $tokens['--on-brand-primary'] = $this->getContrastTextColor($primary);
            }

            if (! isset($tokens['--border-focus'])) {
                $tokens['--border-focus'] = $primary;
            }

            if (! isset($tokens['--shadow-glow'])) {
                $tokens['--shadow-glow'] = sprintf('0 0 25px %s', $this->hexToRgba($primary, 0.25));
            }

            // Generate tonal ramp (50 to 950)
            $palette = $this->generateTonalPalette($primary);
            foreach ($palette as $shade => $shadeHex) {
                $tokens["--brand-primary-{$shade}"] = $shadeHex;
            }
        }

        // 2. Derive Brand Accent variations
        if (isset($tokens['--brand-accent'])) {
            $accent = $tokens['--brand-accent'];

            if (! isset($tokens['--brand-accent-hover'])) {
                $tokens['--brand-accent-hover'] = $this->adjustBrightness($accent, -0.12);
            }

            if (! isset($tokens['--brand-accent-light'])) {
                $tokens['--brand-accent-light'] = $this->hexToRgba($accent, 0.12);
            }

            if (! isset($tokens['--on-brand-accent'])) {
                $tokens['--on-brand-accent'] = $this->getContrastTextColor($accent);
            }

            if (! isset($tokens['--shadow-glow-accent'])) {
                $tokens['--shadow-glow-accent'] = sprintf('0 0 25px %s', $this->hexToRgba($accent, 0.25));
            }
        }

        // 3. Derive Dynamic Border Radii if --radius-base is specified
        if (isset($tokens['--radius-base'])) {
            $base = $tokens['--radius-base'];
            $tokens['--radius-sm'] = "calc({$base} * 0.5)";
            $tokens['--radius-md'] = $base;
            $tokens['--radius-lg'] = "calc({$base} * 1.33)";
            $tokens['--radius-xl'] = "calc({$base} * 1.75)";
            $tokens['--radius-pill'] = '9999px';

            if (! isset($tokens['--btn-radius'])) {
                $tokens['--btn-radius'] = "calc({$base} * 0.75)";
            }

            if (! isset($tokens['--card-radius'])) {
                $tokens['--card-radius'] = "calc({$base} * 1.33)";
            }
        }

        if (! isset($tokens['--btn-radius'])) {
            $tokens['--btn-radius'] = '0.5rem';
        }

        if (! isset($tokens['--card-radius'])) {
            $tokens['--card-radius'] = '1rem';
        }

        // 4. Derive Component and Shadow Defaults
        if (! isset($tokens['--shadow-card'])) {
            $tokens['--shadow-card'] = '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03)';
        }

        if (! isset($tokens['--header-bg'])) {
            $tokens['--header-bg'] = $tokens['--bg-surface'] ?? '#ffffff';
        }

        if (! isset($tokens['--header-text'])) {
            $tokens['--header-text'] = $tokens['--text-primary'] ?? '#090d16';
        }

        if (! isset($tokens['--footer-bg'])) {
            $tokens['--footer-bg'] = $tokens['--bg-surface-elevated'] ?? '#0f172a';
        }

        if (! isset($tokens['--footer-text'])) {
            $tokens['--footer-text'] = $tokens['--text-secondary'] ?? '#94a3b8';
        }

        // 5. Layout & Container Dimensions
        if (! isset($tokens['--container-max-width'])) {
            $tokens['--container-max-width'] = '1280px';
        }

        if (! isset($tokens['--page-padding-x'])) {
            $tokens['--page-padding-x'] = '1rem';
        }

        return $tokens;
    }

    /**
     * Generate full 10-shade tonal palette from a base color.
     */
    public function generateTonalPalette(string $baseHex): array
    {
        return [
            50 => $this->adjustBrightness($baseHex, 0.85),
            100 => $this->adjustBrightness($baseHex, 0.70),
            200 => $this->adjustBrightness($baseHex, 0.50),
            300 => $this->adjustBrightness($baseHex, 0.30),
            400 => $this->adjustBrightness($baseHex, 0.15),
            500 => $baseHex,
            600 => $this->adjustBrightness($baseHex, -0.10),
            700 => $this->adjustBrightness($baseHex, -0.25),
            800 => $this->adjustBrightness($baseHex, -0.40),
            900 => $this->adjustBrightness($baseHex, -0.60),
            950 => $this->adjustBrightness($baseHex, -0.75),
        ];
    }

    /**
     * Sanitize tokens to prevent CSS injection.
     */
    public function sanitizeTokens(array $tokens): array
    {
        $sanitized = [];

        foreach ($tokens as $key => $value) {
            if (! is_string($key) || ! str_starts_with($key, '--')) {
                continue;
            }

            if (! is_string($value) && ! is_numeric($value)) {
                continue;
            }

            if (! preg_match('/^--[a-zA-Z0-9\-_]+$/', $key)) {
                continue;
            }

            $cleanKey = $key;
            $cleanValue = strip_tags(trim((string) $value));

            // Prevent break out of CSS rule
            $cleanValue = str_replace([';', '{', '}', '<', '>'], '', $cleanValue);

            // Block CSS injection, CSS exfiltration, external resource loading, and escaping
            if (preg_match('/url\s*\(|expression\s*\(|@import|\\\|\/\*|\*\//i', $cleanValue)) {
                continue;
            }

            if (filled($cleanKey) && filled($cleanValue)) {
                $sanitized[$cleanKey] = $cleanValue;
            }
        }

        return $sanitized;
    }

    /**
     * Sanitize user-provided Custom CSS (Additional CSS).
     * Preserves valid CSS selectors, rules, media queries, and animations
     * while rigorously neutralizing HTML tags, scripts, expression(), behavior:, and XSS vectors.
     */
    public function sanitizeCustomCss(?string $css): string
    {
        if (empty($css) || ! is_string($css)) {
            return '';
        }

        // 1. Remove entire <script>...</script> blocks including content
        $clean = preg_replace('/<script\b[^>]*>(.*?)<\/script>/is', '', $css);

        // 2. Remove any HTML tags
        $clean = strip_tags($clean);

        // 3. Remove any remaining breakout tags (like </style> or <svg>)
        $clean = preg_replace('/<\/?(style|script|iframe|object|embed|svg|img|link|meta)\b[^>]*>/is', '', $clean);

        // 4. Block dangerous pseudo-protocols, legacy XSS, and expressions
        $dangerousPatterns = [
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/expression\s*\([^)]*\)/i',
            '/behavior\s*:[^;]+;/i',
            '/-moz-binding\s*:[^;]+;/i',
            '/@import\s+[^;]+;/i',
            '/data\s*:\s*text\/html/i',
            '/data\s*:\s*image\/svg\+xml/i',
        ];

        $clean = preg_replace($dangerousPatterns, '/* blocked */', $clean);

        // 5. Disallow </style inside CSS
        $clean = preg_replace('/<\/style/i', '', $clean);

        return trim((string) $clean);
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
        $dtcg = [
            '$name' => 'Bagisto Portal Design Tokens',
            '$version' => '2.0.0',
            '$description' => 'W3C DTCG & Figma Tokens Studio compliant design tokens',
            'color' => [],
            'dimension' => [],
            'typography' => [],
        ];

        foreach ($tokens as $name => $value) {
            if ($name === 'custom_css') {
                $dtcg['custom_css'] = $value;

                continue;
            }

            $cleanName = ltrim($name, '-');
            if (str_contains($cleanName, 'font')) {
                $dtcg['typography'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'fontFamily',
                ];
            } elseif (
                str_contains($cleanName, 'radius')
                || str_contains($cleanName, 'spacing')
                || str_contains($cleanName, 'width')
                || str_contains($cleanName, 'height')
                || str_contains($cleanName, 'padding')
            ) {
                $dtcg['dimension'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'dimension',
                ];
            } else {
                $dtcg['color'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'color',
                ];
            }
        }

        return $dtcg;
    }

    /**
     * Import tokens from W3C DTCG format into internal token map.
     */
    public function importDtcg(array $json): array
    {
        $imported = [];

        // Flatten colors
        if (isset($json['color']) && is_array($json['color'])) {
            foreach ($json['color'] as $key => $tokenDef) {
                $val = is_array($tokenDef) ? ($tokenDef['$value'] ?? $tokenDef['value'] ?? null) : $tokenDef;
                if ($val && (is_string($val) || is_numeric($val))) {
                    $imported['--'.ltrim($key, '-')] = (string) $val;
                }
            }
        }

        // Flatten other groups (dimensions, radii, typography)
        foreach (['dimension', 'radius', 'radii', 'typography', 'shadow'] as $group) {
            if (isset($json[$group]) && is_array($json[$group])) {
                foreach ($json[$group] as $key => $tokenDef) {
                    $val = is_array($tokenDef) ? ($tokenDef['$value'] ?? $tokenDef['value'] ?? null) : $tokenDef;
                    if ($val && (is_string($val) || is_numeric($val))) {
                        $imported['--'.ltrim($key, '-')] = (string) $val;
                    }
                }
            }
        }

        $sanitized = $this->sanitizeTokens($imported);

        if (isset($json['custom_css']) && is_string($json['custom_css'])) {
            $cleanCss = $this->sanitizeCustomCss($json['custom_css']);
            if (filled($cleanCss)) {
                $sanitized['custom_css'] = $cleanCss;
            }
        }

        return $sanitized;
    }

    /**
     * Adjust hex color brightness (+1.0 to -1.0).
     */
    public function adjustBrightness(string $hex, float $percent): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#'.$hex;
        }

        $rgb = array_map('hexdec', str_split($hex, 2));

        foreach ($rgb as &$color) {
            $adjustableLimit = $percent < 0 ? $color : 255 - $color;
            $amount = round($adjustableLimit * $percent);
            $color = max(0, min(255, $color + $amount));
        }

        return sprintf('#%02x%02x%02x', $rgb[0], $rgb[1], $rgb[2]);
    }

    /**
     * Convert Hex to rgba() string.
     */
    public function hexToRgba(string $hex, float $alpha = 1.0): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return $hex;
        }

        $rgb = array_map('hexdec', str_split($hex, 2));

        return sprintf('rgba(%d, %d, %d, %.2f)', $rgb[0], $rgb[1], $rgb[2], $alpha);
    }

    /**
     * Generate Google Fonts embed URL for any Google fonts specified in tokens.
     */
    public function getGoogleFontsUrl(array $tokens): ?string
    {
        $supportedFonts = [
            'Cairo' => 'Cairo:wght@400;500;600;700;800;900',
            'Tajawal' => 'Tajawal:wght@400;500;700;800',
            'Almarai' => 'Almarai:wght@400;700;800',
            'Readex Pro' => 'Readex+Pro:wght@400;500;600;700',
            'Alexandria' => 'Alexandria:wght@400;500;600;700',
            'IBM Plex Sans Arabic' => 'IBM+Plex+Sans+Arabic:wght@400;500;600;700',
            'Inter' => 'Inter:wght@400;500;600;700;800',
            'Plus Jakarta Sans' => 'Plus+Jakarta+Sans:wght@400;500;600;700;800',
            'Outfit' => 'Outfit:wght@400;500;600;700;800',
            'Poppins' => 'Poppins:wght@400;500;600;700;800',
            'Roboto' => 'Roboto:wght@400;500;700;900',
        ];

        $matched = [];

        foreach ($tokens as $key => $value) {
            if (! is_string($value)) {
                continue;
            }

            foreach ($supportedFonts as $fontName => $fontParam) {
                if (stripos($value, $fontName) !== false) {
                    $matched[$fontName] = $fontParam;
                }
            }
        }

        if (empty($matched)) {
            return null;
        }

        $query = implode('&family=', array_values($matched));

        return "https://fonts.googleapis.com/css2?family={$query}&display=swap";
    }

    /**
     * Calculate relative luminance for contrast calculations.
     */
    public function calculateLuminance(string $hex): float
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return 1.0;
        }

        $rgb = array_map(function ($val) {
            $c = hexdec($val) / 255.0;

            return ($c <= 0.03928) ? ($c / 12.92) : pow(($c + 0.055) / 1.055, 2.4);
        }, str_split($hex, 2));

        return 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];
    }

    /**
     * Calculate WCAG 2.1 contrast ratio between two hex colors.
     */
    public function calculateContrastRatio(string $colorA, string $colorB): float
    {
        $l1 = $this->calculateLuminance($colorA);
        $l2 = $this->calculateLuminance($colorB);

        $lighter = max($l1, $l2);
        $darker = min($l1, $l2);

        return round(($lighter + 0.05) / ($darker + 0.05), 2);
    }

    /**
     * Calculate relative luminance to determine contrasting text color (white or dark).
     */
    public function getContrastTextColor(string $hex): string
    {
        $luminance = $this->calculateLuminance($hex);

        // If luminance is high (bright background), return dark text; otherwise white.
        return $luminance > 0.45 ? '#090d16' : '#ffffff';
    }
}
