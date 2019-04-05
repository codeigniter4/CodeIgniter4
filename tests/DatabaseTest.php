<?php namespace Test;

use CodeIgniter\Test\CIDatabaseTestCase;

class DatabaseTest extends CIDatabaseTestCase
{
	public function testSomeThings()
	{
		$this->hasInDatabase('user', [
			'name'    => 'Foo Bar',
			'email'   => 'foobar@example.com',
			'country' => 'US',
		]);

		$this->seeInDatabase('user', [
			'name' => 'Foo Bar',
		]);

		$this->dontSeeInDatabase('user', [
			'name' => 'Fannie Farkle',
		]);

		$name = $this->grabFromDatabase('user', 'name', ['email' => 'foobar@example.com']);
		$this->assertEquals('Foo Bar', $name);

		$this->seeNumRecords(1, 'user', ['name' => 'Foo Bar']);
	}
}
