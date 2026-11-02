<?php

namespace Platform\Portal\Tests\Unit\Security;

use Illuminate\Support\Facades\Validator;
use Platform\Portal\Http\Requests\Auth\RegisterRequest;
use Tests\TestCase;

class PasswordValidationTest extends TestCase
{
    /**
     * Password validation must enforce min 8 chars with letters and numbers.
     */
    public function test_register_request_enforces_strong_passwords(): void
    {
        $request = new RegisterRequest;
        $rules = $request->rules();

        // 1. Weak password (too short: 6 chars)
        $v1 = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ], $rules);

        $this->assertTrue($v1->fails(), 'Weak short password must fail validation.');
        $this->assertArrayHasKey('password', $v1->errors()->toArray());

        // 2. Letters only (missing numbers)
        $v2 = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'abcdefgh',
            'password_confirmation' => 'abcdefgh',
        ], $rules);

        $this->assertTrue($v2->fails(), 'Password missing numbers must fail validation.');

        // 3. Numbers only (missing letters)
        $v3 = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '12345678',
            'password_confirmation' => '12345678',
        ], $rules);

        $this->assertTrue($v3->fails(), 'Password missing letters must fail validation.');

        // 4. Valid strong password
        $v4 = Validator::make([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SecretPass123',
            'password_confirmation' => 'SecretPass123',
        ], $rules);

        $this->assertFalse($v4->fails(), 'Valid password must pass validation.');
    }
}
