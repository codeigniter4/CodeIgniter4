<?php namespace CodeIgniter\Mail;

use Tests\Support\Mail\SimpleMessage;

class BaseMessageTest extends \CIUnitTestCase
{
    public function testStoresOptions()
    {
        $message = new SimpleMessage([
            'from' => 'John Doe <john.doe@example.com>',
            'to'   => 'Jane Doe <jane.doe@example.com>',
            'replyTo' => 'foo@example.com',
            'cc' => 'bar@example.com',
            'bcc' => 'baz@example.com',
            'subject' => 'Foo Dog',
        ]);

        $this->assertEquals('Jane Doe <jane.doe@example.com>', $message->to);
        $this->assertEquals('John Doe <john.doe@example.com>', $message->from);
        $this->assertEquals('foo@example.com', $message->replyTo);
        $this->assertEquals('bar@example.com', $message->cc);
        $this->assertEquals('baz@example.com', $message->bcc);
        $this->assertEquals('Foo Dog', $message->subject);
    }

    public function testCanSetSingleOptions()
    {
        $message = new SimpleMessage();

        $this->assertNull($message->to);

        $message->to = 'foo@example.com';
        $this->assertEquals('foo@example.com', $message->to);
    }

    public function testMiscGoesToData()
    {
        $message = new SimpleMessage();

        $message->foo = 'bar';

        $this->assertEquals(['foo' => 'bar'], $message->data);
    }

    public function testCanGrabFromData()
    {
        $message = new SimpleMessage();

        $message->foo = 'bar';

        $this->assertEquals('bar', $message->foo);
    }


}
