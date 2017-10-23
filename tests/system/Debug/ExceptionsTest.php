<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new \Config\Exceptions());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}
}
