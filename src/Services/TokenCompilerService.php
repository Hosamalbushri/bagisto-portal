<?php

namespace Platform\Portal\Services;

class TokenCompilerService
{
    /**
     * Compile CSS variables string from raw tokens and defaults.
     */
    public function compile(array $customTokens = [], ?string $themeCode = null): string
    {
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

        if (empty($tokens) && empty($darkOverrides)) {
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

        return $rootCss."\n".$darkCss;
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
            $cleanName = ltrim($name, '-');
            if (str_contains($cleanName, 'font')) {
                $dtcg['typography'][$cleanName] = [
                    '$value' => $value,
                    '$type' => 'fontFamily',
                ];
            } elseif (str_contains($cleanName, 'radius') || str_contains($cleanName, 'spacing')) {
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

        return $this->sanitizeTokens($imported);
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
     * Calculate relative luminance to determine contrasting text color (white or dark).
     */
    public function getContrastTextColor(string $hex): string
    {
        $hex = ltrim($hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (strlen($hex) !== 6) {
            return '#ffffff';
        }

        $rgb = array_map(function ($val) {
            $c = hexdec($val) / 255.0;

            return ($c <= 0.03928) ? ($c / 12.92) : pow(($c + 0.055) / 1.055, 2.4);
        }, str_split($hex, 2));

        $luminance = 0.2126 * $rgb[0] + 0.7152 * $rgb[1] + 0.0722 * $rgb[2];

        // If luminance is high (bright background), return dark text; otherwise white.
        return $luminance > 0.45 ? '#090d16' : '#ffffff';
    }
}
