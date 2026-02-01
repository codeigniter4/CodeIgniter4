<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Images;

use CodeIgniter\Config\Services;
use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Images\Handlers\BaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Images;
use Imagick;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;

/**
 * Unit testing for the ImageMagick image handler.
 * It is impractical to programmatically inspect the results of the
 * different transformations, so we have to rely on the underlying package.
 * We can make sure that we can call it without blowing up,
 * and we can make sure the code coverage is good.
 *
 * Was unable to test fontPath & related logic.
 *
 * @internal
 */
#[Group('Others')]
#[RequiresPhpExtension('imagick')]
final class ImageMagickHandlerTest extends CIUnitTestCase
{
    private string $root;
    private string $origin;
    private string $path;
    private BaseHandler $handler;

    protected function setUp(): void
    {
        if (! extension_loaded('imagick')) {
            $this->markTestSkipped('The IMAGICK extension is not available.');
        }

        $this->root = WRITEPATH . 'cache/';

        // cleanup everything
        helper('filesystem');
        delete_files($this->root, false, true);

        // copy our support files
        $this->origin = SUPPORTPATH . 'Images/';

        $this->path = $this->origin . 'ci-logo.png';

        // get our locally available `convert`
        $config        = new Images();
        $this->handler = Services::image('imagick', $config, false);
    }

    public function testGetVersion(): void
    {
        $version = $this->handler->getVersion();

        $this->assertNotSame('', $version);
        $this->assertTrue(version_compare($version, '6.0.0', '>'));
        $this->assertTrue(version_compare($version, '99.0.0', '<'));
    }

    public function testImageProperties(): void
    {
        $this->handler->withFile($this->path);
        $file  = $this->handler->getFile();
        $props = $file->getProperties(true);

        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(155, $props['width']);
        $this->assertSame(155, $file->origWidth);

        $this->assertSame(200, $this->handler->getHeight());
        $this->assertSame(200, $props['height']);
        $this->assertSame(200, $file->origHeight);

        $this->assertSame('width="155" height="200"', $props['size_str']);
    }

    public function testImageTypeProperties(): void
    {
        $this->handler->withFile($this->path);
        $file  = $this->handler->getFile();
        $props = $file->getProperties(true);

        $this->assertSame(IMAGETYPE_PNG, $props['image_type']);
        $this->assertSame('image/png', $props['mime_type']);
    }

    public function testResizeIgnored(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->resize(155, 200); // 155x200 result
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testResizeAbsolute(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->resize(123, 456, false); // 123x456 result
        $this->assertSame(123, $this->handler->getWidth());
        $this->assertSame(456, $this->handler->getHeight());
    }

    public function testResizeAspect(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->resize(123, 456, true); // 123x159 result
        $this->assertSame(123, $this->handler->getWidth());
        $this->assertSame(159, $this->handler->getHeight());
    }

    public function testResizeAspectWidth(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->resize(123, 0, true); // 123x159 result
        $this->assertSame(123, $this->handler->getWidth());
        $this->assertSame(159, $this->handler->getHeight());
    }

    public function testResizeAspectHeight(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->resize(0, 456, true); // 353x456 result
        $this->assertSame(353, $this->handler->getWidth());
        $this->assertSame(456, $this->handler->getHeight());
    }

    public function testCropTopLeft(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(100, 100); // 100x100 result
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testCropMiddle(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(100, 100, 50, 50, false); // 100x100 result
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testCropMiddlePreserved(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(100, 100, 50, 50, true); // 78x100 result
        $this->assertSame(78, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testCropTopLeftPreserveAspect(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(100, 100); // 100x100 result
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testCropNothing(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(155, 200); // 155x200 result
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testCropOutOfBounds(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->crop(100, 100, 100); // 55x100 result in 100x100
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testRotate(): void
    {
        $this->handler->withFile($this->path); // 155x200
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());

        // first rotation
        $this->handler->rotate(90); // 200x155
        $this->assertSame(200, $this->handler->getWidth());

        // check image size again after another rotation
        $this->handler->rotate(180); // 200x155
        $this->assertSame(200, $this->handler->getWidth());
    }

    public function testRotateBadAngle(): void
    {
        $this->handler->withFile($this->path);
        $this->expectException(ImageException::class);
        $this->handler->rotate(77);
    }

    public function testFlatten(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->flatten();
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testFlip(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->flip();
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testHorizontal(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->flip('horizontal');
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testFlipVertical(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->flip('vertical');
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testFlipUnknown(): void
    {
        $this->handler->withFile($this->path);
        $this->expectException(ImageException::class);
        $this->handler->flip('bogus');
    }

    public function testFit(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->fit(100, 100);
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(100, $this->handler->getHeight());
    }

    public function testFitTaller(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->fit(100, 400);
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(400, $this->handler->getHeight());
    }

    public function testFitAutoHeight(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->fit(100);
        $this->assertSame(100, $this->handler->getWidth());
        $this->assertSame(129, $this->handler->getHeight());
    }

    public function testFitPositions(): void
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

        foreach ($choices as $position) {
            $this->handler->fit(100, 100, $position);
            $this->assertSame(100, $this->handler->getWidth(), 'Position ' . $position . ' failed');
            $this->assertSame(100, $this->handler->getHeight(), 'Position ' . $position . ' failed');
        }
    }

    public function testText(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->text('vertical', ['hAlign' => 'right', 'vAlign' => 'bottom']);
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testMoreText(): void
    {
        $this->handler->withFile($this->path);
        $this->handler->text('vertical', ['vAlign' => 'middle', 'withShadow' => 'sure', 'shadowOffset' => 3]);
        $this->assertSame(155, $this->handler->getWidth());
        $this->assertSame(200, $this->handler->getHeight());
    }

    public function testImageCreation(): void
    {
        foreach (['gif', 'jpeg', 'png', 'webp'] as $type) {
            if ($type === 'webp' && ! in_array('WEBP', Imagick::queryFormats(), true)) {
                $this->expectException(ImageException::class);
                $this->expectExceptionMessage('Your server does not support the GD function required to process this type of image.');
            }

            $this->handler->withFile($this->origin . 'ci-logo.' . $type);
            $this->handler->text('vertical');
            $this->assertSame(155, $this->handler->getWidth());
            $this->assertSame(200, $this->handler->getHeight());
        }
    }

    public function testImageCopy(): void
    {
        foreach (['gif', 'jpeg', 'png', 'webp'] as $type) {
            if ($type === 'webp' && ! in_array('WEBP', Imagick::queryFormats(), true)) {
                $this->expectException(ImageException::class);
                $this->expectExceptionMessage('Your server does not support the GD function required to process this type of image.');
            }

            $this->handler->withFile($this->origin . 'ci-logo.' . $type);
            $this->handler->save($this->root . 'ci-logo.' . $type);
            $this->assertFileExists($this->root . 'ci-logo.' . $type);

            $this->assertNotSame(
                file_get_contents($this->origin . 'ci-logo.' . $type),
                file_get_contents($this->root . 'ci-logo.' . $type),
            );
        }
    }

    public function testImageCopyWithNoTargetAndMaxQuality(): void
    {
        foreach (['gif', 'jpeg', 'png', 'webp'] as $type) {
            $this->handler->withFile($this->origin . 'ci-logo.' . $type);
            $this->handler->save(null, 100);
            $this->assertFileExists($this->origin . 'ci-logo.' . $type);

            $this->assertSame(
                file_get_contents($this->origin . 'ci-logo.' . $type),
                file_get_contents($this->origin . 'ci-logo.' . $type),
            );
        }
    }

    public function testImageCompressionGetResource(): void
    {
        foreach (['gif', 'jpeg', 'png', 'webp'] as $type) {
            if ($type === 'webp' && ! in_array('WEBP', Imagick::queryFormats(), true)) {
                $this->expectException(ImageException::class);
                $this->expectExceptionMessage('Your server does not support the GD function required to process this type of image.');
            }

            $this->handler->withFile($this->origin . 'ci-logo.' . $type);
            $this->handler->getResource(); // make sure resource is loaded
            $this->handler->save($this->root . 'ci-logo.' . $type);
            $this->assertFileExists($this->root . 'ci-logo.' . $type);

            $this->assertNotSame(
                file_get_contents($this->origin . 'ci-logo.' . $type),
                file_get_contents($this->root . 'ci-logo.' . $type),
            );
        }
    }

    public function testImageCompressionWithResource(): void
    {
        foreach (['gif', 'jpeg', 'png', 'webp'] as $type) {
            if ($type === 'webp' && ! in_array('WEBP', Imagick::queryFormats(), true)) {
                $this->expectException(ImageException::class);
                $this->expectExceptionMessage('Your server does not support the GD function required to process this type of image.');
            }

            $this->handler->withFile($this->origin . 'ci-logo.' . $type)
                ->withResource() // make sure resource is loaded
                ->save($this->root . 'ci-logo.' . $type);

            $this->assertFileExists($this->root . 'ci-logo.' . $type);

            $this->assertNotSame(
                file_get_contents($this->origin . 'ci-logo.' . $type),
                file_get_contents($this->root . 'ci-logo.' . $type),
            );
        }
    }

    public function testImageConvert(): void
    {
        $this->handler->withFile($this->origin . 'ci-logo.jpeg');
        $this->handler->convert(IMAGETYPE_PNG);
        $this->handler->save($this->root . 'ci-logo.png');
        $this->assertSame(IMAGETYPE_PNG, exif_imagetype($this->root . 'ci-logo.png'));
    }

    public function testImageReorientLandscape(): void
    {
        for ($i = 0; $i <= 8; $i++) {
            $source = $this->origin . 'EXIFsamples/landscape_' . $i . '.jpg';
            $result = $this->root . 'landscape_' . $i . '_reoriented.jpg';

            $this->handler->withFile($source);
            $this->handler->reorient();
            $this->handler->save($result);

            $resource = imagecreatefromjpeg($result);
            $point    = imagecolorat($resource, 0, 0);
            $rgb      = imagecolorsforindex($resource, $point);

            $this->assertSame(['red' => 62, 'green' => 62, 'blue' => 62, 'alpha' => 0], $rgb);
        }
    }

    public function testImageReorientPortrait(): void
    {
        for ($i = 0; $i <= 8; $i++) {
            $source = $this->origin . 'EXIFsamples/portrait_' . $i . '.jpg';
            $result = $this->root . 'portrait_' . $i . '_reoriented.jpg';

            $this->handler->withFile($source);
            $this->handler->reorient();
            $this->handler->save($result);

            $resource = imagecreatefromjpeg($result);
            $point    = imagecolorat($resource, 0, 0);
            $rgb      = imagecolorsforindex($resource, $point);

            $this->assertSame(['red' => 62, 'green' => 62, 'blue' => 62, 'alpha' => 0], $rgb);
        }
    }

    public function testCommandInjectionPrevention(): void
    {
        $injectionFile     = 'ci4_security_test.txt';
        $maliciousFilename = 'image.png`echo 123456 | tee ' . $injectionFile . '`';
        $tempPath          = $this->root . $maliciousFilename;

        $source = $this->origin . 'rocket.png';

        if (file_exists($injectionFile)) {
            unlink($injectionFile);
        }

        file_put_contents($tempPath, file_get_contents($source));

        try {
            $this->handler->withFile($tempPath);
            $this->handler->resize(50, 50);
        } catch (ImageException) {
            $this->fail('Command injection succeeded. Fix is incomplete.');
        } finally {
            // Check that the command injection file was NOT created
            $this->assertFileDoesNotExist($injectionFile);

            // Verify the image processing still works normally
            $this->assertSame(50, $this->handler->getWidth());
            $this->assertSame(50, $this->handler->getHeight());

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function testCommandInjectionPreventionWithText(): void
    {
        $injectionFile = 'ci4_security_test.txt';
        $tempFilename  = 'image.png';
        $tempPath      = $this->root . $tempFilename;

        $source = $this->origin . 'rocket.png';

        if (file_exists($injectionFile)) {
            unlink($injectionFile);
        }

        file_put_contents($tempPath, file_get_contents($source));

        try {
            $this->handler->withFile($tempPath);
            $text = "Hello'; echo 123456 > {$injectionFile}; echo 'World";
            $this->handler->text($text);
        } finally {
            // Check that the command injection file was NOT created
            $this->assertFileDoesNotExist($injectionFile);

            if (file_exists($tempPath)) {
                unlink($tempPath);
            }
        }
    }

    public function testClearMetadataEnsuresResource(): void
    {
        $this->expectException(ImageException::class);
        $this->handler->clearMetadata();
    }

    public function testClearMetadataReturnsSelf(): void
    {
        $this->handler->withFile($this->path);

        $result = $this->handler->clearMetadata();

        $this->assertSame($this->handler, $result);
    }

    public function testClearMetadata(): void
    {
        $this->handler->withFile($this->origin . 'Steveston_dusk.JPG');
        /** @var Imagick $imagick */
        $imagick = $this->handler->getResource();
        $before  = $imagick->getImageProperties();

        $this->assertGreaterThan(40, count($before));

        $this->handler
            ->clearMetadata()
            ->save($this->root . 'exif-info-no-metadata.jpg');

        $this->handler->withFile($this->root . 'exif-info-no-metadata.jpg');
        /** @var Imagick $imagick */
        $imagick = $this->handler->getResource();
        $after   = $imagick->getImageProperties();

        $this->assertLessThanOrEqual(5, count($after));
    }
}
