<?php

namespace Platform\Portal\Services\Tokens;

class TokenDefaultResolver
{
    /**
     * Canonical baseline static defaults.
     *
     * @var array<string, string>
     */
    protected array $staticDefaults = [
        '--btn-radius' => '0.5rem',
        '--card-radius' => '1rem',
        '--shadow-card' => '0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03)',
        '--container-max-width' => '1280px',
        '--page-padding-x' => '1rem',
    ];

    /**
     * Resolve missing defaults and contextual fallbacks while strictly preserving historical key order.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, string>
     */
    public function resolveDefaults(array $tokens): array
    {
        // 1. --btn-radius (if missing from explicit tokens and not derived from --radius-base)
        if (! self::hasNonEmptyValue($tokens, '--btn-radius')) {
            $tokens['--btn-radius'] = $this->staticDefaults['--btn-radius'];
        }

        // 2. --card-radius (if missing from explicit tokens and not derived from --radius-base)
        if (! self::hasNonEmptyValue($tokens, '--card-radius')) {
            $tokens['--card-radius'] = $this->staticDefaults['--card-radius'];
        }

        // 3. --shadow-card
        if (! self::hasNonEmptyValue($tokens, '--shadow-card')) {
            $tokens['--shadow-card'] = $this->staticDefaults['--shadow-card'];
        }

        // 4. Contextual Fallbacks
        if (! self::hasNonEmptyValue($tokens, '--header-bg')) {
            $tokens['--header-bg'] = self::hasNonEmptyValue($tokens, '--bg-surface') ? (string) $tokens['--bg-surface'] : '#ffffff';
        }

        if (! self::hasNonEmptyValue($tokens, '--header-text')) {
            $tokens['--header-text'] = self::hasNonEmptyValue($tokens, '--text-primary') ? (string) $tokens['--text-primary'] : '#090d16';
        }

        if (! self::hasNonEmptyValue($tokens, '--footer-bg')) {
            $tokens['--footer-bg'] = self::hasNonEmptyValue($tokens, '--bg-surface-elevated') ? (string) $tokens['--bg-surface-elevated'] : '#0f172a';
        }

        if (! self::hasNonEmptyValue($tokens, '--footer-text')) {
            $tokens['--footer-text'] = self::hasNonEmptyValue($tokens, '--text-secondary') ? (string) $tokens['--text-secondary'] : '#94a3b8';
        }

        // 5. Layout dimensions
        if (! self::hasNonEmptyValue($tokens, '--container-max-width')) {
            $tokens['--container-max-width'] = $this->staticDefaults['--container-max-width'];
        }

        if (! self::hasNonEmptyValue($tokens, '--page-padding-x')) {
            $tokens['--page-padding-x'] = $this->staticDefaults['--page-padding-x'];
        }

        return $tokens;
    }

    /**
     * Retrieve static defaults dictionary.
     *
     * @return array<string, string>
     */
    public function getStaticDefaults(): array
    {
        return $this->staticDefaults;
    }

    /**
     * Check if a token has a non-empty scalar value in the provided dictionary.
     * Type-safe: Accepts 0, 0.0, '0', '0px'. Rejects null, empty string, whitespace, arrays, objects, booleans.
     * Does NOT claim to perform Semantic CSS Validation.
     *
     * @param  array<string, mixed>  $tokens
     */
    public static function hasNonEmptyValue(array $tokens, string $key): bool
    {
        if (! array_key_exists($key, $tokens)) {
            return false;
        }

        $val = $tokens[$key];

        if ($val === null) {
            return false;
        }

        if (is_numeric($val)) {
            return true;
        }

        if (! is_string($val)) {
            return false;
        }

        return trim($val) !== '';
    }
}
