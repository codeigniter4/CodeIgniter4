<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new \Config\Exceptions(), \Config\Services::request(), \Config\Services::response());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}
}
