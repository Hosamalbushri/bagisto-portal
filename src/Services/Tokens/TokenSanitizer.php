<?php

namespace Platform\Portal\Services\Tokens;

class TokenSanitizer
{
    /**
     * Sanitize tokens to prevent CSS injection.
     *
     * @param  array<string, mixed>  $tokens
     * @return array<string, string>
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
}
