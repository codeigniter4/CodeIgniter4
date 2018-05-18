<?php namespace CodeIgniter\Test;

class TestTest extends \CIUnitTestCase
{
	public function testGetPrivatePropertyWithObject()
	{
		$obj = new __TestForReflectionHelper();
		$actual = $this->getPrivateProperty($obj, 'private');
		$this->assertEquals('secret', $actual);
	}


}
