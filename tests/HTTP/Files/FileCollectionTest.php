<?php namespace CodeIgniter\HTTP\Files;

class FileCollectionTest extends \CIUnitTestCase
{
	public function setUp()
	{
		$_FILES = [];
	}

	//--------------------------------------------------------------------

	public function testAllReturnsNullWithNoFiles()
	{
	    $files = new FileCollection();

		$this->assertNull($files->all());
	}

	//--------------------------------------------------------------------

	public function testAllReturnsValidSingleFile()
	{
		$_FILES = [
			'userfile' => [
				'name' => 'someFile.txt',
				'type' => 'text/plain',
				'size' => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error' => 0
			]
		];

		$collection = new FileCollection();
		$files      = $collection->all();
		$this->assertEquals(1, count($files));

		$file = array_shift($files);
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('someFile.txt', $file->getName());
		$this->assertEquals(124, $file->getSize());
	}

	//--------------------------------------------------------------------

	public function testAllReturnsValidMultipleFilesSameName()
	{
		$_FILES = [
			'userfile' => [
				'name' => ['fileA.txt', 'fileB.txt'],
				'type' => ['text/plain', 'text/csv'],
				'size' => ['124', '248'],
				'tmp_name' => ['/tmp/fileA.txt', '/tmp/fileB.txt'],
				'error' => 0
			]
		];

		$collection = new FileCollection();
		$files      = $collection->all();
		$this->assertEquals(1, count($files));
		$this->assertEquals('userfile', key($files));

		$files = array_shift($files);
		$this->assertEquals(2, count($files));

		$file = $files[0];
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('fileA.txt', $file->getName());
		$this->assertEquals('/tmp/fileA.txt', $file->getTempName());
		$this->assertEquals('txt', $file->getClientExtension());
		$this->assertEquals('text/plain', $file->getClientType());
		$this->assertEquals(124, $file->getSize());
	}

	//--------------------------------------------------------------------


	public function testAllReturnsValidMultipleFilesDifferentName()
	{
		$_FILES = [
			'userfile1' => [
				'name' => 'fileA.txt',
				'type' => 'text/plain',
				'size' => 124,
				'tmp_name' => '/tmp/fileA.txt',
				'error' => 0
			],
			'userfile2' => [
				'name' => 'fileB.txt',
				'type' => 'text/csv',
				'size' => 248,
				'tmp_name' => '/tmp/fileB.txt',
				'error' => 0
			],
		];

		$collection = new FileCollection();
		$files      = $collection->all();
		$this->assertEquals(2, count($files));
		$this->assertEquals('userfile1', key($files));

		$file = array_shift($files);
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('fileA.txt', $file->getName());
		$this->assertEquals('/tmp/fileA.txt', $file->getTempName());
		$this->assertEquals('txt', $file->getClientExtension());
		$this->assertEquals('text/plain', $file->getClientType());
		$this->assertEquals(124, $file->getSize());

		$file = array_pop($files);
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('fileB.txt', $file->getName());
		$this->assertEquals('/tmp/fileB.txt', $file->getTempName());
		$this->assertEquals('txt', $file->getClientExtension());
		$this->assertEquals('text/csv', $file->getClientType());
		$this->assertEquals(248, $file->getSize());
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testAllReturnsValidSingleFileNestedName()
	{
		$_FILES = [
			'userfile' => [
				'name' => [
					'foo' => [
						'bar' => 'fileA.txt'
					]
				],
				'type' => [
					'foo' => [
						'bar' => 'text/plain'
					]
				],
				'size' => [
					'foo' => [
						'bar' => 124
					]
				],
				'tmp_name' => [
					'foo' => [
						'bar' => '/tmp/fileA.txt'
					]
				],
				'error' => 0
			]
		];

		$collection = new FileCollection();
		$files      = $collection->all();
		$this->assertEquals(1, count($files));
		$this->assertEquals('userfile', key($files));

		$this->assertTrue(isset($files['userfile']['foo']['bar']));

		$file = $files['userfile']['foo']['bar'];
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('fileA.txt', $file->getName());
		$this->assertEquals('/tmp/fileA.txt', $file->getTempName());
		$this->assertEquals('txt', $file->getClientExtension());
		$this->assertEquals('text/plain', $file->getClientType());
		$this->assertEquals(124, $file->getSize());
	}

	//--------------------------------------------------------------------

	public function testHasFileWithSingleFile()
	{
		$_FILES = [
			'userfile' => [
				'name' => 'someFile.txt',
				'type' => 'text/plain',
				'size' => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error' => 0
			]
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile'));
		$this->assertFalse($collection->hasFile('foo'));
	}

	//--------------------------------------------------------------------

	public function testHasFileWithMultipleFilesWithDifferentNames()
	{
		$_FILES = [
			'userfile1' => [
				'name' => 'fileA.txt',
				'type' => 'text/plain',
				'size' => 124,
				'tmp_name' => '/tmp/fileA.txt',
				'error' => 0
			],
			'userfile2' => [
				'name' => 'fileB.txt',
				'type' => 'text/csv',
				'size' => 248,
				'tmp_name' => '/tmp/fileB.txt',
				'error' => 0
			],
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile1'));
		$this->assertTrue($collection->hasFile('userfile2'));
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 */
	public function testHasFileWithSingleFileNestedName()
	{
		$_FILES = [
			'userfile' => [
				'name' => [
					'foo' => [
						'bar' => 'fileA.txt'
					]
				],
				'type' => [
					'foo' => [
						'bar' => 'text/plain'
					]
				],
				'size' => [
					'foo' => [
						'bar' => 124
					]
				],
				'tmp_name' => [
					'foo' => [
						'bar' => '/tmp/fileA.txt'
					]
				],
				'error' => 0
			]
		];

		$collection = new FileCollection();

		$this->assertTrue($collection->hasFile('userfile'));
		$this->assertTrue($collection->hasFile('userfile.foo'));
		$this->assertTrue($collection->hasFile('userfile.foo.bar'));
	}

	//--------------------------------------------------------------------

	public function testErrorString()
	{
		$_FILES = [
			'userfile' => [
				'name' => 'someFile.txt',
				'type' => 'text/plain',
				'size' => '124',
				'tmp_name' => '/tmp/myTempFile.txt',
				'error' => UPLOAD_ERR_INI_SIZE
			]
		];

		$expected = 'The file "someFile.txt" exceeds your upload_max_filesize ini directive.';

		$collection = new FileCollection();
		$file = $collection->getFile('userfile');

		$this->assertEquals($expected, $file->getErrorString());
	}

	//--------------------------------------------------------------------
}
