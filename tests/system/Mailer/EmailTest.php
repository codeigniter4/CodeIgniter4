<?php

namespace CodeIgniter\Mailer;

use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;

class EmailTest extends CIUnitTestCase
{
    public function tearDown(): void
	{
		// Restore file permissions after unreadable attachment test
		$thefile = SUPPORTPATH . 'Mailer/ci-logo-not-readable.png';
		chmod($thefile, 0664);
	}

    //--------------------------------------------------------------------
	// Test constructor & configs

    public function testEmptyConstructor()
    {
        $email = new Email();

        // Just to make sure it didn't crash
        $this->assertInstanceOf(Email::class, $email);

        $this->assertNull($email->getBody());
        $this->assertNull($email->getSubject());
        $this->assertNull($email->getFrom());
        $this->assertNull($email->getTo());
        $this->assertNull($email->getCc());
        $this->assertNull($email->getBcc());
        $this->assertNull($email->getReplyTo());
        $this->assertNull($email->getReturnPath());
        $this->assertNull($email->getPriority());
        $this->assertNull($email->getDate());
    }

	public function testConstructorUsesData()
	{
		$email   = new Email([
            'body' => 'Email body',
            'subject' => 'The Rebels need you!',
            'from' => 'leia@alderaan.org',
            'to' => 'lukeskywalker@tattoine.org',
            'cc' => '"Obi Wan Kenobi" <obiwan@tattoine.org>',
            'bcc' => ['one@example.com', 'two@example.com'],
            'replyTo' => 'no-reply@alderaan.org',
            'returnPath' => 'bounces@alderaan.org',
            'priority' => 2,
            'date' => Time::now(),
            'truth' => 'out there',
		]);

		$this->assertSame('Email body', (string) $email->getBody());
		$this->assertSame('The Rebels need you!', (string) $email->getSubject());
		$this->assertSame('leia@alderaan.org', (string) $email->getFrom());
		$this->assertSame('lukeskywalker@tattoine.org', (string)$email->getTo()[0]);
		$this->assertSame('"Obi Wan Kenobi" <obiwan@tattoine.org>', (string)$email->getCc()[0]);
		$this->assertSame('one@example.com', (string)$email->getBcc()[0]);
		$this->assertSame('no-reply@alderaan.org', (string)$email->getReplyTo());
		$this->assertSame('bounces@alderaan.org', (string)$email->getReturnPath());
		$this->assertSame(2, $email->getPriority());
		$this->assertInstanceOf(Time::class, $email->getDate());
	}

    public function testSetters()
    {
        $email = new Email();

        $email->body('Email body');
        $email->subject('The Rebels need you!');
        $email->from('leia@alderaan.org');
        $email->to('lukeskywalker@tattoine.org');
        $email->cc('"Obi Wan Kenobi" <obiwan@tattoine.org>');
        $email->bcc('one@example.com', 'two@example.com');
        $email->replyTo('no-reply@alderaan.org');
        $email->returnPath('bounces@alderaan.org');
        $email->priority(2);
        $email->date(Time::now());

        $this->assertSame('Email body', (string) $email->getBody());
		$this->assertSame('The Rebels need you!', (string) $email->getSubject());
		$this->assertSame('leia@alderaan.org', (string) $email->getFrom());
		$this->assertSame('lukeskywalker@tattoine.org', (string)$email->getTo()[0]);
		$this->assertSame('"Obi Wan Kenobi" <obiwan@tattoine.org>', (string)$email->getCc()[0]);
		$this->assertSame('one@example.com', (string)$email->getBcc()[0]);
		$this->assertSame('no-reply@alderaan.org', (string)$email->getReplyTo());
		$this->assertSame('bounces@alderaan.org', (string)$email->getReturnPath());
		$this->assertSame(2, $email->getPriority());
		$this->assertInstanceOf(Time::class, $email->getDate());
    }

    public function testMessageId()
    {
        $email = new Email([
            'returnPath' => 'no-reply@alderaan.org'
        ]);

        $this->assertMatchesRegularExpression("/^<(.*)@alderaan.org>/", $email->getMessageId());
    }

    public function testMessageIdReturnsNull()
    {
        $email = new Email();

        $this->assertNull($email->getMessageId());
    }

    public function testGetBoundaryCreatesBoundary()
    {
        $email = new Email();

        $this->assertStringContainsString('foo_', $email->getBoundary('foo'));
    }

    public function testCI3SetterMagic()
    {
        $email = new Email();

        $email->setBody('Email body');
        $email->setSubject('The Rebels need you!');
        $email->setFrom('leia@alderaan.org');
        $email->setTo('lukeskywalker@tattoine.org');
        $email->setCc('"Obi Wan Kenobi" <obiwan@tattoine.org>');
        $email->setBcc('one@example.com', 'two@example.com');
        $email->setReplyTo('no-reply@alderaan.org');
        $email->setReturnPath('bounces@alderaan.org');
        $email->setPriority(2);
        $email->setDate(Time::now());

        $this->assertSame('Email body', (string) $email->getBody());
		$this->assertSame('The Rebels need you!', (string) $email->getSubject());
		$this->assertSame('leia@alderaan.org', (string) $email->getFrom());
		$this->assertSame('lukeskywalker@tattoine.org', (string)$email->getTo()[0]);
		$this->assertSame('"Obi Wan Kenobi" <obiwan@tattoine.org>', (string)$email->getCc()[0]);
		$this->assertSame('one@example.com', (string)$email->getBcc()[0]);
		$this->assertSame('no-reply@alderaan.org', (string)$email->getReplyTo());
		$this->assertSame('bounces@alderaan.org', (string)$email->getReturnPath());
		$this->assertSame(2, $email->getPriority());
		$this->assertInstanceOf(Time::class, $email->getDate());
    }

    public function testSetMessage()
    {
        $email = new Email();

        $email->setMessage('Email body');

        $this->assertSame('Email body', $email->getBody());
    }

    //--------------------------------------------------------------------
	// Test setting the "from" property
    // NOTE: Return-Path should NOT be automatically set,
    //      see: https://www.postmastery.com/about-the-return-path-header/

	public function testSetFromMailerOnly()
	{
		$email = new Email();
		$email->from('leia@alderaan.org');
		$this->assertEquals('leia@alderaan.org', (string)$email->getFrom());
		$this->assertNull($email->header('Return-Path'));
	}

	public function testSetFromMailerAndName()
	{
		$email = new Email();
		$email->from('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getFrom());
	}

    public function testSetFromMailerAndNameString()
	{
		$email = new Email();
		$email->from('"Princess Leia" <leia@alderaan.org>');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', (string)$email->getFrom());
	}

    //--------------------------------------------------------------------
	// Test setting the "replyTo" property

	public function testSetReplyToMailerOnly()
	{
		$mailer = new Email();
		$mailer->setReplyTo('leia@alderaan.org');
		$this->assertEquals('leia@alderaan.org', (string)$mailer->getReplyTo());
	}

	public function testSetReplyToMailerAndName()
	{
		$mailer = new Email();
		$mailer->setReplyTo('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', (string)$mailer->getReplyTo());
	}

    //--------------------------------------------------------------------
	// Test setting the "to" property

	public function testSetToBasic()
	{
		$mailer = new Email();
		$mailer->setTo('Luke <luke@tatooine.org>');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getTo());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
	}

    public function testSetToMultiple()
	{
		$mailer = new Email();
		$mailer->setTo('Luke <luke@tatooine.org>', 'padme@naboo.org');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getTo());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

	public function testSetToArray()
	{
		$mailer = new Email();
		$mailer->setTo(['Luke <luke@tatooine.org>', 'padme@naboo.org']);

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getTo());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

    //--------------------------------------------------------------------
	// Test setting the "cc" property (copied recipients)

	public function testSetCCBasic()
	{
		$mailer = new Email();
        $mailer->cc('Luke <luke@tatooine.org>');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getCc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
	}

    public function testSetCCMultiple()
	{
		$mailer = new Email();
		$mailer->cc('Luke <luke@tatooine.org>', 'padme@naboo.org');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getCc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

	public function testSetCCArray()
	{
		$mailer = new Email();
		$mailer->cc(['Luke <luke@tatooine.org>', 'padme@naboo.org']);

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getCc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

    //--------------------------------------------------------------------
	// Test setting the "bcc" property (blind-copied recipients)

	public function testSetBCCBasic()
	{
		$mailer = new Email();
		$mailer->bcc('Luke <luke@tatooine.org>');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getBcc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
	}

    public function testSetBCCMultiple()
	{
		$mailer = new Email();
		$mailer->bcc('Luke <luke@tatooine.org>', 'padme@naboo.org');

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getBcc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

	public function testSetBCCArray()
	{
		$mailer = new Email();
		$mailer->bcc(['Luke <luke@tatooine.org>', 'padme@naboo.org']);

        $emails = array_map(function($addy) {
            return $addy->getEmail();
        }, $mailer->getBcc());

		$this->assertTrue(in_array('luke@tatooine.org', $emails));
		$this->assertTrue(in_array('padme@naboo.org', $emails));
	}

    //--------------------------------------------------------------------
	// Test setting the subject

	public function testSetSubject()
	{
		$mailer    = new Email();
		$original = 'Just a silly love song';
		$mailer->setSubject($original);
		$this->assertEquals($original, $mailer->header('Subject')->getValue());
	}

	public function testSetEncodedSubject()
	{
		$mailer    = new Email();
		$original = 'Just a silly Leià song';
		$expected = '=?UTF-8?Q?Just=20a=20silly=20Lei=C3=A0=20s?==?UTF-8?Q?ong?=';
		$mailer->setSubject($original);
		$this->assertEquals($expected, $mailer->header('Subject')->getValue());
	}
}
