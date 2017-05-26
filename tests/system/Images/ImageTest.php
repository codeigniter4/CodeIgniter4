<?php namespace CodeIgniter\Images;

class ImageTest extends \CIUnitTestCase
{
	protected $path = 'tests/_support/ci-logo.png';

	public function testBasicPropertiesInherited()
	{
		$image = new Image(ROOTPATH.$this->path);

		$this->assertEquals('ci-logo.png', $image->getFilename());
		$this->assertEquals(ROOTPATH.$this->path, $image->getPathname());
		$this->assertEquals(ROOTPATH.'tests/_support', $image->getPath());
		$this->assertEquals('ci-logo.png', $image->getBasename());
	}


	public function testGetProperties()
	{
		$image = new Image(ROOTPATH.$this->path);

		$expected = [
			'width'      => 155,
			'height'     => 200,
			'image_type' => IMAGETYPE_PNG,
			'size_str'   => 'width="155" height="200"',
			'mime_type'  => "image/png",
		];

		$this->assertEquals($expected, $image->getProperties(true));
	}


	public function testCanCopyDefaultName()
	{
		$image = new Image(ROOTPATH.$this->path);

		$image->copy(WRITEPATH);

		$this->assertFileExists(WRITEPATH.'ci-logo.png');

		unlink(WRITEPATH.'ci-logo.png');
	}

	public function testCanCopyNewName()
	{
		$image = new Image(ROOTPATH.$this->path);

		$image->copy(WRITEPATH, 'new-logo.png');

		$this->assertFileExists(WRITEPATH.'new-logo.png');

		unlink(WRITEPATH.'new-logo.png');
	}

}
