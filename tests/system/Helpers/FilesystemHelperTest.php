<?php

namespace CodeIgniter\Helpers;

use org\bovigo\vfs\vfsStream;

class FilesystemHelperTest extends \CodeIgniter\Test\CIUnitTestCase
{

	protected function setUp(): void
	{
		parent::setUp();

		$this->structure = [
			'foo'           => [
				'bar' => 'Once upon a midnight dreary',
				'baz' => 'While I pondered weak and weary',
			],
			'boo'           => [
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
			'foo' . DIRECTORY_SEPARATOR           => [
						'bar',
						'baz',
					],
			'boo' . DIRECTORY_SEPARATOR           => [
						'far',
						'faz',
					],
			'AnEmptyFolder' . DIRECTORY_SEPARATOR => [],
			'simpleFile'
		];

		$root = vfsStream::setup('root', null, $this->structure);
		$this->assertTrue($root->hasChild('foo'));

		$this->assertEquals($expected, directory_map(vfsStream::url('root')));
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
			'.hidden'
		];

		$root = vfsStream::setup('root', null, $this->structure);
		$this->assertTrue($root->hasChild('foo'));

		$this->assertEquals($expected, directory_map(vfsStream::url('root'), false, true));
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

		$this->assertEquals($expected, directory_map(vfsStream::url('root'), 1, true));
	}

	public function testDirectoryMapHandlesNotfound()
	{
		$this->assertEquals([], directory_map(SUPPORTPATH . 'Files/shaker/'));
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
		$vfs = vfsStream::setup('root');

		$this->assertFalse(write_file(vfsStream::url('apple#test.php'), 'Simple'));
	}

	//--------------------------------------------------------------------

	public function testDeleteFilesDefaultsToOneLevelDeep()
	{
		$this->assertTrue(function_exists('delete_files'));

		$vfs = vfsStream::setup('root', null, $this->structure);

		delete_files(vfsStream::url('root'));

		$this->assertFalse($vfs->hasChild('simpleFile'));
		$this->assertFalse($vfs->hasChild('.hidden'));
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
		$this->assertFalse($vfs->hasChild('.hidden'));
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
			'foo',
			'boo',
			'AnEmptyFolder',
			'simpleFile',
		];

		$vfs = vfsStream::setup('root', null, $this->structure);

		$this->assertEquals($expected, get_filenames($vfs->url(), false));
	}

	public function testGetFilenamesWithSource()
	{
		$this->assertTrue(function_exists('delete_files'));

		// Not sure the directory names should actually show up
		// here but this matches v3.x results.
		$expected = [
			DIRECTORY_SEPARATOR . 'foo',
			DIRECTORY_SEPARATOR . 'boo',
			DIRECTORY_SEPARATOR . 'AnEmptyFolder',
			DIRECTORY_SEPARATOR . 'simpleFile',
		];

		$vfs = vfsStream::setup('root', null, $this->structure);

		$this->assertEquals($expected, get_filenames($vfs->url(), true));
	}

	public function testGetFilenamesFailure()
	{
		$this->assertEquals([], get_filenames(SUPPORTPATH . 'Files/shaker/'));
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

		$this->assertEquals($expected, get_dir_file_info(SUPPORTPATH . 'Files/baker'));
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

		$this->assertEquals($expected, get_dir_file_info(SUPPORTPATH . 'Files#baker'));
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

		$this->assertEquals($expected, get_file_info(SUPPORTPATH . 'Files/baker/banana.php'));
	}

	public function testGetFileInfoCustom()
	{
		$expected = [
			'readable'   => true,
			'writable'   => true,
			'executable' => false,
		];

		$this->assertEquals($expected, get_file_info(SUPPORTPATH . 'Files/baker/banana.php', 'readable,writable,executable'));
	}

	public function testGetFileInfoPerms()
	{
		$file     = SUPPORTPATH . 'Files/baker/banana.php';
		$expected = 0664;
		chmod($file, $expected);

		$stuff = get_file_info($file, 'fileperms');

		$this->assertEquals($expected, $stuff['fileperms'] & 0777);
	}

	public function testGetFileNotThereInfo()
	{
		$expected = null;

		$this->assertEquals($expected, get_file_info(SUPPORTPATH . 'Files/icer'));
	}

	//--------------------------------------------------------------------
	public function testOctalPermissions()
	{
		$this->assertEquals('777', octal_permissions(0777));
		$this->assertEquals('655', octal_permissions(0655));
		$this->assertEquals('123', octal_permissions(0123));
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

		foreach ($expected as $perm => $value)
		{
			$this->assertEquals($value, symbolic_permissions($perm));
		}
	}

	//--------------------------------------------------------------------

	public function testRealPathURL()
	{
		$this->expectException(\InvalidArgumentException::class);
		set_realpath('http://somewhere.com/overtherainbow');
	}

	public function testRealPathInvalid()
	{
		$this->expectException(\InvalidArgumentException::class);
		set_realpath(SUPPORTPATH . 'root/../', true);
	}

	public function testRealPathResolved()
	{
		$this->assertEquals(SUPPORTPATH . 'Models/', set_realpath(SUPPORTPATH . 'Files/../Models', true));
	}

}
