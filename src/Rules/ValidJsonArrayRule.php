<?php

namespace Platform\Portal\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidJsonArrayRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_array($value)) {
            return;
        }

        if (is_string($value)) {
            $decoded = json_decode($value, true);

            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                return;
            }
        }

        $msg = trans('portal::app.validation.json-array', ['attribute' => $attribute]);

        if ($msg === 'portal::app.validation.json-array') {
            $msg = trans('validation.json', ['attribute' => $attribute]);

            if ($msg === 'validation.json') {
                $msg = "The {$attribute} field must be a valid JSON object or array.";
            }
        }

        $fail($msg);
    }
}
