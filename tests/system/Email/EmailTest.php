<?php
namespace CodeIgniter\Email;

class EmailTest extends \CIUnitTestCase
{

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

	public function testSetFromEmailOnly()
	{
		$email = new Email();
		$email->setFrom('<leia@alderaan.org>');
		$this->assertEquals(' <leia@alderaan.org>', $email->getHeader('From'));
		$this->assertEquals('<leia@alderaan.org>', $email->getHeader('Return-Path'));
	}

	public function testSetFromEmailAndName()
	{
		$email = new Email();
		$email->setFrom('<leia@alderaan.org>', 'Princess Leia');
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

}
