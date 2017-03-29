<?php namespace CodeIgniter\Mail;

class BaseHandlerTest extends \CIUnitTestCase
{
    protected function getHandler(array $options=[])
    {
        return new class($options) extends BaseHandler
        {
            public function send(MessageInterface $message, bool $clear_after = true)
            {}
        };
    }

    protected function getMessage(array $options=[])
    {
        return new class($options) extends BaseMessage
        {
            public function build() {}
        };
    }

    public function testStoresOptionsInConstructor()
    {
        $options = [
            'useragent' => '007',
            'mailtype'  => 'html',
            'charset'   => 'utf-16',
            'validate'  => false,
            'SMTPAuth' => true,
            'ReplyToFlag' => true,
            'foo' => 'bar'
        ];

        $handler = $this->getHandler($options);

        $this->assertEquals('007', $handler->useragent);
        $this->assertEquals('html', $handler->mailtype);
        $this->assertEquals('UTF-16', $handler->charset);
        $this->assertEquals(false, $handler->validate);
        $this->assertEquals(true, $this->getPrivateProperty($handler, 'SMTPAuth'));
        $this->assertEquals(true, $this->getPrivateProperty($handler, 'ReplyToFlag'));
        $this->assertEquals($options, $this->getPrivateProperty($handler, 'config'));
    }

    public function testSetMessage()
    {
        $handler = $this->getHandler();

        $message = $this->getMessage();

        $handler->setMessage($message);
        $this->assertEquals($message, $handler->getMessage());
    }

    public function testSetHeader()
    {
        $handler = $this->getHandler();

        $handler->setHeader('foo', 'bar');

        $headers = $handler->getHeaders();

        $this->assertTrue($headers['foo'] == 'bar');
    }

}
