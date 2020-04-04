<?php namespace system\Email;

class EmailTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function testEmailValidation()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Email\Email($config);
		$email->setTo('invalid');
		$this->assertStringContainsString('Invalid email address: invalid', $email->printDebugger());
	}

	public function autoClearProvider()
	{
		return [
			'autoclear'     => [true],
			'not autoclear' => [false],
		];
	}

	/**
	 * @dataProvider autoClearProvider
	 */
	public function testEmailSendWithClearance($autoClear)
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');

		$this->assertTrue($email->send($autoClear));

		if (! $autoClear)
		{
			$this->assertEquals('foo@foo.com', $email->archive['recipients'][0]);
		}
	}
}
