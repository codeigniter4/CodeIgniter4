<?php namespace CodeIgniter\Mail;

include __DIR__.'/SimpleMessage.php';

class MessageTest extends \CIUnitTestCase
{
    public function testMessageDataCanBeRead()
    {
        $data = ['foo' => 'bar'];

        $message = new SimpleMessage($data);

        $this->assertEquals($data, $message->data);
    }

    public function testCanSetDataManually()
    {
        $data = ['foo' => 'bar'];

        $message = new SimpleMessage();
        $message->data = $data;

        $this->assertEquals($data, $message->data);
    }


    public function testSetEmailsSingleEmail()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', null, 'to');

        $expected = [
            'foo@example.com'
        ];

        $this->assertEquals($expected, $message->to);
    }

    public function testSetEmailsSingleEmailWithName()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', 'John Foo', 'to');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $this->assertEquals($expected, $message->to);
    }

    public function testSetEmailsMultipleInString()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com, bar@example.com', null, 'to');

        $expected = [
            'foo@example.com',
            'bar@example.com'
        ];

        $this->assertEquals($expected, $message->to);
    }

    public function testSetEmailsMultipleInArrayNoNames()
    {
        $message = new SimpleMessage();
        $message->setEmails([
            'foo@example.com',
            'bar@example.com'
        ], null, 'to');

        $expected = [
            'foo@example.com',
            'bar@example.com'
        ];

        $this->assertEquals($expected, $message->to);
    }

    public function testSetEmailsMultipleInArrayWithNames()
    {
        $message = new SimpleMessage();
        $message->setEmails([
            'John Foo' => 'foo@example.com',
            'Jane Bar' => 'bar@example.com'
        ], null, 'to');

        $expected = [
            ['John Foo' => 'foo@example.com'],
            ['Jane Bar' => 'bar@example.com']
        ];

        $this->assertEquals($expected, $message->to);
    }

    public function testSetEmailsFrom()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', null, 'from');

        $expected = [
            'foo@example.com'
        ];

        $this->assertEquals($expected, $message->from);
    }

    public function testSetEmailsCC()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', null, 'cc');

        $expected = [
            'foo@example.com'
        ];

        $this->assertEquals($expected, $message->cc);
    }

    public function testSetEmailsBCC()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', null, 'bcc');

        $expected = [
            'foo@example.com'
        ];

        $this->assertEquals($expected, $message->bcc);
    }

    public function testSetEmailsReplyTo()
    {
        $message = new SimpleMessage();
        $message->setEmails('foo@example.com', null, 'reply');

        $expected = [
            'foo@example.com'
        ];

        $this->assertEquals($expected, $message->reply);
    }

}
