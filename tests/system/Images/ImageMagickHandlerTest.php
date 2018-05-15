<?php namespace CodeIgniter\Images;

use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Files\Exceptions\FileException;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

class ImageMagickHandlerTest extends \CIUnitTestCase
{

	public function setUp()
	{
		if ( ! extension_loaded('imagick'))
		{
			$this->markTestSkipped('The Imagick extension is not available.');
			return;
		}

		// create virtual file system
		$this->root = vfsStream::setup();
		// copy our support files
		$this->origin = '_support/Images/';
		vfsStream::copyFromFileSystem(TESTPATH . $this->origin, $root);
		// make subfolders
		$structure = ['work' => [], 'wontwork' => []];
		vfsStream::create($structure);
		// with one of them read only
		$wont = $this->root->getChild('wontwork')->chmod(0400);

		$this->start = $this->root->url() . '/';

		$this->image = new Image($this->start . 'ci-logo.png');
	}

	public function testCanReachImageMethods()
	{
		$this->assertInternalType('array', $this->image->getProperties(true));
	}

}
