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
		$this->handler = Services::image('gd');
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

	//--------------------------------------------------------------------
//	public function testResize()
//	{
//		
//	}
//
//	public function testCrop()
//	{
//		
//	}
	//--------------------------------------------------------------------

	public function testRotate()
	{
		$this->handler->withImage($this->path);
		$this->assertEquals(155,$this->handler->getWidth());
		$this->assertEquals(200,$this->handler->getHeight());
		$this->assertInstanceOf(ImageHandlerInterface::class, $this->handler->rotate(90));
		$this->assertEquals(200,$this->handler->getWidth());
		
		// check image size again after another rotation
		$this->handler->rotate(180);
		$this->assertEquals(200,$this->handler->getWidth());
	}

	public function testRotateBadAngle()
	{
		$this->handler->withImage($this->path);
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
