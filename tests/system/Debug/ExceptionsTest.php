<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions();
		$this->assertInstanceOf(Exceptions::class, $actual);
	}
}
