<?php namespace CodeIgniter\Mail;

include __DIR__.'/SimpleMessage.php';

use CodeIgniter\Services;
use Config\Mail;

class MessageTest extends \CIUnitTestCase
{
    /**
     * @var Mailer
     */
    protected $mailer;

    public function setUp()
    {
        $this->mailer = new Mailer(
            new Mail(),
            new SimpleMessage()
        );
    }


    public function testCanSetToNoName()
    {
        $this->mailer->setTo('foo@example.com');

        $expected = [
            'foo@example.com'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->to);
    }

    public function testCanSetToWitName()
    {
        $this->mailer->setTo('foo@example.com', 'John Foo');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->to);
    }

    public function testCanSetFromNoName()
    {
        $this->mailer->setFrom('foo@example.com');

        $expected = [
            'foo@example.com'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->from);
    }

    public function testCanSetFromWitName()
    {
        $this->mailer->setFrom('foo@example.com', 'John Foo');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->from);
    }

    public function testCanSetReplyToNoName()
    {
        $this->mailer->setReplyTo('foo@example.com');

        $expected = [
            'foo@example.com'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->reply);
    }

    public function testCanSetReplyToWitName()
    {
        $this->mailer->setReplyTo('foo@example.com', 'John Foo');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->reply);
    }

    public function testCanSetCCNoName()
    {
        $this->mailer->setCC('foo@example.com');

        $expected = [
            'foo@example.com'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->cc);
    }

    public function testCanSetCCWitName()
    {
        $this->mailer->setCC('foo@example.com', 'John Foo');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->cc);
    }

    public function testCanSetBCCNoName()
    {
        $this->mailer->setBCC('foo@example.com');

        $expected = [
            'foo@example.com'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->bcc);
    }

    public function testCanSetBCCWitName()
    {
        $this->mailer->setBCC('foo@example.com', 'John Foo');

        $expected = [
            ['John Foo' => 'foo@example.com']
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->bcc);
    }

    public function testSetSubject()
    {
        $this->mailer->setSubject('The perils of the working man');

        $expected = 'The perils of the working man';

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->subject);
    }

    public function testSetSubjectWithReplacement()
    {
        $this->mailer->setSubject('The perils of the working {pronoun}', ['pronoun' => 'man']);

        $expected = 'The perils of the working man';

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->subject);
    }

    public function testSetData()
    {
        $this->mailer->setData(['foo' => 'bar']);

        $expected = [
            'foo' => 'bar'
        ];

        $message = $this->mailer->getMessage();

        $this->assertEquals($expected, $message->data);
    }

    public function testSetHeader()
    {
        $this->mailer->setHeader('foo', ['bar', 'baz']);

        $expected = 'bar, baz';

        $message = $this->mailer->getMessage();

        $this->assertTrue($message->hasHeader('Foo'));
        $this->assertEquals($expected, $message->getHeaderLine('foo'));
    }
}
