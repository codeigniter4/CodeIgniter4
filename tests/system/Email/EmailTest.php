<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Email;

use CodeIgniter\Events\Events;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockEmail;
use CodeIgniter\Test\ReflectionHelper;
use ErrorException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use ReflectionException;
use ReflectionMethod;

/**
 * @internal
 */
#[Group('Others')]
final class EmailTest extends CIUnitTestCase
{
    use ReflectionHelper;

    public function testEmailValidation(): void
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new Email($config);
        $email->setTo('invalid');
        $this->assertStringContainsString('Invalid email address: "invalid"', $email->printDebugger());
    }

    /**
     * @param bool $autoClear
     */
    #[DataProvider('provideEmailSendWithClearance')]
    public function testEmailSendWithClearance($autoClear): void
    {
        $email = $this->createMockEmail();

        $email->setTo('foo@foo.com');

        $this->assertTrue($email->send($autoClear));

        if (! $autoClear) {
            $this->assertSame('foo@foo.com', $email->archive['recipients'][0]);
        }
    }

    public static function provideEmailSendWithClearance(): iterable
    {
        return [
            'autoclear'     => [true],
            'not autoclear' => [false],
        ];
    }

    public function testEmailSendStoresArchive(): void
    {
        $email = $this->createMockEmail();

        $email->setTo('foo@foo.com');
        $email->setFrom('bar@foo.com');
        $email->setSubject('Archive Test');

        $this->assertTrue($email->send());

        $this->assertNotEmpty($email->archive);
        $this->assertSame(['foo@foo.com'], $email->archive['recipients']);
        $this->assertSame('bar@foo.com', $email->archive['fromEmail']);
        $this->assertSame('Archive Test', $email->archive['subject']);
    }

    public function testAutoClearLeavesArchive(): void
    {
        $email = $this->createMockEmail();

        $email->setTo('foo@foo.com');

        $this->assertTrue($email->send(true));

        $this->assertNotEmpty($email->archive);
    }

    public function testEmailSendRepeatUpdatesArchive(): void
    {
        $config = config('Email');
        $email  = new MockEmail($config);

        $email->setTo('foo@foo.com');
        $email->setFrom('bar@foo.com');

        $this->assertTrue($email->send());

        $email->setFrom('');
        $email->setSubject('Archive Test');
        $this->assertTrue($email->send());

        $this->assertSame('', $email->archive['fromEmail']);
        $this->assertSame('Archive Test', $email->archive['subject']);
    }

    public function testSuccessDoesTriggerEvent(): void
    {
        $email = $this->createMockEmail();

        $email->setTo('foo@foo.com');

        $result = null;

        Events::on('email', static function ($arg) use (&$result): void {
            $result = $arg;
        });

        $this->assertTrue($email->send());

        $this->assertIsArray($result);
        $this->assertSame(['foo@foo.com'], $result['recipients']);
    }

    public function testFailureDoesNotTriggerEvent(): void
    {
        $email = $this->createMockEmail();

        $email->setTo('foo@foo.com');
        $email->returnValue = false;

        $result = null;

        Events::on('email', static function ($arg) use (&$result): void {
            $result = $arg;
        });

        $this->assertFalse($email->send());

        $this->assertNull($result);
    }

    public function testDestructDoesNotThrowException(): void
    {
        $email = $this->getMockBuilder(Email::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['sendCommand'])
            ->getMock();
        $email->expects($this->once())->method('sendCommand')
            ->willThrowException(new ErrorException('SMTP Error.'));

        // Force resource to be injected into the property
        $SMTPConnect = fopen(__FILE__, 'rb');
        $this->setPrivateProperty($email, 'SMTPConnect', $SMTPConnect);

        $email->__destruct();
    }

    private function createMockEmail(): MockEmail
    {
        $config           = config('Email');
        $config->validate = true;

        return new MockEmail($config);
    }

    public function testSetAttachmentCIDFile(): void
    {
        $email = $this->createMockEmail();

        $email->setFrom('your@example.com', 'Your Name');
        $email->setTo('foo@example.jp');

        $filename = SUPPORTPATH . 'Images/ci-logo.png';
        $email->attach($filename);
        $cid = $email->setAttachmentCID($filename);
        $email->setMessage('<img src="cid:' . $cid . '" alt="CI Logo">');

        $this->assertTrue($email->send());

        $this->assertStringStartsWith('ci-logo.png@', $cid);
        $this->assertStringStartsWith(
            'ci-logo.png@',
            $email->archive['attachments'][0]['cid'],
        );
        $this->assertMatchesRegularExpression(
            '/<img src="cid:ci-logo.png@(.+?)" alt="CI Logo">/u',
            $email->archive['body'],
        );
    }

    public function testSetAttachmentCIDBufferString(): void
    {
        $email = $this->createMockEmail();

        $email->setFrom('your@example.com', 'Your Name');
        $email->setTo('foo@example.jp');

        $filename  = SUPPORTPATH . 'Images/ci-logo.png';
        $imageData = file_get_contents($filename);
        $email->attach($imageData, 'inline', 'image001.png', 'image/png');
        $cid = $email->setAttachmentCID('image001.png');
        $email->setMessage('<img src="cid:' . $cid . '" alt="CI Logo">');

        $this->assertTrue($email->send());

        $this->assertStringStartsWith('image001.png@', $cid);
        $this->assertStringStartsWith(
            'image001.png@',
            $email->archive['attachments'][0]['cid'],
        );
        $this->assertMatchesRegularExpression(
            '/<img src="cid:image001.png@(.+?)" alt="CI Logo">/u',
            $email->archive['body'],
        );
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/9644
     *
     * @throws ReflectionException
     */
    public function testAppendAttachmentsWithoutCID(): void
    {
        $email = $this->createMockEmail();

        // Manually inject an attachment without 'cid'
        $this->setPrivateProperty($email, 'attachments', [
            [
                'multipart'   => 'mixed',
                'content'     => 'VGhpcyBpcyBhIHRlc3QgZmlsZSBjb250ZW50Lg==', // base64 for "This is a test file content."
                'filename'    => '',
                'type'        => 'application/pdf',
                'name'        => ['testfile.pdf'],
                'disposition' => 'attachment',
            ],
        ]);

        $body     = '';
        $boundary = 'test-boundary';

        // Use ReflectionMethod to call protected method with pass-by-reference
        $refMethod = new ReflectionMethod($email, 'appendAttachments');
        $refMethod->invokeArgs($email, [&$body, $boundary, 'mixed']);

        // Assertion: Should not include a Content-ID header
        $this->assertStringContainsString('Content-Type: application/pdf; name="testfile.pdf"', $body);
        $this->assertStringContainsString('Content-Disposition: attachment;', $body);
        $this->assertStringNotContainsString('Content-ID:', $body);
        $this->assertStringContainsString('--' . $boundary . '--', $body);
    }

    /**
     * @throws ReflectionException
     */
    public function testGetHostnameUsesServerName(): void
    {
        $email = $this->createMockEmail();

        $superglobals = service('superglobals');
        $superglobals->setServer('SERVER_NAME', 'example.test');

        $getHostname = self::getPrivateMethodInvoker($email, 'getHostname');

        $this->assertSame('example.test', $getHostname());
    }

    /**
     * @throws ReflectionException
     */
    public function testGetHostnameUsesServerAddr(): void
    {
        $email = $this->createMockEmail();

        $superglobals = service('superglobals');
        $superglobals->setServer('SERVER_NAME', '');
        $superglobals->setServer('SERVER_ADDR', '192.168.1.10');

        $getHostname = self::getPrivateMethodInvoker($email, 'getHostname');

        $this->assertSame('[192.168.1.10]', $getHostname());
    }

    /**
     * @throws ReflectionException
     */
    public function testGetHostnameFallsBackToGethostnameFunction(): void
    {
        $email = $this->createMockEmail();

        $superglobals = service('superglobals');
        $superglobals->setServer('SERVER_NAME', '');
        $superglobals->setServer('SERVER_ADDR', '');

        $getHostname = self::getPrivateMethodInvoker($email, 'getHostname');

        $this->assertSame(gethostname(), $getHostname());
    }
}
