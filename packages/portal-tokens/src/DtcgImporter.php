<?php

namespace Platform\PortalTokens;

class DtcgImporter
{
    protected TokenSanitizer $sanitizer;

    public function __construct(?TokenSanitizer $sanitizer = null)
    {
        $this->sanitizer = $sanitizer ?? (function_exists('app') && app()->bound(TokenSanitizer::class) ? app(TokenSanitizer::class) : new TokenSanitizer);
    }

    /**
     * Import tokens from W3C DTCG format into internal token map.
     *
     * @param  array<string, mixed>  $json
     * @return array<string, string>
     */
    public function import(array $json): array
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

        // Flatten other groups (dimensions, radii, typography, shadow)
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

        $sanitized = $this->sanitizer->sanitizeTokens($imported);

        if (isset($json['custom_css']) && is_string($json['custom_css'])) {
            $cleanCss = $this->sanitizer->sanitizeCustomCss($json['custom_css']);
            if (filled($cleanCss)) {
                $sanitized['custom_css'] = $cleanCss;
            }
        }

        return $sanitized;
    }

    /**
     * Alias for import() to maintain interface flexibility.
     *
     * @param  array<string, mixed>  $json
     * @return array<string, string>
     */
    public function importDtcg(array $json): array
    {
        return $this->import($json);
    }
}
