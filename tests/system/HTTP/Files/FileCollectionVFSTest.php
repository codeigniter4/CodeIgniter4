<?php
namespace CodeIgniter\HTTP\Files;

use org\bovigo\vfs\vfsStream;
use CodeIgniter\HTTP\Exceptions\HTTPException;

class FileCollectionVFSTest extends \CIUnitTestCase
{

	public function setUp()
	{
		parent::setUp();

		$this->root = vfsStream::setup();
		$this->path = '_support/HTTP/Files';
		vfsStream::copyFromFileSystem(TESTPATH . $this->path, $this->root);
		$this->start = $this->root->url() . '/';

		$_FILES = [];
	}

	public function tearDown()
	{
		parent::tearDown();
		$this->root = null;

		//      // cleanup folder being left behind (why?)
		//      $leftover = WRITEPATH . 'uploads/vfs:';
		//      if (is_dir($leftover))
		//      {
		//          rrmdir($leftover);
		//      }
	}

	//--------------------------------------------------------------------

	public function testExtensionGuessing()
	{
		$_FILES = [
			'userfile1' => [
				'name'     => 'fileA.txt',
				'type'     => 'text/plain',
				'size'     => 124,
				'tmp_name' => '/fileA.txt',
				'error'    => 0,
			],
			'userfile2' => [
				'name'     => 'fileB.txt',
				'type'     => 'text/csv',
				'size'     => 248,
				'tmp_name' => '/fileB.txt',
				'error'    => 0,
			],
		];

		$collection = new FileCollection();
		$files      = $collection->all();

		$file = array_shift($files);
		$this->assertInstanceOf(UploadedFile::class, $file);
		$this->assertEquals('txt', $file->getExtension());

		$file = array_pop($files);
		$this->assertInstanceOf(UploadedFile::class, $file);
		$this->assertEquals('csv', $file->guessExtension());
	}

}
