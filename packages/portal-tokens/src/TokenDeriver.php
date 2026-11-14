<?php

namespace Platform\PortalTokens;

class TokenDeriver
{
    protected TokenDefaultResolver $resolver;

    public function __construct(?TokenDefaultResolver $resolver = null)
    {
        $this->resolver = $resolver ?? new TokenDefaultResolver;
    }

    /**
     * Pure derivation of contextual variations (hover, light, contrast, shadows, radii) from primary tokens.
     * Does NOT inject fallback defaults; preserves all explicit non-empty user values.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, string>
     */
    public function derive(array $tokens): array
    {
        // 1. Derive Brand Primary variations & Tonal Palette
        if (TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-primary')) {
            $primary = (string) $tokens['--brand-primary'];

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-primary-hover')) {
                $tokens['--brand-primary-hover'] = $this->adjustBrightness($primary, -0.15);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-primary-light')) {
                $tokens['--brand-primary-light'] = $this->hexToRgba($primary, 0.12);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--on-brand-primary')) {
                $tokens['--on-brand-primary'] = $this->getContrastTextColor($primary);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--border-focus')) {
                $tokens['--border-focus'] = $primary;
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--shadow-glow')) {
                $tokens['--shadow-glow'] = sprintf('0 0 25px %s', $this->hexToRgba($primary, 0.25));
            }

            // Generate tonal ramp (50 to 950) from --brand-primary without recalculating around custom shades
            $palette = $this->generateTonalPalette($primary);
            foreach ($palette as $shade => $shadeHex) {
                $shadeKey = "--brand-primary-{$shade}";
                if (! TokenDefaultResolver::hasNonEmptyValue($tokens, $shadeKey)) {
                    $tokens[$shadeKey] = $shadeHex;
                }
            }
        }

        // 2. Derive Brand Accent variations
        if (TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-accent')) {
            $accent = (string) $tokens['--brand-accent'];

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-accent-hover')) {
                $tokens['--brand-accent-hover'] = $this->adjustBrightness($accent, -0.12);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--brand-accent-light')) {
                $tokens['--brand-accent-light'] = $this->hexToRgba($accent, 0.12);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--on-brand-accent')) {
                $tokens['--on-brand-accent'] = $this->getContrastTextColor($accent);
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--shadow-glow-accent')) {
                $tokens['--shadow-glow-accent'] = sprintf('0 0 25px %s', $this->hexToRgba($accent, 0.25));
            }
        }

        // 3. Derive Dynamic Border Radii if --radius-base is specified
        if (TokenDefaultResolver::hasNonEmptyValue($tokens, '--radius-base')) {
            $base = (string) $tokens['--radius-base'];

            $radiiFormulas = [
                '--radius-sm' => "calc({$base} * 0.5)",
                '--radius-md' => $base,
                '--radius-lg' => "calc({$base} * 1.33)",
                '--radius-xl' => "calc({$base} * 1.75)",
                '--radius-pill' => '9999px',
            ];

            foreach ($radiiFormulas as $radiusKey => $formula) {
                if (! TokenDefaultResolver::hasNonEmptyValue($tokens, $radiusKey)) {
                    $tokens[$radiusKey] = $formula;
                }
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--btn-radius')) {
                $tokens['--btn-radius'] = "calc({$base} * 0.75)";
            }

            if (! TokenDefaultResolver::hasNonEmptyValue($tokens, '--card-radius')) {
                $tokens['--card-radius'] = "calc({$base} * 1.33)";
            }
        }

        return $tokens;
    }

    /**
     * Backward-compatible delegation: derives contextual variations and resolves missing defaults.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, string>
     */
    public function deriveTokens(array $tokens): array
    {
        $derived = $this->derive($tokens);

        return $this->resolver->resolveDefaults($derived);
    }

    /**
     * Generate full 10-shade tonal palette from a base color.
     *
     * @return array<int, string>
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

    /**
     * Derive dark-mode tokens for brand colors and contextual variations.
     *
     * @param  array<string, string>  $rootTokens
     * @return array<string, string>
     */
    public function deriveDarkTokens(array $rootTokens): array
    {
        $dark = [];

        foreach ($rootTokens as $name => $value) {
            if ($name === '--brand-primary') {
                $dark['--brand-primary'] = $value;
                $dark['--brand-primary-hover'] = $this->adjustBrightness($value, 0.15);
                $dark['--brand-primary-light'] = $this->hexToRgba($value, 0.18);
                $dark['--on-brand-primary'] = $this->getContrastTextColor($value);
                $dark['--shadow-glow'] = sprintf('0 0 35px %s', $this->hexToRgba($value, 0.40));
                $dark['--border-focus'] = $this->adjustBrightness($value, 0.15);
            } elseif ($name === '--brand-accent') {
                $dark['--brand-accent'] = $value;
                $dark['--brand-accent-hover'] = $this->adjustBrightness($value, 0.15);
                $dark['--brand-accent-light'] = $this->hexToRgba($value, 0.18);
                $dark['--on-brand-accent'] = $this->getContrastTextColor($value);
                $dark['--shadow-glow-accent'] = sprintf('0 0 30px %s', $this->hexToRgba($value, 0.35));
            }
        }

        return $dark;
    }
}
