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

use Closure;
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

    /**
     * @return iterable<string, list<bool>>
     */
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

    public function testBuildMessagePlain(): void
    {
        $email = new Email();
        $email->setMessage('This is a simple plain text message.');
        $email->setMailType('text');

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $headerStr = $this->getPrivateProperty($email, 'headerStr');
        $finalBody = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('This is a simple plain text message.', (string) $finalBody);
        $this->assertStringContainsString('Content-Type: text/plain', (string) $headerStr);
    }

    public function testBuildMessageHtmlNoMultipart(): void
    {
        $email = new Email();
        $email->setMessage('<p>This is a HTML message.</p>');
        $email->setMailType('html');
        $this->setPrivateProperty($email, 'sendMultipart', false);

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $headerStr = $this->getPrivateProperty($email, 'headerStr');
        $finalBody = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('<p>This is a HTML message.</p>', (string) $finalBody);
        $this->assertStringContainsString('Content-Type: text/html', (string) $headerStr);
        $this->assertStringNotContainsString('multipart/alternative', (string) $headerStr);
    }

    public function testBuildMessageHtmlMultipart(): void
    {
        $email = new Email();
        $email->setMessage('<p>This is a HTML message.</p>');
        $email->setMailType('html');
        $this->setPrivateProperty($email, 'sendMultipart', true);
        $email->setAltMessage('This is alternative text.');

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $headerStr = $this->getPrivateProperty($email, 'headerStr');
        $finalBody = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('<p>This is a HTML message.</p>', (string) $finalBody);
        $this->assertStringContainsString('Content-Type: multipart/alternative', (string) $headerStr);
        $this->assertStringContainsString('This is alternative text.', (string) $finalBody);
    }

    public function testBuildMessagePlainWithAttachments(): void
    {
        $email = new Email();
        $email->setMessage('Plain text with attachments.');
        $email->attach(SUPPORTPATH . 'Images/ci-logo.png');

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $headerStr = $this->getPrivateProperty($email, 'headerStr');
        $finalBody = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('Content-Type: multipart/mixed', (string) $headerStr);
        $this->assertStringContainsString('Content-Type: image/png', (string) $finalBody);
        $this->assertStringContainsString('Content-Disposition: attachment', (string) $finalBody);
    }

    public function testWordWrap(): void
    {
        $email = new Email();
        $email->setNewline();

        $text    = 'This is a very long line of text that should be wrapped to a smaller character limit.';
        $wrapped = $email->wordWrap($text, 20);

        // Verify that long text gets wrapped at roughly 20 chars
        $lines = explode("\n", trim($wrapped));

        foreach ($lines as $line) {
            $this->assertLessThanOrEqual(20, strlen($line));
        }

        // Verify unwrap behavior
        $unwrapText    = 'This is {unwrap}a very long word or link that should not be wrapped under any circumstances{/unwrap} even if it exceeds the limit.';
        $wrappedUnwrap = $email->wordWrap($unwrapText, 20);
        $this->assertStringContainsString('a very long word or link that should not be wrapped under any circumstances', $wrappedUnwrap);
    }

    public function testCleanEmail(): void
    {
        $email = new Email();

        // Test with string input
        $this->assertSame('john@example.com', $email->cleanEmail('John Doe <john@example.com>'));
        $this->assertSame('simple@example.com', $email->cleanEmail('simple@example.com'));

        // Test with array input
        $emails = [
            'John Doe <john@example.com>',
            'simple@example.com',
            '<jane@example.com>',
        ];
        $expected = [
            'john@example.com',
            'simple@example.com',
            'jane@example.com',
        ];
        $this->assertSame($expected, $email->cleanEmail($emails));
    }

    public function testValidateEmail(): void
    {
        $email = new Email();

        // Must be array
        $this->assertFalse($email->validateEmail('string@example.com'));

        // Valid array
        $this->assertTrue($email->validateEmail(['test@example.com', 'another@example.com']));

        // Invalid array
        $this->assertFalse($email->validateEmail(['test@example.com', 'invalid-email']));
    }

    public function testIsValidEmail(): void
    {
        $email = new Email();

        $this->assertTrue($email->isValidEmail('test@example.com'));
        $this->assertFalse($email->isValidEmail('invalid-email'));
        $this->assertFalse($email->isValidEmail('test@example'));

        // IDN domain (internationalized)
        if (function_exists('idn_to_ascii') && defined('INTL_IDNA_VARIANT_UTS46')) {
            $this->assertTrue($email->isValidEmail('user@müller.de'));
        }
    }

    public function testClear(): void
    {
        $email = new Email();
        $email->setTo('test@example.com');
        $email->setSubject('My Subject');
        $email->setMessage('Hello');
        $email->attach(SUPPORTPATH . 'Images/ci-logo.png');

        // Verify state before clear
        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertSame('=?UTF-8?Q?My=20Subject?=', $headers['Subject']);
        $this->assertNotEmpty($this->getPrivateProperty($email, 'attachments'));

        // Clear without clearing attachments
        $email->clear(false);
        $headersAfter = $this->getPrivateProperty($email, 'headers');
        $this->assertCount(1, $headersAfter);
        $this->assertArrayHasKey('Date', $headersAfter);
        $this->assertNotEmpty($this->getPrivateProperty($email, 'attachments'));

        // Clear with clearing attachments
        $email->clear(true);
        $this->assertEmpty($this->getPrivateProperty($email, 'attachments'));
    }

    public function testSetters(): void
    {
        $email = new Email();

        $email->setMailType('html');
        $this->assertSame('html', $this->getPrivateProperty($email, 'mailType'));

        $email->setWordWrap(false);
        $this->assertFalse($this->getPrivateProperty($email, 'wordWrap'));

        $email->setProtocol('smtp');
        $this->assertSame('smtp', $this->getPrivateProperty($email, 'protocol'));

        $email->setPriority(1);
        $this->assertSame(1, $this->getPrivateProperty($email, 'priority'));

        $email->setNewline("\r\n");
        $this->assertSame("\r\n", $this->getPrivateProperty($email, 'newline'));

        $email->setCRLF("\r\n");
        $this->assertSame("\r\n", $this->getPrivateProperty($email, 'CRLF'));
    }

    public function testSetFrom(): void
    {
        $email = new Email();
        $email->setFrom('john@example.com', 'John Doe');

        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertSame('"John Doe" <john@example.com>', $headers['From']);
        $this->assertSame('<john@example.com>', $headers['Return-Path']);

        // Testing Q Encoding
        $email->setFrom('john@example.com', 'Jöhn Døe');
        $headersQ = $this->getPrivateProperty($email, 'headers');
        $this->assertStringContainsString('=?UTF-8?Q?', (string) $headersQ['From']);
    }

    public function testSetReplyTo(): void
    {
        $email = new Email();
        $email->setReplyTo('support@example.com', 'Support Team');

        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertSame('"Support Team" <support@example.com>', $headers['Reply-To']);
        $this->assertTrue($this->getPrivateProperty($email, 'replyToFlag'));
    }

    public function testSetToCCBCC(): void
    {
        $email = new Email();
        $email->setProtocol('smtp');

        $email->setTo('one@example.com, two@example.com');
        $this->assertSame(['one@example.com', 'two@example.com'], $this->getPrivateProperty($email, 'recipients'));

        $email->setCC('cc@example.com');
        $this->assertSame(['cc@example.com'], $this->getPrivateProperty($email, 'CCArray'));

        $email->setBCC('bcc@example.com');
        $this->assertSame(['bcc@example.com'], $this->getPrivateProperty($email, 'BCCArray'));
    }

    public function testAttach(): void
    {
        $email = new Email();
        $email->attach(SUPPORTPATH . 'Images/ci-logo.png', 'attachment', 'new-name.png');

        $attachments = $this->getPrivateProperty($email, 'attachments');
        $this->assertCount(1, $attachments);
        $this->assertSame('new-name.png', $attachments[0]['name'][1]);
        $this->assertSame('attachment', $attachments[0]['disposition']);
        $this->assertSame('image/png', $attachments[0]['type']);
    }

    public function testPrintDebugger(): void
    {
        $email = new Email();
        $this->setPrivateProperty($email, 'headerStr', 'To: recipient@example.com');
        $this->setPrivateProperty($email, 'subject', 'Debug Test');
        $this->setPrivateProperty($email, 'finalBody', 'Testing debugger');
        $this->setPrivateProperty($email, 'debugMessage', ['SMTP success message<br>']);

        $debug = $email->printDebugger();
        $this->assertStringContainsString('SMTP success message', $debug);
        $this->assertStringContainsString('Debug Test', $debug);
        $this->assertStringContainsString('recipient@example.com', $debug);
    }

    public function testSettersFallback(): void
    {
        $email = new Email();

        // Invalid newline falls back to "\n"
        $email->setNewline('invalid');
        $this->assertSame("\n", $this->getPrivateProperty($email, 'newline'));

        // Invalid CRLF falls back to "\n"
        $email->setCRLF('invalid');
        $this->assertSame("\n", $this->getPrivateProperty($email, 'CRLF'));
    }

    public function testGetMessageID(): void
    {
        $email = new Email();
        $email->setFrom('john@example.com');

        $getMessageID = self::getPrivateMethodInvoker($email, 'getMessageID');
        $messageID    = $getMessageID();

        $this->assertStringStartsWith('<', $messageID);
        $this->assertStringEndsWith('>', $messageID);
        $this->assertStringContainsString('@example.com', $messageID);
    }

    public function testValidateEmailForShell(): void
    {
        $email = new Email();

        $validateForShell = Closure::bind(fn (&$email) => $this->validateEmailForShell($email), $email, Email::class);

        $address = 'test@example.com';
        $this->assertTrue($validateForShell($address));

        $invalidAddress = 'invalid-email-address';
        $this->assertFalse($validateForShell($invalidAddress));

        $shellInjection = 'test@example.com; rm -rf /';
        $this->assertFalse($validateForShell($shellInjection));
    }

    public function testSetPriorityFallback(): void
    {
        $email = new Email();

        // Priority 6 is invalid, falls back to 3
        $email->setPriority(6);
        $this->assertSame(3, $this->getPrivateProperty($email, 'priority'));

        // Priority 0 is invalid, falls back to 3
        $email->setPriority(0);
        $this->assertSame(3, $this->getPrivateProperty($email, 'priority'));
    }

    public function testSMTPAuthenticateValidation(): void
    {
        $email = new Email();

        $smtpAuthenticate = self::getPrivateMethodInvoker($email, 'SMTPAuthenticate');

        // SMTPAuth false returns true immediately
        $this->setPrivateProperty($email, 'SMTPAuth', false);
        $this->assertTrue($smtpAuthenticate());

        // SMTPAuth true but empty credentials returns false
        $this->setPrivateProperty($email, 'SMTPAuth', true);
        $this->setPrivateProperty($email, 'SMTPUser', '');
        $this->setPrivateProperty($email, 'SMTPPass', '');
        $this->assertFalse($smtpAuthenticate());

        // Invalid SMTP auth method returns false
        $this->setPrivateProperty($email, 'SMTPUser', 'user');
        $this->setPrivateProperty($email, 'SMTPPass', 'pass');
        $this->setPrivateProperty($email, 'SMTPAuthMethod', 'oauth');
        $this->assertFalse($smtpAuthenticate());
    }

    public function testAttachMissingFile(): void
    {
        $email = new Email();
        $this->assertFalse($email->attach('/path/to/nonexistent/file.txt'));
    }

    public function testSetAttachmentCID(): void
    {
        $email = new Email();

        // Attach from buffer
        $email->attach('file_content', 'inline', 'photo.jpg', 'image/jpeg');

        $cid = $email->setAttachmentCID('photo.jpg');
        $this->assertIsString($cid);
        $this->assertStringContainsString('photo.jpg@', $cid);

        // Non-existent file/buffer name
        $this->assertFalse($email->setAttachmentCID('nonexistent.jpg'));
    }

    public function testSmtpConnectAlreadyConnected(): void
    {
        $email = new Email();
        $this->setPrivateProperty($email, 'SMTPConnect', fopen('php://memory', 'r+b'));

        $smtpConnect = self::getPrivateMethodInvoker($email, 'SMTPConnect');
        $this->assertTrue($smtpConnect());
    }

    public function testBuildMessageHtmlWithInlineAttachments(): void
    {
        $email = new Email();
        $email->setMailType('html');
        $email->setFrom('sender@example.com');
        $email->setTo('recipient@example.com');
        $email->setSubject('Inline Test');
        $email->setMessage('<p>Test HTML</p>');
        $email->attach('image_data', 'inline', 'image.jpg', 'image/jpeg');
        $email->setAttachmentCID('image.jpg');

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $body = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('Content-Disposition: inline', (string) $body);
        $this->assertStringContainsString('Content-ID:', (string) $body);
    }

    public function testMimeTypesUnknown(): void
    {
        $email     = new Email();
        $mimeTypes = self::getPrivateMethodInvoker($email, 'mimeTypes');
        $this->assertSame('application/x-unknown-content-type', $mimeTypes('invalid_ext'));
    }

    public function testAttachRealFileDetectMime(): void
    {
        $email = new Email();
        $this->assertNotFalse($email->attach(__FILE__));

        $attachments = $this->getPrivateProperty($email, 'attachments');
        $this->assertCount(1, $attachments);
        $this->assertSame('application/x-php', $attachments[0]['type']);
    }

    public function testValidateEmailIdn(): void
    {
        $email         = new Email();
        $validateEmail = self::getPrivateMethodInvoker($email, 'validateEmail');
        $address       = ['test@przykłady.pl'];
        $this->assertTrue($validateEmail($address));
    }

    public function testSetSubjectPreventsHeaderInjection(): void
    {
        $email = new Email();
        $email->setSubject("Test Subject\r\nBcc: spy@example.com");

        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertStringNotContainsString('Bcc: spy@example.com', (string) $headers['Subject']);
    }

    public function testSetSubjectExtremelyLongUtf8(): void
    {
        $email   = new Email();
        $subject = str_repeat('Zażółć gęślą jaźń ', 10);
        $email->setSubject($subject);

        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertStringStartsWith('=?UTF-8?Q?', $headers['Subject']);
    }

    public function testAttachWithUtf8Filename(): void
    {
        $email = new Email();
        $email->attach(SUPPORTPATH . 'Images/ci-logo.png', 'attachment', 'żółć-logo.png');

        $attachments = $this->getPrivateProperty($email, 'attachments');
        $this->assertSame('żółć-logo.png', $attachments[0]['name'][1]);

        $buildMessage = self::getPrivateMethodInvoker($email, 'buildMessage');
        $buildMessage();

        $finalBody = $this->getPrivateProperty($email, 'finalBody');
        $this->assertStringContainsString('name="żółć-logo.png"', (string) $finalBody);
    }

    public function testGetProtocolFallback(): void
    {
        $email = new Email();
        $email->setProtocol('invalid');
        $getProtocol = self::getPrivateMethodInvoker($email, 'getProtocol');
        $this->assertSame('mail', $getProtocol());
    }

    public function testSetBccBatchMode(): void
    {
        $email = new Email();
        $email->setBCC('bcc@example.com', '10');
        $this->assertTrue($this->getPrivateProperty($email, 'BCCBatchMode'));
        $this->assertSame('10', $this->getPrivateProperty($email, 'BCCBatchSize'));
    }

    public function testSettersWithArray(): void
    {
        $email = new Email();
        $email->setTo(['one@example.com', 'two@example.com']);
        $this->assertSame(['one@example.com', 'two@example.com'], $this->getPrivateProperty($email, 'recipients'));
    }

    public function testSetFromAndReplyToWithValidation(): void
    {
        $config           = config('Email');
        $config->validate = true;
        $email            = new Email($config);

        $email->setFrom('john@example.com', 'Jöhn Døe');
        $headers = $this->getPrivateProperty($email, 'headers');
        $this->assertStringContainsString('=?UTF-8?Q?', (string) $headers['From']);

        $email->setReplyTo('support@example.com', 'Jöhn Døe');
        $this->assertTrue($this->getPrivateProperty($email, 'replyToFlag'));
    }
}
