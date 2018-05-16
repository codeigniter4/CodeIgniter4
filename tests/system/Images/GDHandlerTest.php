<?php namespace CodeIgniter\Images;

use CodeIgniter\Images\BaseHandler;
use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Config\Services;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Unit testing for the GD image handler.
 * It is impractical to programmatically inspect the results of the
 * different transformations, so we have to rely on the underlying package.
 * We can make sure that we can call it without blowing up,
 * and we can make sure the code coverage is good.
 */
class GDHandlerTest extends \CIUnitTestCase
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
		$this->path = TESTPATH . $this->origin . 'ci-logo.png';
		$this->handler = Services::image('gd', null, false);
	}

	public function testGetVersion()
	{
		$version = $this->handler->getVersion();
		// make sure that the call worked
		$this->assertNotFalse($version);
		// we should have a numeric version, with 3 digits
		$this->assertGreaterThan(100, $version);
		$this->assertLessThan(999, $version);
	}

	public function testImageProperties()
	{
		$this->handler->withFile($this->path);
		$file = $this->handler->getFile();
		$props = $file->getProperties(true);

		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(155, $props['width']);
		$this->assertEquals(155, $file->origWidth);

		$this->assertEquals(200, $this->handler->getHeight());
		$this->assertEquals(200, $props['height']);
		$this->assertEquals(200, $file->origHeight);

		$this->assertEquals('width="155" height="200"', $props['size_str']);
	}

	public function testImageTypeProperties()
	{
		$this->handler->withFile($this->path);
		$file = $this->handler->getFile();
		$props = $file->getProperties(true);

		//FIXME Why is this failing? It detects the file as type 1, GIF
		$this->assertEquals(IMAGETYPE_PNG, $props['image_type']);
		$this->assertEquals('image/png', $props['mime_type']);
	}

//--------------------------------------------------------------------

	public function testResizeIgnored()
	{
		$this->handler->withFile($this->path);
		$this->handler->resize(155, 200); // 155x200 result
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	public function testResizeAbsolute()
	{
		$this->handler->withFile($this->path);
		$this->handler->resize(123, 456, false); // 123x456 result
		$this->assertEquals(123, $this->handler->getWidth());
		$this->assertEquals(456, $this->handler->getHeight());
	}

	public function testResizeAspect()
	{
		$this->handler->withFile($this->path);
		$this->handler->resize(123, 456, true); // 123x159 result
		$this->assertEquals(123, $this->handler->getWidth());
		$this->assertEquals(159, $this->handler->getHeight());
	}

	public function testResizeAspectWidth()
	{
		$this->handler->withFile($this->path);
		$this->handler->resize(123, 0, true); // 123x159 result
		$this->assertEquals(123, $this->handler->getWidth());
		$this->assertEquals(159, $this->handler->getHeight());
	}

	public function testResizeAspectHeight()
	{
		$this->handler->withFile($this->path);
		$this->handler->resize(0, 456, true); // 354x456 result
		$this->assertEquals(354, $this->handler->getWidth());
		$this->assertEquals(456, $this->handler->getHeight());
	}

//--------------------------------------------------------------------
//	public function testCrop()
//	{
//		
//	}
//--------------------------------------------------------------------

	public function testRotate()
	{
		$this->handler->withFile($this->path); // 155x200
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());

		// first rotation
		$this->handler->rotate(90); // 200x155
		$this->assertEquals(200, $this->handler->getWidth());

		// check image size again after another rotation
		$this->handler->rotate(180); // 200x155
		$this->assertEquals(200, $this->handler->getWidth());
	}

	public function testRotateBadAngle()
	{
		$this->handler->withFile($this->path);
		$this->expectException(ImageException::class);
		$this->handler->rotate(77);
	}

//	public function testFlatten()
//	{
//		
//	}
//
//	public function testReorient()
//	{
//		
//	}
//
//	public function testGetEXIT()
//	{
//		
//	}
//
//	public function testFit()
//	{
//		
//	}
}
