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
	// Test setting the "to" property

	public function testSetToBasic()
	{
		$email = new Email();
		$email->setTo('Luke <luke@tatooine.org>');
		$this->assertTrue(in_array('luke@tatooine.org', $email->recipients));
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
