<?php

namespace Platform\Portal\Tests\Unit\Security;

use Tests\TestCase;

class PreviewProtectionTest extends TestCase
{
    /**
     * Unauthenticated guest users must be blocked (403) from accessing draft page previews.
     */
    public function test_unauthenticated_user_cannot_access_page_preview(): void
    {
        $response = $this->get('/pages/non-existent-or-draft-slug/preview');

        // Unauthenticated guests must be aborted with 403 Forbidden
        $response->assertStatus(403);
    }
}
