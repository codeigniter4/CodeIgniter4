<?php namespace CodeIgniter\Mail;

use CodeIgniter\Mail\Handlers\MailHandler;

class MailHandlerTest extends \CIUnitTestCase
{
    protected function getMessage(array $options=[])
    {
        return new class($options) extends BaseMessage
        {
            public function build() {}
        };
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

        $handler->initialize();
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

        $handler->initialize();
        $headers = $handler->getHeaders();
        $this->assertEquals(implode(', ', $expected), $headers['From']);
    }
}
