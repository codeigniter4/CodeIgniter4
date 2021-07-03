<?php

namespace CodeIgniter\Files;

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;

/**
 * @internal
 */
final class FileWithVfsTest extends CIUnitTestCase
{
    // For VFS stuff
    protected $root;
    protected $path;
    protected $start;

    /**
     * @var File
     */
    protected $file;

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

    public function testDestinationUnknown()
    {
        $destination = $this->start . 'charlie/cherry.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationSameFileSameFolder()
    {
        $destination = $this->start . 'able/apple.php';
        $this->assertSame($this->start . 'able/apple_1.php', $this->file->getDestination($destination));
    }

    public function testDestinationSameFileDifferentFolder()
    {
        $destination = $this->start . 'baker/apple.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationDifferentFileSameFolder()
    {
        $destination = $this->start . 'able/date.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationDifferentFileDifferentFolder()
    {
        $destination = $this->start . 'baker/date.php';
        $this->assertSame($destination, $this->file->getDestination($destination));
    }

    public function testDestinationExistingFileDifferentFolder()
    {
        $destination = $this->start . 'baker/banana.php';
        $this->assertSame($this->start . 'baker/banana_1.php', $this->file->getDestination($destination));
    }

    public function testDestinationDelimited()
    {
        $destination = $this->start . 'able/fig_3.php';
        $this->assertSame($this->start . 'able/fig_4.php', $this->file->getDestination($destination));
    }

    public function testDestinationDelimitedAlpha()
    {
        $destination = $this->start . 'able/prune_ripe.php';
        $this->assertSame($this->start . 'able/prune_ripe_1.php', $this->file->getDestination($destination));
    }

    public function testMoveNormal()
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination);
        $this->assertTrue($this->root->hasChild('baker/apple.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveRename()
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'popcorn.php');
        $this->assertTrue($this->root->hasChild('baker/popcorn.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveOverwrite()
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'banana.php', true);
        $this->assertTrue($this->root->hasChild('baker/banana.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveDontOverwrite()
    {
        $destination = $this->start . 'baker';
        $this->file->move($destination, 'banana.php');
        $this->assertTrue($this->root->hasChild('baker/banana_1.php'));
        $this->assertFalse($this->root->hasChild('able/apple.php'));
    }

    public function testMoveFailure()
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
    public function testMoveReturnsNewInstance()
    {
        $destination = $this->start . 'baker';
        $file        = $this->file->move($destination);

        $this->assertTrue($this->root->hasChild('baker/apple.php'));
        $this->assertInstanceOf(File::class, $file);
        $this->assertSame($destination . '/apple.php', $file->getPathname());
    }
}
