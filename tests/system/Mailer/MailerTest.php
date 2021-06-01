<?php namespace CodeIgniter\Mailer;

use CodeIgniter\Test\CIUnitTestCase;

class MailerTest extends CIUnitTestCase
{
	public function tearDown()
	{
		// Restore file permissions after unreadable attachment test
		$thefile = SUPPORTPATH . 'Mailer/ci-logo-not-readable.png';
		chmod($thefile, 0664);
	}

	//--------------------------------------------------------------------
	// Test constructor & configs

	public function testDefaultWithCustomConfig()
	{
		$mailer = new Mailer(['validate' => true, 'truth' => 'out there']);
		$this->assertTrue($mailer->wordWrap);
		$this->assertEquals(76, $mailer->wrapChars);
		$this->assertEquals('text', $mailer->mailType);
		$this->assertEquals('UTF-8', $mailer->charset);
		$this->assertEquals('', $mailer->altMessage);
		$this->assertTrue($mailer->validate);
		$this->assertNull($mailer->truth);
	}

	public function testDefaultWithEmptyConfig()
	{
		$mailer = new Mailer();
		$this->assertTrue($mailer->wordWrap);
		$this->assertEquals(76, $mailer->wrapChars);
		$this->assertEquals('text', $mailer->mailType);
		$this->assertEquals('UTF-8', $mailer->charset);
		$this->assertEquals('', $mailer->altMessage);
		$this->assertFalse($mailer->validate); // this one differs
		$this->assertNull($mailer->truth);
	}

	//--------------------------------------------------------------------
	// Test setting the "from" property

	public function testSetFromMailerOnly()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $mailer->getHeader('Return-Path'));
	}

	public function testSetFromMailerAndName()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $mailer->getHeader('Return-Path'));
	}

	public function testSetFromMailerAndFunkyName()
	{
		$mailer = new Mailer();
		$mailer->setFrom('<leia@alderaan.org>', 'Princess Leià');
		$this->assertEquals('=?UTF-8?Q?Princess=20Lei=C3=A0?= <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $mailer->getHeader('Return-Path'));
	}

	public function testSetFromWithValidation()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setFrom('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $mailer->getHeader('Return-Path'));
	}

	public function testSetFromWithValidationAndReturnPath()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setFrom('leia@alderaan.org', 'Princess Leia', 'leia@alderaan.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $mailer->getHeader('Return-Path'));
	}

	public function testSetFromWithValidationAndDifferentReturnPath()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setFrom('leia@alderaan.org', 'Princess Leia', 'padme@naboo.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('From'));
		$this->assertEquals('<padme@naboo.org>', $mailer->getHeader('Return-Path'));
	}

	//--------------------------------------------------------------------
	// Test setting the "replyTo" property

	public function testSetReplyToMailerOnly()
	{
		$mailer = new Mailer();
		$mailer->setReplyTo('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	public function testSetReplyToMailerAndName()
	{
		$mailer = new Mailer();
		$mailer->setReplyTo('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	public function testSetReplyToMailerAndFunkyName()
	{
		$mailer = new Mailer();
		$mailer->setReplyTo('<leia@alderaan.org>', 'Princess Leià');
		$this->assertEquals('=?UTF-8?Q?Princess=20Lei=C3=A0?= <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidation()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setReplyTo('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidationAndReturnPath()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setReplyTo('leia@alderaan.org', 'Princess Leia', 'leia@alderaan.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidationAndDifferentReturnPath()
	{
		$mailer = new Mailer(['validation' => true]);
		$mailer->setReplyTo('leia@alderaan.org', 'Princess Leia', 'padme@naboo.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $mailer->getHeader('Reply-To'));
	}

	//--------------------------------------------------------------------
	// Test setting the "to" property (recipients)

	public function testSetToBasic()
	{
		$mailer = new Mailer();
		$mailer->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->recipients));
	}

	public function testSetToArray()
	{
		$mailer = new Mailer();
		$mailer->setTo(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->recipients));
		$this->assertTrue(in_array('padme@naboo.org', $mailer->recipients));
	}

	public function testSetToValid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->recipients));
	}

	public function testSetToInvalid()
	{
		$mailer = new Mailer(['validate' => false]);
		$mailer->setTo('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $mailer->recipients));
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testDontSetToInvalid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setTo('Luke <luke@tatooine>');
	}

	public function testSetToHeader()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setProtocol('sendmail');
		$mailer->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->recipients));
		$this->assertEquals('luke@tatooine.org', $mailer->getHeader('To'));
	}

	//--------------------------------------------------------------------
	// Test setting the "cc" property (copied recipients)

	public function testSetCCBasic()
	{
		$mailer = new Mailer();
		$mailer->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->CCArray));
	}

	public function testSetCCArray()
	{
		$mailer = new Mailer();
		$mailer->setCC(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->CCArray));
		$this->assertTrue(in_array('padme@naboo.org', $mailer->CCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $mailer->getHeader('Cc'));
	}

	public function testSetCCValid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->CCArray));
	}

	public function testSetCCInvalid()
	{
		$mailer = new Mailer(['validate' => false]);
		$mailer->setCC('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $mailer->CCArray));
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testDontSetCCInvalid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setCC('Luke <luke@tatooine>');
	}

	public function testSetCCHeader()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->CCArray));
		$this->assertEquals('luke@tatooine.org', $mailer->getHeader('Cc'));
	}

	public function testSetCCForSMTP()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setProtocol('smtp');
		$mailer->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->CCArray));
		$this->assertEquals('luke@tatooine.org', $mailer->getHeader('Cc'));
	}

	//--------------------------------------------------------------------
	// Test setting the "bcc" property (blind-copied recipients)

	public function testSetBCCBasic()
	{
		$mailer = new Mailer();
		$mailer->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
	}

	public function testSetBCCArray()
	{
		$mailer = new Mailer();
		$mailer->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $mailer->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $mailer->getHeader('Bcc'));
	}

	public function testSetBCCValid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
	}

	public function testSetBCCInvalid()
	{
		$mailer = new Mailer(['validate' => false]);
		$mailer->setBCC('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $mailer->BCCArray));
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testDontSetBCCInvalid()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setBCC('Luke <luke@tatooine>');
	}

	public function testSetBCCHeader()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
		$this->assertEquals('luke@tatooine.org', $mailer->getHeader('Bcc'));
	}

	public function testSetBCCForSMTP()
	{
		$mailer = new Mailer(['validate' => true]);
		$mailer->setProtocol('smtp');
		$mailer->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
		$this->assertEquals('luke@tatooine.org', $mailer->getHeader('Bcc'));
	}

	public function testSetBCCBatch()
	{
		$mailer = new Mailer();
		$mailer->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org'], 2);
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $mailer->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $mailer->getHeader('Bcc'));
	}

	public function testSetBCCBiggerBatch()
	{
		$mailer = new Mailer();
		$mailer->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org', 'leia@alderaan.org'], 2);
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $mailer->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org, leia@alderaan.org', $mailer->getHeader('Bcc'));
	}

	//--------------------------------------------------------------------
	// Test setting the subject

	public function testSetSubject()
	{
		$mailer    = new Mailer();
		$original = 'Just a silly love song';
		$expected = '=?UTF-8?Q?Just=20a=20silly=20love=20so?==?UTF-8?Q?ng?=';
		$mailer->setSubject($original);
		$this->assertEquals($expected, $mailer->getHeader('Subject'));
	}

	public function testSetEncodedSubject()
	{
		$mailer    = new Mailer();
		$original = 'Just a silly Leià song';
		$expected = '=?UTF-8?Q?Just=20a=20silly=20Lei=C3=A0=20s?==?UTF-8?Q?ong?=';
		$mailer->setSubject($original);
		$this->assertEquals($expected, $mailer->getHeader('Subject'));
	}

	//--------------------------------------------------------------------
	// Test setting the body

	public function testSetMessage()
	{
		$mailer    = new Mailer();
		$original = 'Just a silly love song';
		$expected = $original;
		$mailer->setMessage($original);
		$this->assertEquals($expected, $mailer->body);
	}

	public function testSetMultilineMessage()
	{
		$mailer    = new Mailer();
		$original = "Just a silly love song\r\nIt's just two lines long";
		$expected = "Just a silly love song\nIt's just two lines long";
		$mailer->setMessage($original);
		$this->assertEquals($expected, $mailer->body);
	}

	//--------------------------------------------------------------------
	// Test setting the alternate message

	public function testSetAltMessage()
	{
		$mailer    = new Mailer();
		$original = 'Just a silly love song';
		$expected = $original;
		$mailer->setAltMessage($original);
		$this->assertEquals($expected, $mailer->altMessage);
	}

	public function testSetMultilineAltMessage()
	{
		$mailer    = new Mailer();
		$original = "Just a silly love song\r\nIt's just two lines long";
		$mailer->setAltMessage($original);
		$this->assertEquals($original, $mailer->altMessage);
	}

	//--------------------------------------------------------------------
	// Test clearing the email

	public function testClearing()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $mailer->getHeader('From'));
		$mailer->setTo('luke@tatooine.org');
		$this->assertTrue(in_array('luke@tatooine.org', $mailer->recipients));

		$mailer->clear(true);
		$this->assertEquals('', $mailer->getHeader('From'));
		$this->assertEquals('', $mailer->getHeader('To'));

		$mailer->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $mailer->getHeader('From'));
	}

	//--------------------------------------------------------------------
	// Test clearing the email

	public function testAttach()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$mailer->setTo('luke@tatooine.org');

		$mailer->attach(SUPPORTPATH . 'Images/ci-logo.png');
		$this->assertEquals(1, count($mailer->attachments));
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testAttachNotThere()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$mailer->setTo('luke@tatooine.org');

		$mailer->attach(SUPPORTPATH . 'Mailer/ci-logo-not-there.png');
		$this->assertEquals(1, count($mailer->attachments));
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testAttachNotReadable()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$mailer->setTo('luke@tatooine.org');

		$thefile = SUPPORTPATH . 'Mailer/ci-logo-not-readable.png';
		chmod($thefile, 0222);
		$mailer->attach($thefile);
	}

	public function testAttachContent()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$mailer->setTo('luke@tatooine.org');

		$content = 'This is bogus content';
		$mailer->attach($content, '', 'truelies.txt', 'text/html');
		$this->assertEquals(1, count($mailer->attachments));
	}

	//--------------------------------------------------------------------
	// Test changing the protocol

	public function testSetProtocol()
	{
		$mailer = new Mailer();
		$this->assertEquals('mail', $mailer->getProtocol()); // default
		$mailer->setProtocol('smtp');
		$this->assertEquals('smtp', $mailer->getProtocol());
		$mailer->setProtocol('mail');
		$this->assertEquals('mail', $mailer->getProtocol());
	}

	/**
	 * @expectedException \CodeIgniter\Mailer\Exceptions\MailerException
	 */
	public function testSetBadProtocol()
	{
		$mailer = new Mailer();
		$mailer->setProtocol('mind-reader');
	}

	//--------------------------------------------------------------------
	// Test word wrap

	public function testWordWrapVanilla()
	{
		$mailer    = new Mailer();
		$original = 'This is a short line.';
		$expected = $original;
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original)));
	}

	public function testWordWrapShortLines()
	{
		$mailer    = new Mailer();
		$original = 'This is a short line.';
		$expected = "This is a short\r\nline.";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original, 16)));
	}

	public function testWordWrapLines()
	{
		$mailer    = new Mailer();
		$original = "This is a\rshort line.";
		$expected = "This is a\r\nshort line.";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original)));
	}

	public function testWordWrapUnwrap()
	{
		$mailer    = new Mailer();
		$original = 'This is a {unwrap}not so short{/unwrap} line.';
		$expected = 'This is a not so short line.';
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original)));
	}

	public function testWordWrapUnwrapWrapped()
	{
		$mailer    = new Mailer();
		$original = 'This is a {unwrap}not so short or something{/unwrap} line.';
		$expected = "This is a\r\nnot so short or something\r\nline.";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original, 16)));
	}

	public function testWordWrapConsolidate()
	{
		$mailer    = new Mailer();
		$original = "This is\r\na not so short or something\r\nline.";
		$expected = "This is\r\na not so short\r\nor something\r\nline.";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original, 16)));
	}

	public function testWordWrapLongWord()
	{
		$mailer    = new Mailer();
		$original = "This is part of interoperabilities isn't it?";
		$expected = "This is part of\r\ninteroperabilit\r\nies\r\nisn't it?";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original, 16)));
	}

	public function testWordWrapURL()
	{
		$mailer    = new Mailer();
		$original = "This is part of http://interoperabilities.com isn't it?";
		$expected = "This is part of\r\nhttp://interoperabilities.com\r\nisn't it?";
		$this->assertEquals($expected, rtrim($mailer->wordWrap($original, 16)));
	}

	//--------------------------------------------------------------------
	// Test support methods

	public function testValidMailer()
	{
		$mailer = new Mailer();
		$this->assertTrue($mailer->isValidMailer('"Princess Leia" <leia@alderaan.org>'));
		$this->assertTrue($mailer->isValidMailer('leia@alderaan.org'));
		$this->assertTrue($mailer->isValidMailer('<princess.leia@alderaan.org>'));
		$this->assertFalse($mailer->isValidMailer('<leia_at_alderaan.org>'));
		$this->assertFalse($mailer->isValidMailer('<leia@alderaan>'));
		$this->assertFalse($mailer->isValidMailer('<leia.alderaan@org>'));
	}

	public function testMagicMethods()
	{
		$mailer           = new Mailer();
		$mailer->protocol = 'mail';
		$this->assertEquals('mail', $mailer->protocol);
	}

	//--------------------------------------------------------------------
	// "Test" sending the email

	public function testFakeSend()
	{
		$mailer = new Mailer();
		$mailer->setFrom('leia@alderaan.org');
		$mailer->setTo('Luke <luke@tatooine>');
		$mailer->setSubject('Hi there');

		// make sure the second parameter below is "false"
		// or you will trigger email for real!
		$this->assertTrue($mailer->send(true, false));
	}
}
