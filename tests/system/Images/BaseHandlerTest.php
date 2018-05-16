<?php namespace CodeIgniter\Images;

use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Config\Services;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

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

	public function setUp()
	{
		if ( ! extension_loaded('gd'))
		{
			$this->markTestSkipped('The GD extension is not available.');
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
		$this->path = $this->start . 'ci-logo.png';
	}

	//--------------------------------------------------------------------

	public function testNew()
	{
		$handler = Services::image('gd', null, false);
		$this->assertTrue($handler instanceof Handlers\BaseHandler);
	}

	public function testWithFile()
	{
		$handler = Services::image('gd', null, false);
		$handler->withFile($this->path);

		$this->assertNull($handler->getResource());
		$image = $handler->getFile();
		$this->assertTrue($image instanceof Image);
		$this->assertEquals(155, $image->origWidth);
		$this->assertEquals($this->path, $image->getPathname());
	}

	public function testMissingFile()
	{
		$this->expectException(\CodeIgniter\Files\Exceptions\FileNotFoundException::class);
		$handler = Services::image('gd', null, false);
		$handler->withFile($this->start . 'No_such_file.jpg');
	}

	// exif_read_data is not supported by vfsStream. 
	// See https://github.com/mikey179/vfsStream/wiki/Known-Issues
	// The functionality is read-only, so we need to use the original file
	public function testEXIF()
	{
		$handler = Services::image('gd', null, false);

		// nothing in our logo
		$handler->withFile(TESTPATH . $this->origin . 'ci-logo.jpeg');
		$this->assertFalse($handler->getEXIF('ExposureTime'));

		// test EXIF image, from https://commons.wikimedia.org/wiki/File:Steveston_dusk.JPG
		$handler->withFile(TESTPATH . $this->origin . 'Steveston_dusk.JPG');
		$this->assertEquals('1/33', $handler->getEXIF('ExposureTime'));
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

}
