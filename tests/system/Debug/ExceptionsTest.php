<?php namespace CodeIgniter\Debug;

class ExceptionsTest extends \CIUnitTestCase
{
	public function testNew()
	{
		$actual = new Exceptions(new \Config\Exceptions(), new \CodeIgniter\HTTP\IncomingRequest(), new \CodeIgniter\HTTP\Response());
		$this->assertInstanceOf(Exceptions::class, $actual);
	}
}
