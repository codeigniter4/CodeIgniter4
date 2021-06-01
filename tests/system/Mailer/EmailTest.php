<?php

namespace CodeIgniter\Mailer;

use CodeIgniter\Test\CIUnitTestCase;

class EmailTest extends CIUnitTestCase
{
	public function testConstructorUsesData()
	{
		$address = 'leia@alderaan.org';
		$email   = new Email([
			'from' => $address,
		]);

		$this->assertSame($address, (string) $email->getFrom());
	}
}
