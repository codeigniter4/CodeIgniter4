<?php
namespace CodeIgniter\Email;

class EmailTest extends \CIUnitTestCase
{

	public function tearDown()
	{
		// restore file permissions after unreadable attachment test
		$thefile = SUPPORTPATH . 'Email/ci-logo-not-readable.png';
		chmod($thefile, 0664);
	}

	//--------------------------------------------------------------------
	// Test constructor & configs

	public function testDefaultWithCustomConfig()
	{
		$email = new Email(['validate' => true, 'truth' => 'out there']);
		$this->assertTrue($email->wordWrap);
		$this->assertEquals(76, $email->wrapChars);
		$this->assertEquals('text', $email->mailType);
		$this->assertEquals('UTF-8', $email->charset);
		$this->assertEquals('', $email->altMessage);
		$this->assertTrue($email->validate);
		$this->assertNull($email->truth);
	}

	public function testDefaultWithEmptyConfig()
	{
		$email = new Email();
		$this->assertTrue($email->wordWrap);
		$this->assertEquals(76, $email->wrapChars);
		$this->assertEquals('text', $email->mailType);
		$this->assertEquals('UTF-8', $email->charset);
		$this->assertEquals('', $email->altMessage);
		$this->assertFalse($email->validate); // this one differs
		$this->assertNull($email->truth);
	}

	//--------------------------------------------------------------------
	// Test setting the "from" property

	public function testSetFromEmailOnly()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromEmailAndName()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromEmailAndFunkyName()
	{
		$email = new Email();
		$email->setFrom('<leia@alderaan.org>', 'Princess Leià');
		$this->assertEquals('=?UTF-8?Q?Princess=20Lei=C3=A0?= <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromWithValidation()
	{
		$email = new Email(['validation' => true]);
		$email->setFrom('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromWithValidationAndReturnPath()
	{
		$email = new Email(['validation' => true]);
		$email->setFrom('leia@alderaan.org', 'Princess Leia', 'leia@alderaan.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromWithValidationAndDifferentReturnPath()
	{
		$email = new Email(['validation' => true]);
		$email->setFrom('leia@alderaan.org', 'Princess Leia', 'padme@naboo.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<padme@naboo.org>', $email->getHeader('Return-Path'));
	}

	//--------------------------------------------------------------------
	// Test setting the "replyTo" property

	public function testSetReplyToEmailOnly()
	{
		$email = new Email();
		$email->setReplyTo('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	public function testSetReplyToEmailAndName()
	{
		$email = new Email();
		$email->setReplyTo('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	public function testSetReplyToEmailAndFunkyName()
	{
		$email = new Email();
		$email->setReplyTo('<leia@alderaan.org>', 'Princess Leià');
		$this->assertEquals('=?UTF-8?Q?Princess=20Lei=C3=A0?= <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidation()
	{
		$email = new Email(['validation' => true]);
		$email->setReplyTo('leia@alderaan.org', 'Princess Leia');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidationAndReturnPath()
	{
		$email = new Email(['validation' => true]);
		$email->setReplyTo('leia@alderaan.org', 'Princess Leia', 'leia@alderaan.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	public function testSetReplyToWithValidationAndDifferentReturnPath()
	{
		$email = new Email(['validation' => true]);
		$email->setReplyTo('leia@alderaan.org', 'Princess Leia', 'padme@naboo.org');
		$this->assertEquals('"Princess Leia" <leia@alderaan.org>', $email->getHeader('Reply-To'));
	}

	//--------------------------------------------------------------------
	// Test setting the "to" property (recipients)

	public function testSetToBasic()
	{
		$email = new Email();
		$email->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));
	}

	public function testSetToArray()
	{
		$email = new Email();
		$email->setTo(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));
		$this->assertTrue(in_array('padme@naboo.org', $email->recipients));
	}

	public function testSetToValid()
	{
		$email = new Email(['validate' => true]);
		$email->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));
	}

	public function testSetToInvalid()
	{
		$email = new Email(['validate' => false]);
		$email->setTo('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $email->recipients));
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testDontSetToInvalid()
	{
		$email = new Email(['validate' => true]);
		$email->setTo('Luke <luke@tatooine>');
	}

	public function testSetToHeader()
	{
		$email = new Email(['validate' => true]);
		$email->setProtocol('sendmail');
		$email->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));
		$this->assertEquals('luke@tatooine.org', $email->getHeader('To'));
	}

	//--------------------------------------------------------------------
	// Test setting the "cc" property (copied recipients)

	public function testSetCCBasic()
	{
		$email = new Email();
		$email->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->CCArray));
	}

	public function testSetCCArray()
	{
		$email = new Email();
		$email->setCC(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $email->CCArray));
		$this->assertTrue(in_array('padme@naboo.org', $email->CCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $email->getHeader('Cc'));
	}

	public function testSetCCValid()
	{
		$email = new Email(['validate' => true]);
		$email->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->CCArray));
	}

	public function testSetCCInvalid()
	{
		$email = new Email(['validate' => false]);
		$email->setCC('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $email->CCArray));
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testDontSetCCInvalid()
	{
		$email = new Email(['validate' => true]);
		$email->setCC('Luke <luke@tatooine>');
	}

	public function testSetCCHeader()
	{
		$email = new Email(['validate' => true]);
		$email->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->CCArray));
		$this->assertEquals('luke@tatooine.org', $email->getHeader('Cc'));
	}

	public function testSetCCForSMTP()
	{
		$email = new Email(['validate' => true]);
		$email->setProtocol('smtp');
		$email->setCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->CCArray));
		$this->assertEquals('luke@tatooine.org', $email->getHeader('Cc'));
	}

	//--------------------------------------------------------------------
	// Test setting the "bcc" property (blind-copied recipients)

	public function testSetBCCBasic()
	{
		$email = new Email();
		$email->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
	}

	public function testSetBCCArray()
	{
		$email = new Email();
		$email->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org']);
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $email->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $email->getHeader('Bcc'));
	}

	public function testSetBCCValid()
	{
		$email = new Email(['validate' => true]);
		$email->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
	}

	public function testSetBCCInvalid()
	{
		$email = new Email(['validate' => false]);
		$email->setBCC('Luke <luke@tatooine>');
		$this->assertTrue(in_array('luke@tatooine', $email->BCCArray));
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testDontSetBCCInvalid()
	{
		$email = new Email(['validate' => true]);
		$email->setBCC('Luke <luke@tatooine>');
	}

	public function testSetBCCHeader()
	{
		$email = new Email(['validate' => true]);
		$email->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
		$this->assertEquals('luke@tatooine.org', $email->getHeader('Bcc'));
	}

	public function testSetBCCForSMTP()
	{
		$email = new Email(['validate' => true]);
		$email->setProtocol('smtp');
		$email->setBCC('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
		$this->assertEquals('luke@tatooine.org', $email->getHeader('Bcc'));
	}

	public function testSetBCCBatch()
	{
		$email = new Email();
		$email->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org'], 2);
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $email->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org', $email->getHeader('Bcc'));
	}

	public function testSetBCCBiggerBatch()
	{
		$email = new Email();
		$email->setBCC(['Luke <luke@tatooine.org>', 'padme@naboo.org', 'leia@alderaan.org'], 2);
		$this->assertTrue(in_array('luke@tatooine.org', $email->BCCArray));
		$this->assertTrue(in_array('padme@naboo.org', $email->BCCArray));
		$this->assertEquals('luke@tatooine.org, padme@naboo.org, leia@alderaan.org', $email->getHeader('Bcc'));
	}

	//--------------------------------------------------------------------
	// Test setting the subject

	public function testSetSubject()
	{
		$email    = new Email();
		$original = 'Just a silly love song';
		$expected = '=?UTF-8?Q?Just=20a=20silly=20love=20so?==?UTF-8?Q?ng?=';
		$email->setSubject($original);
		$this->assertEquals($expected, $email->getHeader('Subject'));
	}

	public function testSetEncodedSubject()
	{
		$email    = new Email();
		$original = 'Just a silly Leià song';
		$expected = '=?UTF-8?Q?Just=20a=20silly=20Lei=C3=A0=20s?==?UTF-8?Q?ong?=';
		$email->setSubject($original);
		$this->assertEquals($expected, $email->getHeader('Subject'));
	}

	//--------------------------------------------------------------------
	// Test setting the body

	public function testSetMessage()
	{
		$email    = new Email();
		$original = 'Just a silly love song';
		$expected = $original;
		$email->setMessage($original);
		$this->assertEquals($expected, $email->body);
	}

	public function testSetMultilineMessage()
	{
		$email    = new Email();
		$original = "Just a silly love song\r\nIt's just two lines long";
		$expected = "Just a silly love song\nIt's just two lines long";
		$email->setMessage($original);
		$this->assertEquals($expected, $email->body);
	}

	//--------------------------------------------------------------------
	// Test setting the alternate message

	public function testSetAltMessage()
	{
		$email    = new Email();
		$original = 'Just a silly love song';
		$expected = $original;
		$email->setAltMessage($original);
		$this->assertEquals($expected, $email->altMessage);
	}

	public function testSetMultilineAltMessage()
	{
		$email    = new Email();
		$original = "Just a silly love song\r\nIt's just two lines long";
		$email->setAltMessage($original);
		$this->assertEquals($original, $email->altMessage);
	}

	//--------------------------------------------------------------------
	// Test clearing the email

	public function testClearing()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $email->getHeader('From'));
		$email->setTo('luke@tatooine.org');
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));

		$email->clear(true);
		$this->assertEquals('', $email->getHeader('From'));
		$this->assertEquals('', $email->getHeader('To'));

		$email->setFrom('leia@alderaan.org');
		$this->assertEquals(' <leia@alderaan.org>', $email->getHeader('From'));
	}

	//--------------------------------------------------------------------
	// Test clearing the email

	public function testAttach()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$email->setTo('luke@tatooine.org');

		$email->attach(SUPPORTPATH . 'Images/ci-logo.png');
		$this->assertEquals(1, count($email->attachments));
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testAttachNotThere()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$email->setTo('luke@tatooine.org');

		$email->attach(SUPPORTPATH . 'Email/ci-logo-not-there.png');
		$this->assertEquals(1, count($email->attachments));
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testAttachNotReadable()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$email->setTo('luke@tatooine.org');

		$thefile = SUPPORTPATH . 'Email/ci-logo-not-readable.png';
		chmod($thefile, 0222);
		$email->attach($thefile);
	}

	public function testAttachContent()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$email->setTo('luke@tatooine.org');

		$content = 'This is bogus content';
		$email->attach($content, '', 'truelies.txt', 'text/html');
		$this->assertEquals(1, count($email->attachments));
	}

	//--------------------------------------------------------------------
	// Test changing the protocol

	public function testSetProtocol()
	{
		$email = new Email();
		$this->assertEquals('mail', $email->getProtocol()); // default
		$email->setProtocol('smtp');
		$this->assertEquals('smtp', $email->getProtocol());
		$email->setProtocol('mail');
		$this->assertEquals('mail', $email->getProtocol());
	}

	/**
	 * @expectedException \CodeIgniter\Email\Exceptions\EmailException
	 */
	public function testSetBadProtocol()
	{
		$email = new Email();
		$email->setProtocol('mind-reader');
	}

	//--------------------------------------------------------------------
	// Test word wrap

	public function testWordWrapVanilla()
	{
		$email    = new Email();
		$original = 'This is a short line.';
		$expected = $original;
		$this->assertEquals($expected, rtrim($email->wordWrap($original)));
	}

	public function testWordWrapShortLines()
	{
		$email    = new Email();
		$original = 'This is a short line.';
		$expected = "This is a short\r\nline.";
		$this->assertEquals($expected, rtrim($email->wordWrap($original, 16)));
	}

	public function testWordWrapLines()
	{
		$email    = new Email();
		$original = "This is a\rshort line.";
		$expected = "This is a\r\nshort line.";
		$this->assertEquals($expected, rtrim($email->wordWrap($original)));
	}

	public function testWordWrapUnwrap()
	{
		$email    = new Email();
		$original = 'This is a {unwrap}not so short{/unwrap} line.';
		$expected = 'This is a not so short line.';
		$this->assertEquals($expected, rtrim($email->wordWrap($original)));
	}

	public function testWordWrapUnwrapWrapped()
	{
		$email    = new Email();
		$original = 'This is a {unwrap}not so short or something{/unwrap} line.';
		$expected = "This is a\r\nnot so short or something\r\nline.";
		$this->assertEquals($expected, rtrim($email->wordWrap($original, 16)));
	}

	public function testWordWrapConsolidate()
	{
		$email    = new Email();
		$original = "This is\r\na not so short or something\r\nline.";
		$expected = "This is\r\na not so short\r\nor something\r\nline.";
		$this->assertEquals($expected, rtrim($email->wordWrap($original, 16)));
	}

	public function testWordWrapLongWord()
	{
		$email    = new Email();
		$original = "This is part of interoperabilities isn't it?";
		$expected = "This is part of\r\ninteroperabilit\r\nies\r\nisn't it?";
		$this->assertEquals($expected, rtrim($email->wordWrap($original, 16)));
	}

	public function testWordWrapURL()
	{
		$email    = new Email();
		$original = "This is part of http://interoperabilities.com isn't it?";
		$expected = "This is part of\r\nhttp://interoperabilities.com\r\nisn't it?";
		$this->assertEquals($expected, rtrim($email->wordWrap($original, 16)));
	}

	//--------------------------------------------------------------------
	// Test support methods

	public function testValidEmail()
	{
		$email = new Email();
		$this->assertTrue($email->isValidEmail('"Princess Leia" <leia@alderaan.org>'));
		$this->assertTrue($email->isValidEmail('leia@alderaan.org'));
		$this->assertTrue($email->isValidEmail('<princess.leia@alderaan.org>'));
		$this->assertFalse($email->isValidEmail('<leia_at_alderaan.org>'));
		$this->assertFalse($email->isValidEmail('<leia@alderaan>'));
		$this->assertFalse($email->isValidEmail('<leia.alderaan@org>'));
	}

	public function testMagicMethods()
	{
		$email           = new Email();
		$email->protocol = 'mail';
		$this->assertEquals('mail', $email->protocol);
	}

	//--------------------------------------------------------------------
	// "Test" sending the email

	public function testFakeSend()
	{
		$email = new Email();
		$email->setFrom('leia@alderaan.org');
		$email->setTo('Luke <luke@tatooine>');
		$email->setSubject('Hi there');

		// make sure the second parameter below is "false"
		// or you will trigger email for real!
		$this->assertTrue($email->send(true, false));
	}

}
