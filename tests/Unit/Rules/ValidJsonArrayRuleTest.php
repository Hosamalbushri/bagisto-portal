<?php

namespace Platform\Portal\Tests\Unit\Rules;

use Platform\Portal\Rules\ValidJsonArrayRule;
use Platform\Portal\Tests\TestCase;

class ValidJsonArrayRuleTest extends TestCase
{
    public function test_passes_with_php_array(): void
    {
        $rule = new ValidJsonArrayRule();
        $failed = false;

        $rule->validate('tokens', ['key' => 'value'], function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_passes_with_valid_json_string(): void
    {
        $rule = new ValidJsonArrayRule();
        $failed = false;

        $rule->validate('tokens', '{"primary": "#1c66f6"}', function () use (&$failed) {
            $failed = true;
        });

        $this->assertFalse($failed);
    }

    public function test_fails_with_invalid_json_string(): void
    {
        $rule = new ValidJsonArrayRule();
        $failedMessage = null;

        $rule->validate('tokens', 'invalid json string', function ($message) use (&$failedMessage) {
            $failedMessage = $message;
        });

        $this->assertNotNull($failedMessage);
    }

    public function test_fails_with_scalar_value(): void
    {
        $rule = new ValidJsonArrayRule();
        $failedMessage = null;

        $rule->validate('tokens', 12345, function ($message) use (&$failedMessage) {
            $failedMessage = $message;
        });

        $this->assertNotNull($failedMessage);
    }
}
