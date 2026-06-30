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

    #[DataProvider('providePrepQuotedPrintableWithLfCrlf')]
    public function testPrepQuotedPrintableWithLfCrlf(string $input, string $expected): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        $this->assertSame($expected, $prepQP($input));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function providePrepQuotedPrintableWithLfCrlf(): iterable
    {
        return [
            'empty string'               => ['', ''],
            'safe ascii only'            => ['hello world', 'hello world'],
            'safe chars only'            => ['abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789(),-./:?', 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789(),-./:?'],
            'unsafe char encoded'        => ["a\x01b", 'a=01b'],
            'trailing space encoded'     => ["hello \nworld", "hello=20\nworld"],
            'trailing tab encoded'       => ["hello\t\nworld", "hello=09\nworld"],
            'equals sign encoded as =3D' => ['a=b', 'a=3Db'],
            'multiple spaces reduced'    => ['a  b', 'a b'],
            'null bytes removed'         => ["a\x00b", 'ab'],
            'unwrap tags removed'        => ['{unwrap}secret{/unwrap}', 'secret'],
            'single line'                => ['test', 'test'],
            'two lines'                  => ["line1\nline2", "line1\nline2"],
            'three lines trailing empty' => ["line1\nline2\n", "line1\nline2\n"],
        ];
    }

    public function testPrepQuotedPrintableWithCrlfNative(): void
    {
        $email       = new Email();
        $email->CRLF = "\r\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        $result = $prepQP('test');

        $this->assertSame(quoted_printable_encode('test'), $result);
    }

    public function testPrepQuotedPrintableSoftLineBreak(): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        // 76 'a' chars fit in one line; add 2 more 'b' chars and they soft-wrap
        // After reduction: no trailing spaces, just safe chars
        $input  = str_repeat('a', 76) . 'bb';
        $result = $prepQP($input);

        $this->assertStringContainsString("=\n", $result, 'Soft line break must be present');
        $this->assertStringNotContainsString("\r\n", $result, 'Custom CRLF must not contain \\r');
    }

    public function testPrepQuotedPrintableSoftBreakAfterEncodedChar(): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        // 74 safe chars + 1 encoded (=3D = 3 bytes) = 77 → must break before encoded
        $input  = str_repeat('a', 74) . '=';
        $result = $prepQP($input);

        $this->assertSame(str_repeat('a', 74) . "=\n=3D", $result);
    }

    public function testPrepQuotedPrintableHardLineBreakNoInternalSpaceReduction(): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        // Spaces not at end of line must be left as-is
        $this->assertSame('a b', $prepQP('a   b'));
    }

    public function testPrepQuotedPrintableMixedContent(): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        $input  = "Hello, World!\nline ends with tab\t\n=special chars: \x01\x02";
        $result = $prepQP($input);

        $this->assertStringContainsString('Hello, World=21', $result);
        $this->assertStringContainsString('=09', $result);
        $this->assertStringContainsString('=3D', $result);
        $this->assertStringContainsString('=01', $result);
        $this->assertStringContainsString('=02', $result);
    }

    public function testPrepQuotedPrintableUnwrapRemovesTagsOnly(): void
    {
        $email       = new Email();
        $email->CRLF = "\n";
        $prepQP      = self::getPrivateMethodInvoker($email, 'prepQuotedPrintable');

        $this->assertSame('keep =7Bbraces=7D', $prepQP('keep {braces}'));
        $this->assertSame('keep (parentheses)', $prepQP('keep (parentheses)'));
    }
}
