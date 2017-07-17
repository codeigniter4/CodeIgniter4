<?php namespace CodeIgniter\HTTP\Files;

class FileCollectionTest extends \CIUnitTestCase
{
	public function setUp()
	{
		$_FILES = [];
	}

	//--------------------------------------------------------------------

	public function testAllReturnsArrayWithNoFiles()
	{
	    $files = new FileCollection();

		$this->assertEquals([], $files->all());
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
		$this->assertEquals('text/plain', $file->getClientMimeType());
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
		$this->assertEquals('text/plain', $file->getClientMimeType());
		$this->assertEquals(124, $file->getSize());

		$file = array_pop($files);
		$this->assertTrue($file instanceof UploadedFile);

		$this->assertEquals('fileB.txt', $file->getName());
		$this->assertEquals('/tmp/fileB.txt', $file->getTempName());
		$this->assertEquals('txt', $file->getClientExtension());
		$this->assertEquals('text/csv', $file->getClientMimeType());
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
		$this->assertEquals('text/plain', $file->getClientMimeType());
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
        
        public function testFileReturnsValidSingleFile() {
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
                $file = $collection->getFile('userfile');
                $this->assertTrue($file instanceof UploadedFile);

                $this->assertEquals('someFile.txt', $file->getName());
                $this->assertEquals(124, $file->getSize());
        }
        
    //--------------------------------------------------------------------

        public function testFileNoExistSingleFile() {
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
                $file = $collection->getFile('fileuser');
                $this->AssertNull($file);
        }
        
    //--------------------------------------------------------------------
        
        public function testFileReturnValidMultipleFiles() {
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

		$file_1 = $collection->getFile('userfile.0');
		$this->assertTrue($file_1 instanceof UploadedFile);
		$this->assertEquals('fileA.txt', $file_1->getName());
		$this->assertEquals('/tmp/fileA.txt', $file_1->getTempName());
		$this->assertEquals('txt', $file_1->getClientExtension());
		$this->assertEquals('text/plain', $file_1->getClientMimeType());
		$this->assertEquals(124, $file_1->getSize());
                
		$file_2 = $collection->getFile('userfile.1');
		$this->assertTrue($file_2 instanceof UploadedFile);
		$this->assertEquals('fileB.txt', $file_2->getName());
		$this->assertEquals('/tmp/fileB.txt', $file_2->getTempName());
		$this->assertEquals('txt', $file_2->getClientExtension());
		$this->assertEquals('text/csv', $file_2->getClientMimeType());
		$this->assertEquals(248, $file_2->getSize());
    }
        
    //--------------------------------------------------------------------
        
    public function testFileWithMultipleFilesNestedName() {
        $_FILES = [
			'my-form' => [
				'name' => [
					'details' => [
						'avatars' => ['fileA.txt','fileB.txt']
					]
				],
				'type' => [
					'details' => [
						'avatars' => ['text/plain','text/plain']
					]
				],
				'size' => [
					'details' => [
						'avatars' => [125,243]
					]
				],
				'tmp_name' => [
					'details' => [
						'avatars' => ['/tmp/fileA.txt','/tmp/fileB.txt']
					]
				],
				'error' => [
					'details' => [
						'avatars' => [0,0]
					]
				],
			]
		];
        
        $collection = new FileCollection();

        $file_1 = $collection->getFile('my-form.details.avatars.0');
        $this->assertTrue($file_1 instanceof UploadedFile);
        $this->assertEquals('fileA.txt', $file_1->getName());
		$this->assertEquals('/tmp/fileA.txt', $file_1->getTempName());
		$this->assertEquals('txt', $file_1->getClientExtension());
		$this->assertEquals('text/plain', $file_1->getClientMimeType());
		$this->assertEquals(125, $file_1->getSize());

        $file_2 = $collection->getFile('my-form.details.avatars.1');
        $this->assertTrue($file_2 instanceof UploadedFile);
        $this->assertEquals('fileB.txt', $file_2->getName());
		$this->assertEquals('/tmp/fileB.txt', $file_2->getTempName());
		$this->assertEquals('txt', $file_2->getClientExtension());
		$this->assertEquals('text/plain', $file_2->getClientMimeType());
		$this->assertEquals(243, $file_2->getSize());
    }

    /**
     * @group move-file
     */
    public function testMoveWhereOverwriteIsFalseWithMultipleFilesWithSameName()
    {
        $finalFilename = 'fileA';

        $_FILES = [
            'userfile1' => [
                'name' => $finalFilename . '.txt',
                'type' => 'text/plain',
                'size' => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error' => 0
            ],
            'userfile2' => [
                'name' => 'fileA.txt',
                'type' => 'text/csv',
                'size' => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error' => 0
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));
        $this->assertTrue($collection->hasFile('userfile2'));

        $destination = '/tmp/destination/';

        // Create the destination if not exists
        is_dir($destination) || mkdir($destination, 0777, true);

        foreach ($collection->all() as $file) {
            $this->assertTrue($file instanceof UploadedFile);
            $file->move($destination, $file->getName(), false);
        }

        $this->assertFileExists($destination . $finalFilename . '.txt');
        $this->assertFileNotExists($destination . $finalFilename . '_1.txt');

        // Delete the recently created files for the destination above
        foreach(glob($destination . "*") as $f) {
            unlink($f);
        }
        // Delete the recently created destination dir
        rmdir($destination);
    }

    //--------------------------------------------------------------------

    /**
     * @group move-file
     */
    public function testMoveWhereOverwriteIsTrueWithMultipleFilesWithSameName()
    {
        $finalFilename = 'file_with_delimiters_underscore';

        $_FILES = [
            'userfile1' => [
                'name' => $finalFilename . '.txt',
                'type' => 'text/plain',
                'size' => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error' => 0
            ],
            'userfile2' => [
                'name' => $finalFilename . '.txt',
                'type' => 'text/csv',
                'size' => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error' => 0
            ],
            'userfile3' => [
                'name' => $finalFilename . '.txt',
                'type' => 'text/csv',
                'size' => 248,
                'tmp_name' => '/tmp/fileC.txt',
                'error' => 0
            ],
        ];

        $collection = new FileCollection();

        $this->assertTrue($collection->hasFile('userfile1'));
        $this->assertTrue($collection->hasFile('userfile2'));
        $this->assertTrue($collection->hasFile('userfile3'));

        $destination = '/tmp/destination/';

        // Create the destination if not exists
        is_dir($destination) || mkdir($destination, 0777, true);

        foreach ($collection->all() as $file) {
            $this->assertTrue($file instanceof UploadedFile);
            $file->move($destination, $file->getName(), true);
        }

        $this->assertFileExists($destination . $finalFilename . '.txt');
        $this->assertFileExists($destination . $finalFilename . '_1.txt');
        $this->assertFileExists($destination . $finalFilename . '_2.txt');

        // Delete the recently created files for the destination above
        foreach(glob($destination . "*") as $f) {
            unlink($f);
        }
        // Delete the recently created destination dir
        rmdir($destination);
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
    if (! file_exists($filename))
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
