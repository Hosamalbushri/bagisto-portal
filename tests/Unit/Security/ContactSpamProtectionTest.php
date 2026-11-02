<?php

namespace Platform\Portal\Tests\Unit\Security;

use Illuminate\Support\Facades\Validator;
use Platform\Portal\Http\Requests\ContactRequest;
use Tests\TestCase;

class ContactSpamProtectionTest extends TestCase
{
    /**
     * Contact request with honeypot field filled by bot must fail.
     */
    public function test_contact_honeypot_blocks_spam_bots(): void
    {
        $request = new ContactRequest;
        $rules = $request->rules();

        // Legitimate user (honeypot empty)
        $validData = [
            'name' => 'Alice Doe',
            'email' => 'alice@example.com',
            'contact' => '+1234567890',
            'message' => 'Hello, I have an inquiry.',
            'website' => '',
        ];

        $v1 = Validator::make($validData, $rules);
        $this->assertFalse($v1->fails(), 'Clean submission must pass validation.');

        // Bot submission (honeypot filled)
        $spamData = array_merge($validData, [
            'website' => 'http://spam-site.com',
        ]);

        $v2 = Validator::make($spamData, $rules);
        $this->assertTrue($v2->fails(), 'Submission with honeypot filled must fail validation.');
        $this->assertArrayHasKey('website', $v2->errors()->toArray());
    }
}
