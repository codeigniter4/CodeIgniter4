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

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\visitor\vfsStreamStructureVisitor;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FilesystemHelperTest extends CIUnitTestCase
{
    /**
     * @var array<string, array<string, list<mixed>>>|array<string, array<string, string>>|array<string, list<mixed>>|array<string, mixed>|array<string, string>
     */
    private array $structure;

    protected function setUp(): void
    {
        parent::setUp();

        $this->structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary',
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door',
            ],
            'AnEmptyFolder' => [],
            'simpleFile'    => 'A tap-tap-tapping upon my door',
            '.hidden'       => 'There is no spoon',
        ];

        helper('filesystem');
    }

    public function testDirectoryMapDefaults(): void
    {
        $this->assertTrue(function_exists('directory_map'));

        $expected = [
            'foo' . DIRECTORY_SEPARATOR => [
                'bar',
                'baz',
            ],
            'boo' . DIRECTORY_SEPARATOR => [
                'far',
                'faz',
            ],
            'AnEmptyFolder' . DIRECTORY_SEPARATOR => [],
            'simpleFile',
        ];

        $root = vfsStream::setup('root', null, $this->structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertSame($expected, directory_map(vfsStream::url('root')));
    }

    public function testDirectoryMapShowsHiddenFiles(): void
    {
        $this->assertTrue(function_exists('directory_map'));

        $expected = [
            'foo' . DIRECTORY_SEPARATOR => [
                'bar',
                'baz',
            ],
            'boo' . DIRECTORY_SEPARATOR => [
                'far',
                'faz',
            ],
            'AnEmptyFolder' . DIRECTORY_SEPARATOR => [],
            'simpleFile',
            '.hidden',
        ];

        $root = vfsStream::setup('root', null, $this->structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertSame($expected, directory_map(vfsStream::url('root'), 0, true));
    }

    public function testDirectoryMapLimitsRecursion(): void
    {
        $this->assertTrue(function_exists('directory_map'));

        $expected = [
            'foo' . DIRECTORY_SEPARATOR,
            'boo' . DIRECTORY_SEPARATOR,
            'AnEmptyFolder' . DIRECTORY_SEPARATOR,
            'simpleFile',
            '.hidden',
        ];

        $root = vfsStream::setup('root', null, $this->structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertSame($expected, directory_map(vfsStream::url('root'), 1, true));
    }

    public function testDirectoryMapHandlesNotfound(): void
    {
        $this->assertSame([], directory_map(SUPPORTPATH . 'Files/shaker/'));
    }

    public function testDirectoryMirror(): void
    {
        $this->assertTrue(function_exists('directory_mirror'));

        // Create a subdirectory
        $this->structure['foo']['bam'] = ['zab' => 'A deep file'];

        vfsStream::setup('root', null, $this->structure);
        $root = rtrim(vfsStream::url('root') . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        directory_mirror($root . 'foo', $root . 'boo');

        $this->assertFileExists($root . 'boo/bar');
        $this->assertFileExists($root . 'boo/bam/zab');
    }

    public function testDirectoryMirrorOverwrites(): void
    {
        $this->assertTrue(function_exists('directory_mirror'));

        // Create duplicate files
        $this->structure['foo']['far'] = 'all your base';
        $this->structure['foo']['faz'] = 'are belong to us';

        vfsStream::setup('root', null, $this->structure);
        $root = rtrim(vfsStream::url('root') . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        directory_mirror($root . 'foo', $root . 'boo', true);
        $result = file_get_contents($root . 'boo/faz');

        $this->assertSame($this->structure['foo']['faz'], $result);
    }

    public function testDirectoryMirrorNotOverwrites(): void
    {
        $this->assertTrue(function_exists('directory_mirror'));

        // Create duplicate files
        $this->structure['foo']['far'] = 'all your base';
        $this->structure['foo']['faz'] = 'are belong to us';

        vfsStream::setup('root', null, $this->structure);
        $root = rtrim(vfsStream::url('root') . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        directory_mirror($root . 'foo', $root . 'boo', false);
        $result = file_get_contents($root . 'boo/faz');

        $this->assertSame($this->structure['boo']['faz'], $result);
    }

    public function testDirectoryMirrorSkipExistingFolder(): void
    {
        $this->assertTrue(function_exists('directory_mirror'));

        $this->structure = [
            'src' => [
                'AnEmptyFolder' => [],
            ],
            'dest' => [
                'AnEmptyFolder' => [],
            ],
        ];
        vfsStream::setup('root', null, $this->structure);
        $root = rtrim(vfsStream::url('root') . DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // skips the existing folder
        directory_mirror($root . 'src', $root . 'dest');

        $structure = vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure();
        $this->assertSame([], $structure['root']['dest']['AnEmptyFolder']);

        // skips the existing folder (the same as overwrite = true)
        directory_mirror($root . 'src', $root . 'dest', false);

        $structure = vfsStream::inspect(new vfsStreamStructureVisitor())->getStructure();
        $this->assertSame([], $structure['root']['dest']['AnEmptyFolder']);
    }

    public function testWriteFileSuccess(): void
    {
        $vfs = vfsStream::setup('root');

        $this->assertTrue(write_file(vfsStream::url('root/test.php'), 'Simple'));
        $this->assertFileExists($vfs->getChild('test.php')->url());
    }

    public function testWriteFileFailure(): void
    {
        vfsStream::setup('root');

        $this->assertFalse(write_file(vfsStream::url('apple#test.php'), 'Simple'));
    }

    public function testDeleteFilesDefaultsToOneLevelDeep(): void
    {
        $this->assertTrue(function_exists('delete_files'));

        $vfs = vfsStream::setup('root', null, $this->structure);

        delete_files(vfsStream::url('root'));

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertTrue($vfs->hasChild('.hidden'));
        $this->assertTrue($vfs->hasChild('foo'));
        $this->assertTrue($vfs->hasChild('boo'));
        $this->assertTrue($vfs->hasChild('AnEmptyFolder'));
    }

    public function testDeleteFilesHandlesRecursion(): void
    {
        $this->assertTrue(function_exists('delete_files'));

        $vfs = vfsStream::setup('root', null, $this->structure);

        delete_files(vfsStream::url('root'), true);

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertTrue($vfs->hasChild('.hidden'));
        $this->assertFalse($vfs->hasChild('foo'));
        $this->assertFalse($vfs->hasChild('boo'));
        $this->assertFalse($vfs->hasChild('AnEmptyFolder'));
    }

    public function testDeleteFilesLeavesHTFiles(): void
    {
        $structure = array_merge($this->structure, [
            '.htaccess'  => 'Deny All',
            'index.html' => 'foo',
            'index.php'  => 'blah',
        ]);

        $vfs = vfsStream::setup('root', null, $structure);

        delete_files(vfsStream::url('root'), true, true);

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertTrue($vfs->hasChild('.hidden'));
        $this->assertFalse($vfs->hasChild('foo'));
        $this->assertFalse($vfs->hasChild('boo'));
        $this->assertFalse($vfs->hasChild('AnEmptyFolder'));
        $this->assertTrue($vfs->hasChild('.htaccess'));
        $this->assertTrue($vfs->hasChild('index.html'));
        $this->assertTrue($vfs->hasChild('index.php'));
    }

    public function testDeleteFilesIncludingHidden(): void
    {
        $structure = array_merge($this->structure, [
            '.htaccess'  => 'Deny All',
            'index.html' => 'foo',
            'index.php'  => 'blah',
        ]);

        $vfs = vfsStream::setup('root', null, $structure);

        delete_files(vfsStream::url('root'), true, true, true);

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertFalse($vfs->hasChild('.hidden'));
        $this->assertFalse($vfs->hasChild('foo'));
        $this->assertFalse($vfs->hasChild('boo'));
        $this->assertFalse($vfs->hasChild('AnEmptyFolder'));
        $this->assertTrue($vfs->hasChild('.htaccess'));
        $this->assertTrue($vfs->hasChild('index.html'));
        $this->assertTrue($vfs->hasChild('index.php'));
    }

    public function testDeleteFilesFailure(): void
    {
        $this->assertFalse(delete_files(SUPPORTPATH . 'Files/shaker/'));
    }

    public function testGetFilenames(): void
    {
        $this->assertTrue(function_exists('get_filenames'));

        // Not sure the directory names should actually show up
        // here but this matches v3.x results.
        $expected = [
            'AnEmptyFolder',
            'bar',
            'baz',
            'boo',
            'far',
            'faz',
            'foo',
            'simpleFile',
        ];

        $vfs = vfsStream::setup('root', null, $this->structure);

        $this->assertSame($expected, get_filenames($vfs->url(), false));
    }

    public function testGetFilenamesWithoutDirectories(): void
    {
        $vfs = vfsStream::setup('root', null, $this->structure);

        $filenames = get_filenames($vfs->url(), true, false, false);

        $expected = [
            'vfs://root/boo/far',
            'vfs://root/boo/faz',
            'vfs://root/foo/bar',
            'vfs://root/foo/baz',
            'vfs://root/simpleFile',
        ];
        $this->assertSame($expected, $filenames);
    }

    public function testGetFilenamesWithHidden(): void
    {
        $this->assertTrue(function_exists('get_filenames'));

        // Not sure the directory names should actually show up
        // here but this matches v3.x results.
        $expected = [
            '.hidden',
            'AnEmptyFolder',
            'bar',
            'baz',
            'boo',
            'far',
            'faz',
            'foo',
            'simpleFile',
        ];

        $vfs = vfsStream::setup('root', null, $this->structure);

        $this->assertSame($expected, get_filenames($vfs->url(), false, true));
    }

    public function testGetFilenamesWithRelativeSource(): void
    {
        $this->assertTrue(function_exists('get_filenames'));

        $expected = [
            'AnEmptyFolder',
            'boo',
            'boo/far',
            'boo/faz',
            'foo',
            'foo/bar',
            'foo/baz',
            'simpleFile',
        ];

        $vfs = vfsStream::setup('root', null, $this->structure);

        $this->assertSame($expected, get_filenames($vfs->url(), null));
    }

    public function testGetFilenamesWithFullSource(): void
    {
        $this->assertTrue(function_exists('get_filenames'));

        $vfs = vfsStream::setup('root', null, $this->structure);

        $expected = [
            $vfs->url() . DIRECTORY_SEPARATOR . 'AnEmptyFolder',
            $vfs->url() . DIRECTORY_SEPARATOR . 'boo',
            $vfs->url() . DIRECTORY_SEPARATOR . 'boo/far',
            $vfs->url() . DIRECTORY_SEPARATOR . 'boo/faz',
            $vfs->url() . DIRECTORY_SEPARATOR . 'foo',
            $vfs->url() . DIRECTORY_SEPARATOR . 'foo/bar',
            $vfs->url() . DIRECTORY_SEPARATOR . 'foo/baz',
            $vfs->url() . DIRECTORY_SEPARATOR . 'simpleFile',
        ];

        $this->assertSame($expected, get_filenames($vfs->url(), true));
    }

    public function testGetFilenamesFailure(): void
    {
        $this->assertSame([], get_filenames(SUPPORTPATH . 'Files/shaker/'));
    }

    public function testGetFilenamesWithSymlinks(): void
    {
        $targetDir = APPPATH . 'Language';
        $linkDir   = APPPATH . 'Controllers/Language';
        if (file_exists($linkDir)) {
            unlink($linkDir);
        }
        symlink($targetDir, $linkDir);

        $targetFile = APPPATH . 'Common.php';
        $linkFile   = APPPATH . 'Controllers/Common.php';
        if (file_exists($linkFile)) {
            unlink($linkFile);
        }
        symlink($targetFile, $linkFile);

        $this->assertSame([
            0 => 'BaseController.php',
            1 => 'Common.php',
            2 => 'Home.php',
            3 => 'Language',
            4 => 'Validation.php',
            5 => 'en',
        ], get_filenames(APPPATH . 'Controllers'));

        unlink($linkDir);
        unlink($linkFile);
    }

    public function testGetDirFileInfo(): void
    {
        $file1 = SUPPORTPATH . 'Files/baker/banana.php';
        $info1 = get_file_info($file1);
        $file2 = SUPPORTPATH . 'Files/baker/fig_3.php.txt';
        $info2 = get_file_info($file2);

        $expected = [
            'banana.php' => [
                'name'          => 'banana.php',
                'server_path'   => $file1,
                'size'          => $info1['size'],
                'date'          => $info1['date'],
                'relative_path' => realpath(__DIR__ . '/../../_support/Files/baker'),
            ],
            'fig_3.php.txt' => [
                'name'          => 'fig_3.php.txt',
                'server_path'   => $file2,
                'size'          => $info2['size'],
                'date'          => $info2['date'],
                'relative_path' => realpath(__DIR__ . '/../../_support/Files/baker'),
            ],
        ];

        $result = get_dir_file_info(SUPPORTPATH . 'Files/baker');
        ksort($result);

        $this->assertSame($expected, $result);
    }

    public function testGetDirFileInfoNested(): void
    {
        $expected = [
            'banana.php',
            'prune_ripe.php',
            'fig_3.php',
            'apple.php',
        ];

        $results = get_dir_file_info(SUPPORTPATH . 'Files', false);
        $this->assertEmpty(array_diff($expected, array_keys($results)));
    }

    public function testGetDirFileInfoFailure(): void
    {
        $expected = [];

        $this->assertSame($expected, get_dir_file_info(SUPPORTPATH . 'Files#baker'));
    }

    public function testGetFileInfo(): void
    {
        $file = SUPPORTPATH . 'Files/baker/banana.php';
        $info = get_file_info($file);

        $expected = [
            'name'        => 'banana.php',
            'server_path' => $file,
            'size'        => $info['size'],
            'date'        => $info['date'],
        ];

        $this->assertSame($expected, get_file_info(SUPPORTPATH . 'Files/baker/banana.php'));
    }

    public function testGetFileInfoCustom(): void
    {
        $expected = [
            'readable'   => true,
            'writable'   => true,
            'executable' => false,
        ];

        $this->assertSame($expected, get_file_info(SUPPORTPATH . 'Files/baker/banana.php', 'readable,writable,executable'));
    }

    public function testGetFileInfoPerms(): void
    {
        $file     = SUPPORTPATH . 'Files/baker/banana.php';
        $expected = 0664;
        chmod($file, $expected);

        $stuff = get_file_info($file, 'fileperms');

        $this->assertSame($expected, $stuff['fileperms'] & 0777);
    }

    public function testGetFileNotThereInfo(): void
    {
        $expected = null;

        $this->assertSame($expected, get_file_info(SUPPORTPATH . 'Files/icer'));
    }

    public function testSameFileSame(): void
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Files/able/apple.php';

        $this->assertTrue(same_file($file1, $file2));
    }

    public function testSameFileIdentical(): void
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Files/baker/banana.php';

        $this->assertTrue(same_file($file1, $file2));
    }

    public function testSameFileDifferent(): void
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/ci-logo.gif';

        $this->assertFalse(same_file($file1, $file2));
    }

    public function testSameFileOrder(): void
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/ci-logo.gif';

        $this->assertFalse(same_file($file2, $file1));
    }

    public function testSameFileDirectory(): void
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/';

        $this->assertFalse(same_file($file1, $file2));
    }

    public function testOctalPermissions(): void
    {
        $this->assertSame('777', octal_permissions(0777));
        $this->assertSame('655', octal_permissions(0655));
        $this->assertSame('123', octal_permissions(0123));
    }

    public function testSymbolicPermissions(): void
    {
        $expected = [
            0777    => 'urwxrwxrwx',
            0655    => 'urw-r-xr-x',
            0123    => 'u--x-w--wx',
            010655  => 'prw-r-xr-x',
            020655  => 'crw-r-xr-x',
            040655  => 'drw-r-xr-x',
            060655  => 'brw-r-xr-x',
            0100655 => '-rw-r-xr-x',
            0120655 => 'lrw-r-xr-x',
            0140655 => 'srw-r-xr-x',
        ];

        foreach ($expected as $perm => $value) {
            $this->assertSame($value, symbolic_permissions($perm));
        }
    }

    public function testRealPathURL(): void
    {
        $this->expectException('InvalidArgumentException');
        set_realpath('http://somewhere.com/overtherainbow');
    }

    public function testRealPathInvalid(): void
    {
        $this->expectException('InvalidArgumentException');
        set_realpath(SUPPORTPATH . 'root/../', true);
    }

    public function testRealPathResolved(): void
    {
        $this->assertSame(SUPPORTPATH . 'Models/', set_realpath(SUPPORTPATH . 'Files/../Models', true));
    }
}
