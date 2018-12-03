<?php namespace CodeIgniter\Images;

use CodeIgniter\Config\Services;
use org\bovigo\vfs\vfsStream;

/**
 * Test the common image processing functionality.
 *
 * Note: some of the underlying PHP functions do not play nicely
 * with vfsStream, so the support files are used directly for
 * most work, and the virtual file system will be used for
 * testing saving only.
 */
class BaseHandlerTest extends \CIUnitTestCase
{

	protected function setUp()
	{
		if (! extension_loaded('gd'))
		{
			$this->markTestSkipped('The GD extension is not available.');
			return;
		}

		// create virtual file system
		$this->root = vfsStream::setup();
		// copy our support files
		$this->origin = SUPPORTPATH . 'Images/';
		vfsStream::copyFromFileSystem($this->origin, $this->root);
		// make subfolders
		$structure = [
			'work'     => [],
			'wontwork' => [],
		];
		vfsStream::create($structure);
		// with one of them read only
		$wont = $this->root->getChild('wontwork')->chmod(0400);

		// for VFS tests
		$this->start = $this->root->url() . '/';
		$this->path  = $this->start . 'ci-logo.png';
	}

	//--------------------------------------------------------------------

	public function testNew()
	{
		$handler = Services::image('gd', null, false);
		$this->assertTrue($handler instanceof Handlers\BaseHandler);
	}

	public function testWithFile()
	{
		$path    = $this->origin . 'ci-logo.png';
		$handler = Services::image('gd', null, false);
		$handler->withFile($path);

		$image = $handler->getFile();
		$this->assertTrue($image instanceof Image);
		$this->assertEquals(155, $image->origWidth);
		$this->assertEquals($path, $image->getPathname());
	}

	public function testMissingFile()
	{
		$this->expectException(\CodeIgniter\Files\Exceptions\FileNotFoundException::class);
		$handler = Services::image('gd', null, false);
		$handler->withFile($this->start . 'No_such_file.jpg');
	}

	public function testFileTypes()
	{
		$handler = Services::image('gd', null, false);
		$handler->withFile($this->start . 'ci-logo.png');
		$image = $handler->getFile();
		$this->assertTrue($image instanceof Image);

		$handler->withFile($this->start . 'ci-logo.jpeg');
		$image = $handler->getFile();
		$this->assertTrue($image instanceof Image);

		$handler->withFile($this->start . 'ci-logo.gif');
		$image = $handler->getFile();
		$this->assertTrue($image instanceof Image);
	}

	//--------------------------------------------------------------------
	// Something handled by our Image
	public function testImageHandled()
	{
		$handler = Services::image('gd', null, false);
		$handler->withFile($this->path);
		$this->assertEquals($this->path, $handler->getPathname());
	}

}
