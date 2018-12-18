<?php
namespace CodeIgniter\Email;

class EmailTest extends \CIUnitTestCase
{

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
		$original = "Just a silly love song\n\rIt's just two lines long";
		$expected = "Just a silly love song\nIt's just two lines long";
		$email->setMessage($original);
		$this->assertEquals($expected, $email->body);
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

}
