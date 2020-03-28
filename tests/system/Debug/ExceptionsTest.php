<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CodeIgniter\Test\CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new \Config\Exceptions(), \Config\Services::request(), \Config\Services::response());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}
}
