<?php
namespace CodeIgniter\HTTP\Files;

use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class FileMovingTest extends \CIUnitTestCase
{

	public function setUp()
	{
		parent::setUp();

		$this->root	 = vfsStream::setup();
		$this->path	 = '_support/Files/';
		vfsStream::copyFromFileSystem(TESTPATH . $this->path, $this->root);
		$this->start = $this->root->url() . '/';

		$_FILES = [];
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->root = null;
		if (is_dir('/tmp/destination'))
			rmdir('/tmp/destination');
	}

	//--------------------------------------------------------------------

	public function testMove()
	{
		$finalFilename = 'fileA';

		$_FILES = [
			'userfile1'	 => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/plain',
				'size'		 => 124,
				'tmp_name'	 => '/tmp/fileA.txt',
				'error'		 => 0
			],
			'userfile2'	 => [
				'name'		 => 'fileA.txt',
				'type'		 => 'text/csv',
				'size'		 => 248,
				'tmp_name'	 => '/tmp/fileB.txt',
				'error'		 => 0
			],
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile1'));
		$this->assertTrue($collection->hasFile('userfile2'));

		$destination = $this->root->url() . '/destination';

		// Create the destination if not exists
		is_dir($destination) || mkdir($destination, 0777, true);

		foreach ($collection->all() as $file)
		{
			$this->assertInstanceOf(UploadedFile::class, $file);
			$file->move($destination, $file->getName(), false);
		}

		$this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '.txt'));
		$this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '_1.txt'));
	}

	//--------------------------------------------------------------------

	public function testMoveOverwriting()
	{
		$finalFilename = 'file_with_delimiters_underscore';

		$_FILES = [
			'userfile1'	 => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/plain',
				'size'		 => 124,
				'tmp_name'	 => '/tmp/fileA.txt',
				'error'		 => 0
			],
			'userfile2'	 => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/csv',
				'size'		 => 248,
				'tmp_name'	 => '/tmp/fileB.txt',
				'error'		 => 0
			],
			'userfile3'	 => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/csv',
				'size'		 => 248,
				'tmp_name'	 => '/tmp/fileC.txt',
				'error'		 => 0
			],
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile1'));
		$this->assertTrue($collection->hasFile('userfile2'));
		$this->assertTrue($collection->hasFile('userfile3'));

		$destination = $this->root->url() . '/destination';

		// Create the destination if not exists
		is_dir($destination) || mkdir($destination, 0777, true);

		foreach ($collection->all() as $file)
		{
			$this->assertInstanceOf(UploadedFile::class, $file);
			$file->move($destination, $file->getName(), true);
		}

		$this->assertTrue($this->root->hasChild('destination/' . $finalFilename . '.txt'));
		$this->assertFalse($this->root->hasChild('destination/' . $finalFilename . '_1.txt'));
		$this->assertFalse($this->root->hasChild('destination/' . $finalFilename . '_2.txt'));
		$this->assertFileExists($destination . '/' . $finalFilename . '.txt');
	}

	//--------------------------------------------------------------------

	public function testMoved()
	{
		$finalFilename = 'fileA';

		$_FILES = [
			'userfile1' => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/plain',
				'size'		 => 124,
				'tmp_name'	 => '/tmp/fileA.txt',
				'error'		 => 0
			],
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile1'));

		$destination = $this->root->url() . '/destination';

		// Create the destination if not exists
		is_dir($destination) || mkdir($destination, 0777, true);

		$file = $collection->getFile('userfile1');

		$this->assertInstanceOf(UploadedFile::class, $file);
		$this->assertFalse($file->hasMoved());
		$file->move($destination, $file->getName(), false);
		$this->assertTrue($file->hasMoved());
	}

	//--------------------------------------------------------------------

	public function testAlreadyMoved()
	{
		$finalFilename = 'fileA';

		$_FILES = [
			'userfile1' => [
				'name'		 => $finalFilename . '.txt',
				'type'		 => 'text/plain',
				'size'		 => 124,
				'tmp_name'	 => '/tmp/fileA.txt',
				'error'		 => 0
			],
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile1'));

		$destination = $this->root->url() . '/destination';

		// Create the destination if not exists
		is_dir($destination) || mkdir($destination, 0777, true);

		$this->expectException(HTTPException::class);

		foreach ($collection->all() as $file)
		{
			$file->move($destination, $file->getName(), false);
			$file->move($destination, $file->getName(), false);
		}
	}

	//--------------------------------------------------------------------

	public function testInvalidFile()
	{
		$_FILES = [
			'userfile' => [
				'name'		 => 'someFile.txt',
				'type'		 => 'text/plain',
				'size'		 => '124',
				'tmp_name'	 => '/tmp/myTempFile.txt',
				'error'		 => UPLOAD_ERR_INI_SIZE
			]
		];

		$destination = $this->root->url() . '/destination';
		// don't create the folder, so setPath() is invoked.

		$collection	 = new FileCollection();
		$file		 = $collection->getFile('userfile');

		$this->expectException(HTTPException::class);
		$file->move($destination, $file->getName(), false);
	}

	//--------------------------------------------------------------------

	public function testFailedMove()
	{
		$_FILES = [
			'userfile' => [
				'name'		 => 'someFile.txt',
				'type'		 => 'text/plain',
				'size'		 => '124',
				'tmp_name'	 => '/tmp/myTempFile.txt',
				'error'		 => 0,
			]
		];

		$destination = '/tmp/destination';
		// Create the destination and make it read only
		is_dir($destination) || mkdir($destination, 0400, true);

		$collection	 = new FileCollection();
		$file		 = $collection->getFile('userfile');

		$this->expectException(HTTPException::class);
		$file->move($destination, $file->getName(), false);
	}

	//--------------------------------------------------------------------
}

/*
 * Overwrite the function so that it will only check whether the file exists or not.
 * Original function also checks if the file was uploaded with a POST request.
 *
 * This overwrite is for testing the move operation.
 */

function is_uploaded_file($filename)
{
	if ( ! file_exists($filename))
	{
		file_put_contents($filename, 'data');
	}
	return file_exists($filename);
}

/*
 * Overwrite the function so that it just copy without checking the file is an uploaded file.
 *
 * This overwrite is for testing the move operation.
 */

function move_uploaded_file($filename, $destination)
{
		return copy($filename, $destination);
}
