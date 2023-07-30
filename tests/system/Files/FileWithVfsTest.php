<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Files;

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @internal
 *
 * @group Others
 */
final class FileWithVfsTest extends CIUnitTestCase
{
    // For VFS stuff
    private ?vfsStreamDirectory $root = null;
    private string $path;
    private string $start;
    private File $file;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->path = '_support/Files/';
        vfsStream::copyFromFileSystem(TESTPATH . $this->path, $this->root);
        $this->start = $this->root->url() . '/';
        $this->file  = new File($this->start . 'able/apple.php');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->root = null;
    }

    public function testDestinationUnknown(): void
    {
        $destination = $this->start . 'charlie/cherry.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationSameFileSameFolder(): void
    {
        $destination = $this->start . 'able/apple.php';
        $this->assertSame($this->start . 'able/apple_1.php', $this->file->getDestination($destination));
    }

    public function testDestinationSameFileDifferentFolder(): void
    {
        $destination = $this->start . 'baker/apple.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationDifferentFileSameFolder(): void
    {
        $destination = $this->start . 'able/date.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationDifferentFileDifferentFolder(): void
    {
        $destination = $this->start . 'baker/date.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationExistingFileDifferentFolder(): void
    {
        $destination = $this->start . 'baker/banana.php';
        $this->assertSame($this->start . 'baker/banana_1.php', $this->file->getDestination($destination));
    }

    public function testDestinationDelimited(): void
    {
        $destination = $this->start . 'able/fig_3.php';
        $this->assertSame($this->start . 'able/fig_4.php', $this->file->getDestination($destination));
    }

    public function testDestinationDelimitedAlpha(): void
    {
        $destination = $this->start . 'able/prune_ripe.php';
        $this->assertSame($this->start . 'able/prune_ripe_1.php', $this->file->getDestination($destination));
    }

    public function testMoveNormal(): void
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination);
        $this->assertTrue($this->root->hasChild('baker/apple.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveRename(): void
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'popcorn.php');
        $this->assertTrue($this->root->hasChild('baker/popcorn.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveOverwrite(): void
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'banana.php', true);
        $this->assertTrue($this->root->hasChild('baker/banana.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveDontOverwrite(): void
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'banana.php');
        $this->assertTrue($this->root->hasChild('baker/banana_1.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveFailure(): void
    {
        $this->expectException('Exception');

        $here = $this->root->url();

        chmod($here, 400); // make a read-only folder
        $destination = $here . '/charlie';
        $this->file->move($destination); // try to move our file there
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1782
     */
    public function testMoveReturnsNewInstance(): void
    {
        $destination = $this->start . 'baker';
        $file        = $this->file->move($destination);

        $this->assertTrue($this->root->hasChild('baker/apple.php'));
        $this->assertInstanceOf(File::class, $file);
        $this->assertSame($destination . '/apple.php', $file->getPathname());
    }
}
