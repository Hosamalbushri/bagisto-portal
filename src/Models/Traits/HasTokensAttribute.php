<?php

namespace Platform\Portal\Models\Traits;

use Platform\Portal\Services\Tokens\TokenSanitizer;

trait HasTokensAttribute
{
    /**
     * Mutate a token attribute with normalization.
     */
    protected function mutateTokenAttribute(string $attribute, mixed $value, bool $emptyAsNull = false): void
    {
        if (is_null($value)) {
            $this->attributes[$attribute] = null;

            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                return;
            }

            $value = $decoded;
        }

        if (is_array($value)) {
            $sanitizer = function_exists('app') && app()->bound(TokenSanitizer::class)
                ? app(TokenSanitizer::class)
                : null;

            $normalized = $sanitizer ? $sanitizer->normalizeTokens($value) : $value;

            $this->attributes[$attribute] = ($emptyAsNull && empty($normalized))
                ? null
                : json_encode($normalized);
        }
    }
}
