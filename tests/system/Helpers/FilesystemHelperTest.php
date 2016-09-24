<?php namespace CodeIgniter\Helpers;

include BASEPATH.'../vendor/autoload.php';

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;

class FilesystemHelperTest extends \CIUnitTestCase
{

    public function testDirectoryMapDefaults()
    {
        helper('filesystem');
        $this->assertTrue(function_exists('directory_map'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        $expected = [
            'foo/' => [
                'bar',
                'baz'
            ],
            'boo/' => [
                'far',
                'faz'
            ],
            'AnEmptyFolder/' => [],
            'simpleFile'
        ];

        $root = vfsStream::setup('root', null, $structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertEquals($expected, directory_map(vfsStream::url('root')));
    }

    //--------------------------------------------------------------------

    public function testDirectoryMapShowsHiddenFiles()
    {
        helper('filesystem');
        $this->assertTrue(function_exists('directory_map'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        $expected = [
            'foo/' => [
                'bar',
                'baz'
            ],
            'boo/' => [
                'far',
                'faz'
            ],
            'AnEmptyFolder/' => [],
            'simpleFile',
            '.hidden'
        ];

        $root = vfsStream::setup('root', null, $structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertEquals($expected, directory_map(vfsStream::url('root'), false, true));
    }

    //--------------------------------------------------------------------

    public function testDirectoryMapLimitsRecursion()
    {
        $this->assertTrue(function_exists('directory_map'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        $expected = [
            'foo/',
            'boo/',
            'AnEmptyFolder/',
            'simpleFile',
            '.hidden'
        ];

        $root = vfsStream::setup('root', null, $structure);
        $this->assertTrue($root->hasChild('foo'));

        $this->assertEquals($expected, directory_map(vfsStream::url('root'), 1, true));
    }

    //--------------------------------------------------------------------

    public function testWriteFileSuccess()
    {
        $vfs = vfsStream::setup('root');

        $this->assertTrue(write_file(vfsStream::url('root/test.php'), 'Simple'));
        $this->assertFileExists($vfs->getChild('test.php')->url());
    }

    //--------------------------------------------------------------------

    public function testDeleteFilesDefaultsToOneLevelDeep()
    {
        $this->assertTrue(function_exists('delete_files'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        $vfs = vfsStream::setup('root', null, $structure);

        delete_files(vfsStream::url('root'));

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertFalse($vfs->hasChild('.hidden'));
        $this->assertTrue($vfs->hasChild('foo'));
        $this->assertTrue($vfs->hasChild('boo'));
        $this->assertTrue($vfs->hasChild('AnEmptyFolder'));
    }

    //--------------------------------------------------------------------

    public function testDeleteFilesHandlesRecursion()
    {
        $this->assertTrue(function_exists('delete_files'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        $vfs = vfsStream::setup('root', null, $structure);

        delete_files(vfsStream::url('root'), true);

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertFalse($vfs->hasChild('.hidden'));
        $this->assertFalse($vfs->hasChild('foo'));
        $this->assertFalse($vfs->hasChild('boo'));
        $this->assertFalse($vfs->hasChild('AnEmptyFolder'));
    }

    //--------------------------------------------------------------------

    public function testDeleteFilesLeavesHTFiles()
    {
        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon',
            '.htaccess' => 'Deny All',
            'index.html' => 'foo',
            'index.php' => 'blah'
        ];

        $vfs = vfsStream::setup('root', null, $structure);

        delete_files(vfsStream::url('root'), true, true);

        $this->assertFalse($vfs->hasChild('simpleFile'));
        $this->assertFalse($vfs->hasChild('foo'));
        $this->assertFalse($vfs->hasChild('boo'));
        $this->assertFalse($vfs->hasChild('AnEmptyFolder'));
        $this->assertTrue($vfs->hasChild('.htaccess'));
        $this->assertTrue($vfs->hasChild('index.html'));
        $this->assertTrue($vfs->hasChild('index.php'));
    }

    //--------------------------------------------------------------------

    public function testGetFilenames()
    {
        $this->assertTrue(function_exists('delete_files'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        // Not sure the directory names should actually show up
        // here but this matches v3.x results.
        $expected = [
            'foo',
            'boo',
            'AnEmptyFolder',
            'simpleFile'
        ];

        $vfs = vfsStream::setup('root', null, $structure);

        $this->assertEquals($expected, get_filenames($vfs->url(), false));
    }

    //--------------------------------------------------------------------

    public function testGetFilenamesWithSource()
    {
        $this->assertTrue(function_exists('delete_files'));

        $structure = [
            'foo' => [
                'bar' => 'Once upon a midnight dreary',
                'baz' => 'While I pondered weak and weary'
            ],
            'boo' => [
                'far' => 'Upon a tome of long-forgotten lore',
                'faz' => 'There came a tapping up on the door'
            ],
            'AnEmptyFolder' => [],
            'simpleFile' => 'A tap-tap-tapping upon my door',
            '.hidden' => 'There is no spoon'
        ];

        // Not sure the directory names should actually show up
        // here but this matches v3.x results.
        $expected = [
            '/foo',
            '/boo',
            '/AnEmptyFolder',
            '/simpleFile'
        ];

        $vfs = vfsStream::setup('root', null, $structure);

        $this->assertEquals($expected, get_filenames($vfs->url(), true));
    }

    //--------------------------------------------------------------------


}
