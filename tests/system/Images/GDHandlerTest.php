<?php namespace CodeIgniter\Images;

use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Config\Services;
use org\bovigo\vfs\vfsStream;

/**
 * Unit testing for the GD image handler.
 * It is impractical to programmatically inspect the results of the
 * different transformations, so we have to rely on the underlying package.
 * We can make sure that we can call it without blowing up,
 * and we can make sure the code coverage is good.
 *
 * Was unable to test fontPath & related logic.
 */
class GDHandlerTest extends \CIUnitTestCase
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
		// make subfolders
		$structure = [
			'work'     => [],
			'wontwork' => [],
		];
		vfsStream::create($structure);
		// with one of them read only
		$wont = $this->root->getChild('wontwork')->chmod(0400);

		$this->start = $this->root->url() . '/';

		$this->path    = $this->origin . 'ci-logo.png';
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
		$file  = $this->handler->getFile();
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
		$file  = $this->handler->getFile();
		$props = $file->getProperties(true);

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

	public function testCropTopLeft()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(100, 100); // 100x100 result
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

	public function testCropMiddle()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(100, 100, 50, 50, false); // 100x100 result
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

	public function testCropMiddlePreserved()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(100, 100, 50, 50, true); // 78x100 result
		$this->assertEquals(78, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

	public function testCropTopLeftPreserveAspect()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(100, 100); // 100x100 result
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

	public function testCropNothing()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(155, 200); // 155x200 result
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	public function testCropOutOfBounds()
	{
		$this->handler->withFile($this->path);
		$this->handler->crop(100, 100, 100); // 55x100 result in 100x100
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

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

	//--------------------------------------------------------------------

	public function testFlatten()
	{
		$this->handler->withFile($this->path);
		$this->handler->flatten();
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	//--------------------------------------------------------------------

	public function testFlip()
	{
		$this->handler->withFile($this->path);
		$this->handler->flip();
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	public function testHorizontal()
	{
		$this->handler->withFile($this->path);
		$this->handler->flip('horizontal');
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	public function testFlipVertical()
	{
		$this->handler->withFile($this->path);
		$this->handler->flip('vertical');
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	public function testFlipUnknown()
	{
		$this->handler->withFile($this->path);
		$this->expectException(ImageException::class);
		$this->handler->flip('bogus');
	}

	//--------------------------------------------------------------------
	public function testFit()
	{
		$this->handler->withFile($this->path);
		$this->handler->fit(100, 100);
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(100, $this->handler->getHeight());
	}

	public function testFitTaller()
	{
		$this->handler->withFile($this->path);
		$this->handler->fit(100, 400);
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(400, $this->handler->getHeight());
	}

	public function testFitAutoHeight()
	{
		$this->handler->withFile($this->path);
		$this->handler->fit(100);
		$this->assertEquals(100, $this->handler->getWidth());
		$this->assertEquals(129, $this->handler->getHeight());
	}

	public function testFitPositions()
	{
		$choices = [
			'top-left',
			'top',
			'top-right',
			'left',
			'center',
			'right',
			'bottom-left',
			'bottom',
			'bottom-right',
		];
		$this->handler->withFile($this->path);
		foreach ($choices as $position)
		{
			$this->handler->fit(100, 100, $position);
			$this->assertEquals(100, $this->handler->getWidth(), 'Position ' . $position . ' failed');
			$this->assertEquals(100, $this->handler->getHeight(), 'Position ' . $position . ' failed');
		}
	}

	//--------------------------------------------------------------------

	public function testText()
	{
		$this->handler->withFile($this->path);
		$this->handler->text('vertical', ['hAlign' => 'right', 'vAlign' => 'bottom']);
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	//--------------------------------------------------------------------

	public function testMoreText()
	{
		$this->handler->withFile($this->path);
		$this->handler->text('vertical', ['vAlign' => 'middle', 'withShadow' => 'sure', 'shadowOffset' => 3]);
		$this->assertEquals(155, $this->handler->getWidth());
		$this->assertEquals(200, $this->handler->getHeight());
	}

	//--------------------------------------------------------------------

	public function testImageCreation()
	{
		foreach (['gif', 'jpeg', 'png'] as $type)
		{
			$this->handler->withFile($this->origin . 'ci-logo.' . $type);
			$this->handler->text('vertical');
			$this->assertEquals(155, $this->handler->getWidth());
			$this->assertEquals(200, $this->handler->getHeight());
		}
	}

	//--------------------------------------------------------------------

	public function testImageSave()
	{
		foreach (['gif', 'jpeg', 'png'] as $type)
		{
			$this->handler->withFile($this->origin . 'ci-logo.' . $type);
			$this->handler->getResource(); // make sure resource is loaded
			$this->handler->save($this->start . 'work/ci-logo.' . $type);
			$this->assertTrue($this->root->hasChild('work/ci-logo.' . $type));
		}
	}

}
