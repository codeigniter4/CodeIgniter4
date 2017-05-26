<?php namespace CodeIgniter\Images;

class GDHandlerTest extends \CIUnitTestCase
{
	protected $path = 'tests/_support/ci-logo.png';

	public function testCanReachImageMethods()
	{
		$image = new Image(ROOTPATH.$this->path);

		$this->assertTrue(is_array($image->getProperties(true)));
	}

}
