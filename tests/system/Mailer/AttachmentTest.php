<?php

namespace CodeIgniter\Mailer;

use CodeIgniter\Test\CIUnitTestCase;

class AttachmentTest extends CIUnitTestCase
{
    public function testConstructorSimple()
    {
        $attachment = new Attachment(__FILE__);

        $this->assertSame('AttachmentTest.php', $attachment->getBasename());
    }

    public function testGetContentID()
    {
        $attachment = new Attachment(__FILE__);

        $this->assertStringContainsString('AttachmentTest.php@', $attachment->getContentId());
    }

    public function testGetContent()
    {
        $attachment = new Attachment(__FILE__);

        $body = $attachment->getContent();

        $this->assertNotEmpty($body);
        // Detect if string if base64_encoded
        $this->assertTrue(preg_match('%^[a-zA-Z0-9/+]*={0,2}$%', $body) === 1);
    }
}
