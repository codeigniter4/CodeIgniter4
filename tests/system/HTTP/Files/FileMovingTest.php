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

namespace CodeIgniter\HTTP\Files;

use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FileMovingTest extends CIUnitTestCase
{
    private ?vfsStreamDirectory $root;
    private string $destination;

    protected function setUp(): void
    {
        parent::setUp();

        $this->root = vfsStream::setup();
        $path       = '_support/Files/';
        vfsStream::copyFromFileSystem(TESTPATH . $path, $this->root);
        $start = $this->root->url() . '/';

        $this->destination = $start . 'destination';
        if (is_dir($this->destination)) {
            rmdir($this->destination);
        }

        service('superglobals')->setFilesArray([]);

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
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => sys_get_temp_dir() . '/fileB.txt',
                'error'    => 0,
            ],
        ]);

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
        $this->assertSame(0666 & ~umask(), $this->root->getChild('destination/' . $finalFilename . '.txt')->getPermissions());
    }

    public function testMoveSanitizesClientNameByDefault(): void
    {
        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => '../../public/shell.php',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile'));

        $destination = $this->destination;
        if (! is_dir($destination)) {
            mkdir($destination, 0777, true);
        }

        $file = $collection->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        // No second argument: the client-provided name must be sanitized.
        $file->move($destination);

        $this->assertSame('publicshell.php', $file->getName());
        $this->assertTrue($this->root->hasChild('destination/publicshell.php'));
        $this->assertFalse($this->root->hasChild('public/shell.php'));
    }

    /**
     * On Windows these names may resolve to a different file or an NTFS stream.
     */
    #[DataProvider('provideMoveRejectsUnsafeDefaultNameOnWindows')]
    public function testMoveRejectsUnsafeDefaultNameOnWindows(string $clientName): void
    {
        is_windows(true);

        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => $clientName,
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $file = (new FileCollection())->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        try {
            $file->move($this->destination);
            $this->fail('HTTPException was not thrown.');
        } catch (HTTPException $e) {
            $this->assertSame(lang('HTTP.invalidFileName'), $e->getMessage());
        }

        $this->assertFalse($file->hasMoved());
        $this->assertSame($clientName, $file->getName());
        $this->assertSame([], array_values(array_diff(scandir($this->destination), ['.', '..', 'index.html'])));
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideMoveRejectsUnsafeDefaultNameOnWindows(): iterable
    {
        yield 'single period' => ['shell.php.'];

        yield 'double period' => ['shell.php..'];

        yield 'triple period' => ['shell.php...'];

        yield 'space then periods' => ['shell.php ..'];

        yield 'period then space' => ['shell.php. '];

        yield 'trailing space' => ['shell.php '];

        yield 'periods only' => ['..'];

        yield 'NTFS stream' => ['shell.php:stream'];

        yield 'NTFS stream in text file' => ['notes.txt:metadata'];
    }

    #[DataProvider('provideMoveKeepsWindowsUnsafeDefaultNameOutsideWindows')]
    public function testMoveKeepsWindowsUnsafeDefaultNameOutsideWindows(string $clientName): void
    {
        is_windows(false);

        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => $clientName,
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $file = (new FileCollection())->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertTrue($file->move($this->destination));
        $this->assertSame($clientName, $file->getName());
        $this->assertTrue($this->root->hasChild('destination/' . $clientName));
    }

    /**
     * A trailing space is not covered here: vfsStream trims trailing whitespace from paths.
     *
     * @return iterable<string, array{string}>
     */
    public static function provideMoveKeepsWindowsUnsafeDefaultNameOutsideWindows(): iterable
    {
        yield 'trailing periods' => ['notes.txt..'];

        yield 'colon' => ['meeting 10:30.txt'];
    }

    public function testMoveAcceptsDefaultNameWithInnerPeriodsAndSpaces(): void
    {
        is_windows(true);

        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => 'my file.v1..final.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $file = (new FileCollection())->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertTrue($file->move($this->destination));
        $this->assertSame('my file.v1..final.txt', $file->getName());
        $this->assertTrue($this->root->hasChild('destination/my file.v1..final.txt'));
    }

    public function testMoveDoesNotRejectCallerSuppliedName(): void
    {
        is_windows(true);

        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => 'shell.php..',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $file = (new FileCollection())->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertTrue($file->move($this->destination, 'avatar.txt'));
        $this->assertSame('avatar.txt', $file->getName());
    }

    public function testMoveOverwriting(): void
    {
        $finalFilename = 'file_with_delimiters_underscore';
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => sys_get_temp_dir() . '/fileB.txt',
                'error'    => 0,
            ],
            'userfile3' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => sys_get_temp_dir() . '/fileC.txt',
                'error'    => 0,
            ],
        ]);

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
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

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
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));

        $file = $collection->getFile('userfile1');

        $this->assertInstanceOf(UploadedFile::class, $file);

        $folderName = 'file-moving-test-' . bin2hex(random_bytes(4));
        $directory  = WRITEPATH . 'uploads/' . $folderName;

        try {
            $path = $file->store($folderName, $file->getName());

            $this->assertSame($folderName . '/fileA.txt', $path);
            $this->assertFileExists($directory . '/fileA.txt');
        } finally {
            if (is_file($directory . '/fileA.txt')) {
                unlink($directory . '/fileA.txt');
            }
            if (is_file($directory . '/index.html')) {
                unlink($directory . '/index.html');
            }
            if (is_dir($directory)) {
                rmdir($directory);
            }
        }
    }

    public function testAlreadyMoved(): void
    {
        $finalFilename = 'fileA';
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => $finalFilename . '.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

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
        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/myTempFile.txt',
                'error'    => UPLOAD_ERR_INI_SIZE,
            ],
        ]);

        $destination = $this->destination;
        $collection  = new FileCollection();

        $this->expectException(HTTPException::class);

        $file = $collection->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $file->move($destination, $file->getName(), false);
    }

    public function testFailedMoveBecauseOfWarning(): void
    {
        service('superglobals')->setFilesArray([
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/myTempFile.txt',
                'error'    => 0,
            ],
        ]);

        $destination = $this->destination;
        // Create the destination and make it read only
        if (! is_dir($destination)) {
            mkdir($destination, 0400, true);
        }

        $collection = new FileCollection();

        $this->expectException(HTTPException::class);

        $file = $collection->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $file->move($destination, $file->getName(), false);
    }

    public function testFailedMoveBecauseOfFalseReturned(): void
    {
        service('superglobals')->setFilesArray([
            'userfile1' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => sys_get_temp_dir() . '/fileA.txt',
                'error'    => 0,
            ],
        ]);

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
        $this->assertInstanceOf(UploadedFile::class, $file);
        $file->move($destination, $file->getName(), false);
    }
}

/*
 * Overwrite the function so that it will only check whether the file exists or not.
 * Original function also checks if the file was uploaded with a POST request.
 *
 * This overwrite is for testing the move operation.
 */
function is_uploaded_file(string $filename): bool
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
function move_uploaded_file(string $filename, string $destination, ?bool $setReturnValue = null): bool
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

function rrmdir(string $src): void
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
