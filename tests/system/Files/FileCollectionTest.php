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

use CodeIgniter\Files\Exceptions\FileException;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class FileCollectionTest extends CIUnitTestCase
{
    /**
     * A known, valid file
     */
    private string $file = SUPPORTPATH . 'Files/baker/banana.php';

    /**
     * A known, valid directory
     */
    private string $directory = SUPPORTPATH . 'Files/able/';

    /**
     * Initialize the helper, since some
     * tests call static methods before
     * the constructor would load it.
     */
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper(['filesystem']);
    }

    public function testResolveDirectoryDirectory()
    {
        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveDirectory');

        $this->assertSame($this->directory, $method($this->directory));
    }

    public function testResolveDirectoryFile()
    {
        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveDirectory');

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedDirectory', ['invokeArgs']));

        $method($this->file);
    }

    public function testResolveDirectorySymlink()
    {
        // Create a symlink to test
        $link = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(4));
        symlink($this->directory, $link);

        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveDirectory');

        $this->assertSame($this->directory, $method($link));

        unlink($link);
    }

    public function testResolveFileFile()
    {
        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveFile');

        $this->assertSame($this->file, $method($this->file));
    }

    public function testResolveFileSymlink()
    {
        // Create a symlink to test
        $link = sys_get_temp_dir() . DIRECTORY_SEPARATOR . bin2hex(random_bytes(4));
        symlink($this->file, $link);

        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveFile');

        $this->assertSame($this->file, $method($link));

        unlink($link);
    }

    public function testResolveFileDirectory()
    {
        $method = $this->getPrivateMethodInvoker(FileCollection::class, 'resolveFile');

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedFile', ['invokeArgs']));

        $method($this->directory);
    }

    public function testConstructorAddsFiles()
    {
        $expected = [
            $this->directory . 'apple.php',
            $this->file,
        ];

        $collection = new class ([$this->file]) extends FileCollection {
            protected $files = [
                SUPPORTPATH . 'Files/able/apple.php',
            ];
        };

        $this->assertSame($expected, $collection->get());
    }

    public function testConstructorCallsDefine()
    {
        $collection = new class () extends FileCollection {
            protected function define(): void
            {
                $this->add(SUPPORTPATH . 'Files/baker/banana.php');
            }
        };

        $this->assertSame([$this->file], $collection->get());
    }

    public function testAddStringFile()
    {
        $files = new FileCollection();

        $files->add(SUPPORTPATH . 'Files/baker/banana.php');

        $this->assertSame([$this->file], $files->get());
    }

    public function testAddStringFileRecursiveDoesNothing()
    {
        $files = new FileCollection();

        $files->add(SUPPORTPATH . 'Files/baker/banana.php', true);

        $this->assertSame([$this->file], $files->get());
    }

    public function testAddStringDirectory()
    {
        $files = new FileCollection();

        $expected = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
        ];

        $files->add(SUPPORTPATH . 'Files/able');

        $this->assertSame($expected, $files->get());
    }

    public function testAddStringDirectoryRecursive()
    {
        $files = new FileCollection();

        $expected = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $files->add(SUPPORTPATH . 'Files');

        $this->assertSame($expected, $files->get());
    }

    public function testAddArrayFiles()
    {
        $files = new FileCollection();

        $expected = [
            $this->directory . 'apple.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $files->add([
            $this->directory . 'apple.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ]);

        $this->assertSame($expected, $files->get());
    }

    public function testAddArrayDirectoryAndFile()
    {
        $files = new FileCollection();

        $expected = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $files->add([
            SUPPORTPATH . 'Files/able', // directory
            SUPPORTPATH . 'Files/baker/banana.php',
        ]);

        $this->assertSame($expected, $files->get());
    }

    public function testAddArrayRecursive()
    {
        $files = new FileCollection();

        $expected = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
            SUPPORTPATH . 'Log/Handlers/TestHandler.php',
        ];

        $files->add([
            SUPPORTPATH . 'Files',
            SUPPORTPATH . 'Log',
        ], true);

        $this->assertSame($expected, $files->get());
    }

    public function testAddFile()
    {
        $collection = new FileCollection();
        $this->assertSame([], $this->getPrivateProperty($collection, 'files'));

        $collection->addFile($this->file);
        $this->assertSame([$this->file], $this->getPrivateProperty($collection, 'files'));
    }

    public function testAddFileMissing()
    {
        $collection = new FileCollection();

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedFile', ['addFile']));

        $collection->addFile('TheHillsAreAlive.bmp');
    }

    public function testAddFileDirectory()
    {
        $collection = new FileCollection();

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedFile', ['addFile']));

        $collection->addFile($this->directory);
    }

    public function testAddFiles()
    {
        $collection = new FileCollection();
        $files      = [
            $this->file,
            $this->file,
        ];

        $collection->addFiles($files);
        $this->assertSame($files, $this->getPrivateProperty($collection, 'files'));
    }

    public function testGet()
    {
        $collection = new FileCollection();
        $collection->addFile($this->file);

        $this->assertSame([$this->file], $collection->get());
    }

    public function testGetSorts()
    {
        $collection = new FileCollection();
        $files      = [
            $this->file,
            $this->directory . 'apple.php',
        ];

        $collection->addFiles($files);

        $this->assertSame(array_reverse($files), $collection->get());
    }

    public function testGetUniques()
    {
        $collection = new FileCollection();
        $files      = [
            $this->file,
            $this->file,
        ];

        $collection->addFiles($files);
        $this->assertSame([$this->file], $collection->get());
    }

    public function testSet()
    {
        $collection = new FileCollection();

        $collection->set([$this->file]);
        $this->assertSame([$this->file], $collection->get());
    }

    public function testSetInvalid()
    {
        $collection = new FileCollection();

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedFile', ['addFile']));

        $collection->set(['flerb']);
    }

    public function testRemoveFile()
    {
        $collection = new FileCollection();
        $files      = [
            $this->file,
            $this->directory . 'apple.php',
        ];

        $collection->addFiles($files);

        $collection->removeFile($this->file);

        $this->assertSame([$this->directory . 'apple.php'], $collection->get());
    }

    public function testRemoveFiles()
    {
        $collection = new FileCollection();
        $files      = [
            $this->file,
            $this->directory . 'apple.php',
        ];

        $collection->addFiles($files);

        $collection->removeFiles($files);

        $this->assertSame([], $collection->get());
    }

    public function testAddDirectoryInvalid()
    {
        $collection = new FileCollection();

        $this->expectException(FileException::class);
        $this->expectExceptionMessage(lang('Files.expectedDirectory', ['addDirectory']));

        $collection->addDirectory($this->file);
    }

    public function testAddDirectory()
    {
        $collection = new FileCollection();
        $expected   = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
        ];

        $collection->addDirectory($this->directory);

        $this->assertSame($expected, $collection->get());
    }

    public function testAddDirectoryRecursive()
    {
        $collection = new FileCollection();
        $expected   = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $this->assertSame($expected, $collection->get());
    }

    public function testAddDirectories()
    {
        $collection = new FileCollection();
        $expected   = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->addDirectories([
            $this->directory,
            SUPPORTPATH . 'Files/baker',
        ]);

        $this->assertSame($expected, $collection->get());
    }

    public function testAddDirectoriesRecursive()
    {
        $collection = new FileCollection();
        $expected   = [
            $this->directory . 'apple.php',
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
            SUPPORTPATH . 'Files/baker/banana.php',
            SUPPORTPATH . 'Log/Handlers/TestHandler.php',
        ];

        $collection->addDirectories([
            SUPPORTPATH . 'Files',
            SUPPORTPATH . 'Log',
        ], true);

        $this->assertSame($expected, $collection->get());
    }

    public function testRemovePatternEmpty()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $files = $collection->get();

        $collection->removePattern('');

        $this->assertSame($files, $collection->get());
    }

    public function testRemovePatternRegex()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            $this->directory . 'apple.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->removePattern('#[a-z]+_.*#');

        $this->assertSame($expected, $collection->get());
    }

    public function testRemovePatternPseudo()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            $this->directory . 'apple.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->removePattern('*_*.php');

        $this->assertSame($expected, $collection->get());
    }

    public function testRemovePatternScope()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->removePattern('*.php', $this->directory);

        $this->assertSame($expected, $collection->get());
    }

    public function testRetainPatternEmpty()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $files = $collection->get();

        $collection->retainPattern('');

        $this->assertSame($files, $collection->get());
    }

    public function testRetainPatternRegex()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            $this->directory . 'fig_3.php',
            $this->directory . 'prune_ripe.php',
        ];

        $collection->retainPattern('#[a-z]+_.*#');

        $this->assertSame($expected, $collection->get());
    }

    public function testRetainPatternPseudo()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            $this->directory . 'fig_3.php',
        ];

        $collection->retainPattern('*_?.php');

        $this->assertSame($expected, $collection->get());
    }

    public function testRetainPatternScope()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $expected = [
            $this->directory . 'fig_3.php',
            SUPPORTPATH . 'Files/baker/banana.php',
        ];

        $collection->retainPattern('*_?.php', $this->directory);

        $this->assertSame($expected, $collection->get());
    }

    public function testCount()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $this->assertCount(4, $collection);
    }

    public function testIterable()
    {
        $collection = new FileCollection();
        $collection->addDirectory(SUPPORTPATH . 'Files', true);

        $count = 0;

        foreach ($collection as $file) {
            $this->assertInstanceOf(File::class, $file);
            $count++;
        }

        $this->assertSame($count, 4);
    }
}
