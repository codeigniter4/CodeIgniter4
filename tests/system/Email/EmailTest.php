<?php namespace system\Email;

class EmailTest extends \CIUnitTestCase
{
	public function testEmailValidation()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Email\Email($config);
		$email->setTo('invalid');
		$this->assertStringContainsString('Invalid email address: invalid', $email->printDebugger());
	}
}
