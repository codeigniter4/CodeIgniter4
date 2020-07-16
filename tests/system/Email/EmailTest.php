<?php namespace system\Email;

use CodeIgniter\Events\Events;

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

	public function testEmailSendStoresArchive()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');
		$email->setFrom('bar@foo.com');
		$email->setSubject('Archive Test');

		$this->assertTrue($email->send());

		$this->assertNotEmpty($email->archive);
		$this->assertEquals(['foo@foo.com'], $email->archive['recipients']);
		$this->assertEquals('bar@foo.com', $email->archive['fromEmail']);
		$this->assertEquals('Archive Test', $email->archive['subject']);
	}

	public function testAutoClearLeavesArchive()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');

		$this->assertTrue($email->send(true));

		$this->assertNotEmpty($email->archive);
	}

	public function testEmailSendRepeatUpdatesArchive()
	{
		$config = config('Email');
		$email  = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');
		$email->setFrom('bar@foo.com');

		$this->assertTrue($email->send());

		$email->setFrom('');
		$email->setSubject('Archive Test');
		$this->assertTrue($email->send());

		$this->assertEquals('', $email->archive['fromEmail']);
		$this->assertEquals('Archive Test', $email->archive['subject']);
	}

	public function testSuccessDoesTriggerEvent()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');

		$result = null;

		Events::on('email', function ($arg) use (&$result) {
			$result = $arg;
		});

		$this->assertTrue($email->send());

		$this->assertIsArray($result);
		$this->assertEquals(['foo@foo.com'], $result['recipients']);
	}

	public function testFailureDoesNotTriggerEvent()
	{
		$config           = config('Email');
		$config->validate = true;
		$email            = new \CodeIgniter\Test\Mock\MockEmail($config);
		$email->setTo('foo@foo.com');
		$email->returnValue = false;

		$result = null;

		Events::on('email', function ($arg) use (&$result) {
			$result = $arg;
		});

		$this->assertFalse($email->send());

		$this->assertNull($result);
	}
}
