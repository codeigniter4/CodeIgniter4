<?php namespace CodeIgniter\Mail;

use Tests\Support\Mail\SimpleMessage;

class BaseMessageTest extends \CIUnitTestCase
{
    public function testStoresOptions()
    {
        $message = new SimpleMessage([
            'from' => ['John Doe' => 'john.doe@example.com'],
            'to'   => ['Jane Doe' => 'jane.doe@example.com'],
            'replyTo' => 'foo@example.com',
            'cc' => 'bar@example.com',
            'bcc' => 'baz@example.com',
            'subject' => 'Foo Dog',
        ]);

        $this->assertEquals([['Jane Doe' => 'jane.doe@example.com']], $message->getTo());
        $this->assertEquals([['John Doe' => 'john.doe@example.com']], $message->getFrom());
        $this->assertEquals(['foo@example.com'], $message->getReplyTo());
        $this->assertEquals(['bar@example.com'], $message->getCC());
        $this->assertEquals(['baz@example.com'], $message->getBCC());
        $this->assertEquals('Foo Dog', $message->getSubject());
    }

    public function testSingleSettersGetters()
    {
        $message = new SimpleMessage();

        $message->setFrom('john.doe@example.com', 'John Doe')
                ->setTo('jane.doe@example.com', 'Jane Doe')
                ->setReplyTo('foo@example.com')
                ->setCC('bar@example.com')
                ->setBCC('baz@example.com')
                ->setSubject('Foo Dog');

        $this->assertEquals([['Jane Doe' => 'jane.doe@example.com']], $message->getTo());
        $this->assertEquals([['John Doe' => 'john.doe@example.com']], $message->getFrom());
        $this->assertEquals(['foo@example.com'], $message->getReplyTo());
        $this->assertEquals(['bar@example.com'], $message->getCC());
        $this->assertEquals(['baz@example.com'], $message->getBCC());
        $this->assertEquals('Foo Dog', $message->getSubject());
    }

    public function testManySettersGetters()
    {
        $message = new SimpleMessage();

        $message->setFromMany(['John Doe' => 'john.doe@example.com', 'jane@example.com'])
                ->setToMany(['John Doe' => 'john.doe@example.com', 'jane@example.com'])
                ->setReplyToMany(['John Doe' => 'john.doe@example.com', 'jane@example.com'])
                ->setCCMany(['John Doe' => 'john.doe@example.com', 'jane@example.com'])
                ->setBCCMany(['John Doe' => 'john.doe@example.com', 'jane@example.com']);

        $this->assertEquals([['John Doe' => 'john.doe@example.com'], 'jane@example.com'], $message->getTo());
        $this->assertEquals([['John Doe' => 'john.doe@example.com'], 'jane@example.com'], $message->getFrom());
        $this->assertEquals([['John Doe' => 'john.doe@example.com'], 'jane@example.com'], $message->getReplyTo());
        $this->assertEquals([['John Doe' => 'john.doe@example.com'], 'jane@example.com'], $message->getCC());
        $this->assertEquals([['John Doe' => 'john.doe@example.com'], 'jane@example.com'], $message->getBCC());
    }

    public function testSetRecipientsThrowsOnInvalidEmails()
    {
        $this->setExpectedException('CodeIgniter\Mail\InvalidEmailAddress');

        $message = new SimpleMessage();

        $message->setTo('johndoeexample');
    }

    public function testSetHTMLMessageSuccess()
    {
        $message = new SimpleMessage();

        $message->setHTMLMessage('<h1>Welcome</h1>');

        $this->assertEquals('<h1>Welcome</h1>', $message->getHTMLMessage());
    }

    public function testSetTextMessageSuccess()
    {
        $message = new SimpleMessage();

        $message->setTextMessage('<h1>Welcome</h1>');

        $this->assertEquals('Welcome', $message->getTextMessage());
    }

    public function testSetDataSuccess()
    {
        $message = new SimpleMessage();

        $message->setData(['foo' => 'bar']);
        $message->setData(['bar' => 'baz']);

        $this->assertEquals(['foo' => 'bar', 'bar' => 'baz'], $message->getData());
    }

    public function testSetDataOverwritesItself()
    {
        $message = new SimpleMessage();

        $message->setData(['foo' => 'bar']);
        $message->setData(['foo' => 'baz']);

        $this->assertEquals(['foo' => 'baz'], $message->getData());
    }

    public function testSetDataOverwritesConstructor()
    {
        $message = new SimpleMessage([
            'foo' => 'bar'
        ]);

        $message->setData(['foo' => 'baz']);

        $this->assertEquals(['foo' => 'baz'], $message->getData());
    }
}
