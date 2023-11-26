<?php

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
use CodeIgniter\Files\Exceptions\FileNotFoundException;
use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Images\Handlers\BaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * Test the common image processing functionality.
 *
 * Note: some of the underlying PHP functions do not play nicely
 * with vfsStream, so the support files are used directly for
 * most work, and the virtual file system will be used for
 * testing saving only.
 *
 * @internal
 *
 * @group Others
 */
final class BaseHandlerTest extends CIUnitTestCase
{
    private vfsStreamDirectory $root;
    private string $origin;
    private string $start;
    private string $path;

    protected function setUp(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('The GD extension is not available.');
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
        $this->root->getChild('wontwork')->chmod(0400);

        // for VFS tests
        $this->start = $this->root->url() . '/';
        $this->path  = $this->start . 'ci-logo.png';
    }

    public function testNew(): void
    {
        $handler = Services::image('gd', null, false);
        $this->assertInstanceOf(BaseHandler::class, $handler);
    }

    public function testWithFile(): void
    {
        $path    = $this->origin . 'ci-logo.png';
        $handler = Services::image('gd', null, false);
        $handler->withFile($path);

        $image = $handler->getFile();
        $this->assertInstanceOf(Image::class, $image);
        $this->assertSame(155, $image->origWidth);
        $this->assertSame($path, $image->getPathname());
    }

    public function testMissingFile(): void
    {
        $this->expectException(FileNotFoundException::class);
        $handler = Services::image('gd', null, false);
        $handler->withFile($this->start . 'No_such_file.jpg');
    }

    public function testNonImageFile(): void
    {
        $this->expectException(ImageException::class);
        $handler = Services::image('gd', null, false);
        $handler->withFile(SUPPORTPATH . 'Files/baker/banana.php');

        // Make any call that accesses the image
        $handler->resize(100, 100);
    }

    public function testForgotWithFile(): void
    {
        $this->expectException(ImageException::class);
        $handler = Services::image('gd', null, false);

        // Make any call that accesses the image
        $handler->resize(100, 100);
    }

    public function testFileTypes(): void
    {
        $handler = Services::image('gd', null, false);
        $handler->withFile($this->start . 'ci-logo.png');
        $image = $handler->getFile();
        $this->assertInstanceOf(Image::class, $image);

        $handler->withFile($this->start . 'ci-logo.jpeg');
        $image = $handler->getFile();
        $this->assertInstanceOf(Image::class, $image);

        $handler->withFile($this->start . 'ci-logo.gif');
        $image = $handler->getFile();
        $this->assertInstanceOf(Image::class, $image);
    }

    // Something handled by our Image
    public function testImageHandled(): void
    {
        $handler = Services::image('gd', null, false);
        $handler->withFile($this->path);
        $this->assertSame($this->path, $handler->getPathname());
    }
}
