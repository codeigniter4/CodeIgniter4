<?php

class ExampleSessionTest extends \Tests\Support\SessionTestCase
{
	public function setUp(): void
	{
		parent::setUp();
	}

	public function testSessionSimple()
	{
		$this->session->set('logged_in', 123);

		$value = $this->session->get('logged_in');

		$this->assertEquals(123, $value);
	}
}
