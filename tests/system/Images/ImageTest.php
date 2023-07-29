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

use CodeIgniter\Images\Exceptions\ImageException;
use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @internal
 *
 * @group Others
 */
final class ImageTest extends CIUnitTestCase
{
    private vfsStreamDirectory $root;
    private string $origin;
    private string $start;
    private Image $image;

    protected function setUp(): void
    {
        // create virtual file system
        $this->root = vfsStream::setup();
        // copy our support files
        $this->origin = '_support/Images/';
        vfsStream::copyFromFileSystem(TESTPATH . $this->origin, $this->root);
        // make subfolders
        $structure = [
            'work'     => [],
            'wontwork' => [],
        ];
        vfsStream::create($structure);
        // with one of them read only
        $this->root->getChild('wontwork')->chmod(0400);

        $this->start = $this->root->url() . '/';

        $this->image = new Image($this->start . 'ci-logo.png');
    }

    public function testBasicPropertiesInherited(): void
    {
        $this->assertSame('ci-logo.png', $this->image->getFilename());
        $this->assertSame($this->start . 'ci-logo.png', $this->image->getPathname());
        $this->assertSame($this->root->url(), $this->image->getPath());
        $this->assertSame('ci-logo.png', $this->image->getBasename());
    }

    public function testGetProperties(): void
    {
        $expected = [
            'width'      => 155,
            'height'     => 200,
            'image_type' => IMAGETYPE_PNG,
            'size_str'   => 'width="155" height="200"',
            'mime_type'  => 'image/png',
        ];

        $this->assertSame($expected, $this->image->getProperties(true));
    }

    public function testExtractProperties(): void
    {
        // extract properties from the image
        $this->assertTrue($this->image->getProperties(false));

        $this->assertSame(155, $this->image->origWidth);
        $this->assertSame(200, $this->image->origHeight);
        $this->assertSame(IMAGETYPE_PNG, $this->image->imageType);
        $this->assertSame('width="155" height="200"', $this->image->sizeStr);
        $this->assertSame('image/png', $this->image->mime);
    }

    public function testCopyDefaultName(): void
    {
        $targetPath = $this->start . 'work';
        $this->image->copy($targetPath);
        $this->assertTrue($this->root->hasChild('work/ci-logo.png'));
    }

    public function testCopyNewName(): void
    {
        $this->image->copy($this->root->url(), 'new-logo.png');
        $this->assertTrue($this->root->hasChild('new-logo.png'));
    }

    public function testCopyNewFolder(): void
    {
        $targetPath = $this->start . 'work/subfolder';
        $this->image->copy($targetPath, 'new-logo.png');
        $this->assertTrue($this->root->hasChild('work/subfolder/new-logo.png'));
    }

    public function testCopyNowhere(): void
    {
        $this->expectException(ImageException::class);
        $targetPath = $this->start . 'work';
        $this->image->copy($targetPath, '');
    }
}
