<?php

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use InvalidArgumentException;
use org\bovigo\vfs\vfsStream;

/**
 * @internal
 */
final class FilesystemHelperTest extends CIUnitTestCase
{
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
    }

    //--------------------------------------------------------------------

    public function testDirectoryMapDefaults()
    {
        helper('filesystem');
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

    public function testDirectoryMapShowsHiddenFiles()
    {
        helper('filesystem');
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

        $this->assertSame($expected, directory_map(vfsStream::url('root'), false, true));
    }

    public function testDirectoryMapLimitsRecursion()
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

    public function testDirectoryMapHandlesNotfound()
    {
        $this->assertSame([], directory_map(SUPPORTPATH . 'Files/shaker/'));
    }

    //--------------------------------------------------------------------

    public function testDirectoryMirror()
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

    public function testDirectoryMirrorOverwrites()
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

    public function testDirectoryMirrorNotOverwrites()
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

    //--------------------------------------------------------------------

    public function testWriteFileSuccess()
    {
        $vfs = vfsStream::setup('root');

        $this->assertTrue(write_file(vfsStream::url('root/test.php'), 'Simple'));
        $this->assertFileExists($vfs->getChild('test.php')->url());
    }

    public function testWriteFileFailure()
    {
        vfsStream::setup('root');

        $this->assertFalse(write_file(vfsStream::url('apple#test.php'), 'Simple'));
    }

    //--------------------------------------------------------------------

    public function testDeleteFilesDefaultsToOneLevelDeep()
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

    public function testDeleteFilesHandlesRecursion()
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

    public function testDeleteFilesLeavesHTFiles()
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

    public function testDeleteFilesIncludingHidden()
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

    public function testDeleteFilesFailure()
    {
        $this->assertFalse(delete_files(SUPPORTPATH . 'Files/shaker/'));
    }

    //--------------------------------------------------------------------

    public function testGetFilenames()
    {
        $this->assertTrue(function_exists('delete_files'));

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

    public function testGetFilenamesWithHidden()
    {
        $this->assertTrue(function_exists('delete_files'));

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

    public function testGetFilenamesWithRelativeSource()
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

    public function testGetFilenamesWithFullSource()
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

    public function testGetFilenamesFailure()
    {
        $this->assertSame([], get_filenames(SUPPORTPATH . 'Files/shaker/'));
    }

    //--------------------------------------------------------------------

    public function testGetDirFileInfo()
    {
        $file = SUPPORTPATH . 'Files/baker/banana.php';
        $info = get_file_info($file);

        $expected = [
            'banana.php' => [
                'name'          => 'banana.php',
                'server_path'   => $file,
                'size'          => $info['size'],
                'date'          => $info['date'],
                'relative_path' => realpath(__DIR__ . '/../../_support/Files/baker'),
            ],
        ];

        $this->assertSame($expected, get_dir_file_info(SUPPORTPATH . 'Files/baker'));
    }

    public function testGetDirFileInfoNested()
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

    public function testGetDirFileInfoFailure()
    {
        $expected = [];

        $this->assertSame($expected, get_dir_file_info(SUPPORTPATH . 'Files#baker'));
    }

    //--------------------------------------------------------------------

    public function testGetFileInfo()
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

    public function testGetFileInfoCustom()
    {
        $expected = [
            'readable'   => true,
            'writable'   => true,
            'executable' => false,
        ];

        $this->assertSame($expected, get_file_info(SUPPORTPATH . 'Files/baker/banana.php', 'readable,writable,executable'));
    }

    public function testGetFileInfoPerms()
    {
        $file     = SUPPORTPATH . 'Files/baker/banana.php';
        $expected = 0664;
        chmod($file, $expected);

        $stuff = get_file_info($file, 'fileperms');

        $this->assertSame($expected, $stuff['fileperms'] & 0777);
    }

    public function testGetFileNotThereInfo()
    {
        $expected = null;

        $this->assertSame($expected, get_file_info(SUPPORTPATH . 'Files/icer'));
    }

    //--------------------------------------------------------------------

    public function testSameFileSame()
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Files/able/apple.php';

        $this->assertTrue(same_file($file1, $file2));
    }

    public function testSameFileIdentical()
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Files/baker/banana.php';

        $this->assertTrue(same_file($file1, $file2));
    }

    public function testSameFileDifferent()
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/ci-logo.gif';

        $this->assertFalse(same_file($file1, $file2));
    }

    public function testSameFileOrder()
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/ci-logo.gif';

        $this->assertFalse(same_file($file2, $file1));
    }

    public function testSameFileDirectory()
    {
        $file1 = SUPPORTPATH . 'Files/able/apple.php';
        $file2 = SUPPORTPATH . 'Images/';

        $this->assertFalse(same_file($file1, $file2));
    }

    //--------------------------------------------------------------------

    public function testOctalPermissions()
    {
        $this->assertSame('777', octal_permissions(0777));
        $this->assertSame('655', octal_permissions(0655));
        $this->assertSame('123', octal_permissions(0123));
    }

    public function testSymbolicPermissions()
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

    //--------------------------------------------------------------------

    public function testRealPathURL()
    {
        $this->expectException(InvalidArgumentException::class);
        set_realpath('http://somewhere.com/overtherainbow');
    }

    public function testRealPathInvalid()
    {
        $this->expectException(InvalidArgumentException::class);
        set_realpath(SUPPORTPATH . 'root/../', true);
    }

    public function testRealPathResolved()
    {
        $this->assertSame(SUPPORTPATH . 'Models/', set_realpath(SUPPORTPATH . 'Files/../Models', true));
    }
}
