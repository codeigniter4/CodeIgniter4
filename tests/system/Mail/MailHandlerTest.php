<?php namespace CodeIgniter\Mail;

use CodeIgniter\Mail\Handlers\MailHandler;

class MailHandlerTest extends \CIUnitTestCase
{
    public function __construct()
    {
        parent::__construct();

        $this->setMultiByteFlags();
    }


    protected function getMessage(array $options=[])
    {
        return new class($options) extends BaseMessage
        {
            public function build() {}
        };
    }

    /**
     * A simplified version of what happens in CodeIgniter.php
     * just to ensure we have a compatible set of constants
     * defined, that's needed for the Q Encoding.
     */
    protected function setMultiByteFlags()
    {
        if (defined('MB_ENABLED')) return;

        $charset = 'UTF-8';

        if (extension_loaded('mbstring'))
        {
            define('MB_ENABLED', TRUE);
        }
        else
        {
            define('MB_ENABLED', FALSE);
        }

        // There's an ICONV_IMPL constant, but the PHP manual says that using
        // iconv's predefined constants is "strongly discouraged".
        if (extension_loaded('iconv'))
        {
            define('ICONV_ENABLED', TRUE);
        }
        else
        {
            define('ICONV_ENABLED', FALSE);
        }
    }


    public function testCorrectlySetsSMTPAuthFlag()
    {
        $options = [
            'SMTPUser' => 'fuser',
            'SMTPPass' => 'barword'
        ];

        $handler = new MailHandler($options);

        $this->assertEquals('fuser', $this->getPrivateProperty($handler, 'SMTPUser'));
        $this->assertEquals('barword', $this->getPrivateProperty($handler, 'SMTPPass'));
        $this->assertTrue($this->getPrivateProperty($handler, 'SMTPAuth'));
    }

    public function testInitializeRetrievesAndFormatsToEmails()
    {
        $handler = new MailHandler();
        $handler->setMessage($this->getMessage([
            'to' => ['foo' => 'foo@example.com', 'bar' => 'bar@example.com']
        ]));

        $expected = [
            'foo <foo@example.com>',
            'bar <bar@example.com>'
        ];

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $this->assertEquals($expected, $this->getPrivateProperty($handler, 'recipients'));
    }

    public function testInitializeRetrievesAndFormatsFromEmailsArray()
    {
        $handler = new MailHandler();
        $handler->setMessage($this->getMessage([
            'from' => ['foo' => 'foo@example.com', 'bar' => 'bar@example.com']
        ]));

        $expected = [
            '"foo" <foo@example.com>',
            '"bar" <bar@example.com>'
        ];

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $headers = $handler->getHeaders();
        $this->assertEquals(implode(', ', $expected), $headers['From']);
    }

    public function testInitializeRetrievesAndFormatsReplyToEmailsArray()
    {
        $handler = new MailHandler();
        $handler->setMessage($this->getMessage([
            'replyTo' => ['foo' => 'foo@example.com', 'bar' => 'bar@example.com']
        ]));

        $expected = [
            '"foo" <foo@example.com>',
            '"bar" <bar@example.com>'
        ];

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $headers = $handler->getHeaders();
        $this->assertEquals(implode(', ', $expected), $headers['Reply-To']);
    }

    public function testInitializeRetrievesAndFormatsCCEmailsArray()
    {
        $handler = new MailHandler(['protocol' => 'smtp']);
        $handler->setMessage($this->getMessage([
            'cc' => ['foo' => 'foo@example.com', 'bar' => 'bar@example.com']
        ]));

        $expected = [
            '"foo" <foo@example.com>',
            '"bar" <bar@example.com>'
        ];

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $headers = $handler->getHeaders();
        $this->assertEquals(implode(', ', $expected), $headers['Cc']);
        $this->assertEquals($expected, $this->getPrivateProperty($handler, 'CC'));
    }

    public function testInitializeRetrievesAndFormatsBCCEmailsArray()
    {
        $handler = new MailHandler(['protocol' => 'smtp']);
        $handler->setMessage($this->getMessage([
            'bcc' => ['foo' => 'foo@example.com', 'bar' => 'bar@example.com']
        ]));

        $expected = [
            '"foo" <foo@example.com>',
            '"bar" <bar@example.com>'
        ];

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $headers = $handler->getHeaders();
        $this->assertEquals(implode(', ', $expected), $headers['Bcc']);
        $this->assertEquals($expected, $this->getPrivateProperty($handler, 'BCC'));
    }

    public function testInitializeRetrievesSubjectAndQEncodes()
    {
        $handler = new MailHandler(['protocol' => 'smtp']);
        $handler->setMessage($this->getMessage([
            'subject' => 'Once more into the breach'
        ]));

        $func = $this->getPrivateMethodInvoker($handler, 'initialize');
        $func();
        $headers = $handler->getHeaders();
        $this->assertEquals('=?UTF-8?Q?Once=20more=20into=20the=20b?==?UTF-8?Q?reach?=', $headers['Subject']);
    }

    public function testSetHeader()
    {
        $handler = new MailHandler();
        $handler->setHeader('Foo', 'bar');

        $headers = $handler->getHeaders();
        $this->assertEquals('bar', $headers['Foo']);
    }

    public function testSendFailsWithNoFrom()
    {
        $handler = new MailHandler([]);

        $result = $handler->send($this->getMessage());

        $this->assertTrue($result->hasErrors());
        $debug = $handler->getDebugger([]);
        $this->assertTrue(strpos($debug, lang('mail.noFrom')) !== false);
    }

}
