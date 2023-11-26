<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Files;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;

/**
 * @internal
 *
 * @group Others
 */
final class FileMovingTest extends CIUnitTestCase
{
    private ?vfsStreamDirectory $root;
    private string $path;
    private string $start;
    private string $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $this->path = '_support/Files/';
        vfsStream::copyFromFileSystem(TESTPATH . $this->path, $this->root);
        $this->start = $this->root->url() . '/';

        $this->destination = $this->start . 'destination';
        if (is_dir($this->destination)) {
            rmdir($this->destination);
        }

        $_FILES = [];

        // Set the mock's return value to true
        move_uploaded_file('', '', true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->root = null;

        // cleanup folder being left behind (why?)
        $leftover = WRITEPATH . 'uploads/vfs:';
        if (is_dir($leftover)) {
            rrmdir($leftover);
        }
    }

    public function testMove(): void
    {
        $finalFilename = 'fileA';
        $_FILES        = [
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));
        $this->assertTrue($collection->hasFile('userfile2'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        foreach ($collection->all() as $file) {
            $this->assertInstanceOf(UploadedFile::class, $file);
            $file->move($destination, $file->getName(), false);
        }

        $this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '.txt'));
        $this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '_1.txt'));
    }

    public function testMoveOverwriting(): void
    {
        $finalFilename = 'file_with_delimiters_underscore';
        $_FILES        = [
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error'    => 0,
            ],
            'userfile3' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => '/tmp/fileC.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));
        $this->assertTrue($collection->hasFile('userfile2'));
        $this->assertTrue($collection->hasFile('userfile3'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        foreach ($collection->all() as $file) {
            $this->assertInstanceOf(UploadedFile::class, $file);
            $file->move($destination, $file->getName(), true);
        }

        $this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '.txt'));
        $this->assertFalse($this->root->hasChild('destination/' . $finalFilename . '_1.txt'));
        $this->assertFalse($this->root->hasChild('destination/' . $finalFilename . '_2.txt'));
        $this->assertFileExists($destination . '/' . $finalFilename . '.txt');
    }

    public function testMoved(): void
    {
        $finalFilename = 'fileA';
        $_FILES        = [
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $file = $collection->getFile('userfile1');

        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertFalse($file->hasMoved());

        $file->move($destination, $file->getName(), false);

        $this->assertTrue($file->hasMoved());
    }

    public function testStore(): void
    {
        $finalFilename = 'fileA';
        $_FILES        = [
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $file = $collection->getFile('userfile1');

        $this->assertInstanceOf(UploadedFile::class, $file);

        $path = $file->store($destination, $file->getName());

        $this->assertSame($destination . '/fileA.txt', $path);
    }

    public function testAlreadyMoved(): void
    {
        $finalFilename = 'fileA';
        $_FILES        = [
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $this->expectException(HTTPException::class);

        foreach ($collection->all() as $file) {
            $file->move($destination, $file->getName(), false);
            $file->move($destination, $file->getName(), false);
        }
    }

    public function testInvalidFile(): void
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => UPLOAD_ERR_INI_SIZE,
            ],
        ];

        $destination = $this->destination;
        $collection  = new FileCollection();

        $this->expectException(HTTPException::class);

        $file = $collection->getFile('userfile');
        $file->move($destination, $file->getName(), false);
    }

    public function testFailedMoveBecauseOfWarning(): void
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $destination = $this->destination;
        // Create the destination and make it read only
        if (! is_dir($destination)) {
            mkdir($destination, 0400, true);
        }

        $collection = new FileCollection();

        $this->expectException(HTTPException::class);

        $file = $collection->getFile('userfile');
        $file->move($destination, $file->getName(), false);
    }

    public function testFailedMoveBecauseOfFalseReturned(): void
    {
        $_FILES = [
            'userfile1' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));

        $destination = $this->destination;
        // Create the destination if not exists
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }
        // Set the mock's return value to false
        move_uploaded_file('', '', false);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('move_uploaded_file() returned false');

        $file = $collection->getFile('userfile1');
        $file->move($destination, $file->getName(), false);
    }
}

/*
 * Overwrite the function so that it will only check whether the file exists or not.
 * Original function also checks if the file was uploaded with a POST request.
 *
 * This overwrite is for testing the move operation.
 */

function is_uploaded_file($filename)
{
    if (! is_file($filename)) {
        file_put_contents($filename, 'data');
    }

    return is_file($filename);
}

/*
 * Overwrite the function so that it just copy without checking the file is an uploaded file.
 *
 * This overwrite is for testing the move operation.
 */

function move_uploaded_file($filename, $destination, ?bool $setReturnValue = null)
{
    static $return = true;

    if ($setReturnValue !== null) {
        $return = $setReturnValue;

        return true;
    }

    copy($filename, $destination);
    unlink($filename);

    return $return;
}

function rrmdir($src): void
{
    $dir = opendir($src);

    while (false !== $file = readdir($dir)) {
        if (($file !== '.') && ($file !== '..')) {
            $full = $src . '/' . $file;

            if (is_dir($full)) {
                rrmdir($full);
            } else {
                unlink($full);
            }
        }
    }

    closedir($dir);
    rmdir($src);
}
