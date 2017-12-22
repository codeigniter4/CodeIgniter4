<?php namespace CodeIgniter\Images;

class GDHandlerTest extends \CIUnitTestCase
{
	protected $path = 'tests/_support/ci-logo.png';

	public function testCanReachImageMethods()
	{
		$image = new Image(ROOTPATH.$this->path);

		$this->assertInternalType('array', $image->getProperties(true));
	}

}
