<?php

namespace CodeIgniter\HTTP\Files;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Mimes;

/**
 * @internal
 */
final class FileCollectionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $_FILES = [];
    }

    //--------------------------------------------------------------------

    public function testAllReturnsArrayWithNoFiles()
    {
        $files = new FileCollection();

        $this->assertSame([], $files->all());
    }

    //--------------------------------------------------------------------

    public function testAllReturnsValidSingleFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();
        $files      = $collection->all();
        $this->assertCount(1, $files);

        $file = array_shift($files);
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('someFile.txt', $file->getName());
        $this->assertSame(124, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testAllReturnsValidMultipleFilesSameName()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'fileA.txt',
                    'fileB.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/csv',
                ],
                'size' => [
                    '124',
                    '248',
                ],
                'tmp_name' => [
                    '/tmp/fileA.txt',
                    '/tmp/fileB.txt',
                ],
                'error' => 0,
            ],
        ];

        $collection = new FileCollection();
        $files      = $collection->all();
        $this->assertCount(1, $files);
        $this->assertSame('userfile', key($files));

        $files = array_shift($files);
        $this->assertCount(2, $files);

        $file = $files[0];
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('fileA.txt', $file->getName());
        $this->assertSame('/tmp/fileA.txt', $file->getTempName());
        $this->assertSame('txt', $file->getClientExtension());
        $this->assertSame('text/plain', $file->getClientMimeType());
        $this->assertSame(124, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testAllReturnsValidMultipleFilesDifferentName()
    {
        $_FILES = [
            'userfile1' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => 'fileB.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();
        $files      = $collection->all();
        $this->assertCount(2, $files);
        $this->assertSame('userfile1', key($files));

        $file = array_shift($files);
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('fileA.txt', $file->getName());
        $this->assertSame('fileA.txt', $file->getClientName());
        $this->assertSame('/tmp/fileA.txt', $file->getTempName());
        $this->assertSame('txt', $file->getClientExtension());
        $this->assertSame('text/plain', $file->getClientMimeType());
        $this->assertSame(124, $file->getSize());

        $file = array_pop($files);
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('fileB.txt', $file->getName());
        $this->assertSame('fileB.txt', $file->getClientName());
        $this->assertSame('/tmp/fileB.txt', $file->getTempName());
        $this->assertSame('txt', $file->getClientExtension());
        $this->assertSame('text/csv', $file->getClientMimeType());
        $this->assertSame(248, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testExtensionGuessing()
    {
        $_FILES = [
            'userfile1' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/plain',
                'size'     => 4,
                'tmp_name' => SUPPORTPATH . 'HTTP/Files/tmp/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => 'fileB.txt',
                'type'     => 'text/csv',
                'size'     => 9,
                'tmp_name' => SUPPORTPATH . 'HTTP/Files/tmp/fileB.txt',
                'error'    => 0,
            ],
            'userfile3' => [
                'name'     => 'fileC.csv',
                'type'     => 'text/csv',
                'size'     => 16,
                'tmp_name' => SUPPORTPATH . 'HTTP/Files/tmp/fileC.csv',
                'error'    => 0,
            ],
            'userfile4' => [
                'name'     => 'fileD.zip',
                'type'     => 'application/zip',
                'size'     => 441,
                'tmp_name' => SUPPORTPATH . 'HTTP/Files/tmp/fileD.zip',
                'error'    => 0,
            ],
            'userfile5' => [
                'name'     => 'fileE.zip.rar',
                'type'     => 'application/rar',
                'size'     => 441,
                'tmp_name' => SUPPORTPATH . 'HTTP/Files/tmp/fileE.zip.rar',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        // proposed extension matches finfo_open mime type (text/plain)
        $file = $collection->getFile('userfile1');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('txt', $file->getExtension());

        // proposed extension matches finfo_open mime type (text/plain)
        $file = $collection->getFile('userfile2');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('txt', $file->getExtension());
        // but not client mime type
        $this->assertNull(Mimes::guessExtensionFromType($file->getClientMimeType(), $file->getClientExtension()));

        // proposed extension does not match finfo_open mime type (text/plain)
        // but can be resolved by reverse searching
        $file = $collection->getFile('userfile3');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('csv', $file->getExtension());

        // proposed extension matches finfo_open mime type (application/zip)
        $file = $collection->getFile('userfile4');
        $this->assertInstanceOf(UploadedFile::class, $file);
        $this->assertSame('zip', $file->getExtension());

        // proposed extension matches client mime type, but not finfo_open mime type (application/zip)
        // this is a zip file (userFile4) but hat been renamed to 'rar'
        $file = $collection->getFile('userfile5');
        $this->assertInstanceOf(UploadedFile::class, $file);
        // getExtension falls back to clientExtension (insecure)
        $this->assertSame('rar', $file->getExtension());
        $this->assertSame('rar', Mimes::guessExtensionFromType($file->getClientMimeType(), $file->getClientExtension()));
        // guessExtension is secure and does not returns empty
        $this->assertSame('', $file->guessExtension());
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
                        'bar' => 'fileA.txt',
                    ],
                ],
                'type' => [
                    'foo' => [
                        'bar' => 'text/plain',
                    ],
                ],
                'size' => [
                    'foo' => [
                        'bar' => 124,
                    ],
                ],
                'tmp_name' => [
                    'foo' => [
                        'bar' => '/tmp/fileA.txt',
                    ],
                ],
                'error' => 0,
            ],
        ];

        $collection = new FileCollection();
        $files      = $collection->all();
        $this->assertCount(1, $files);
        $this->assertSame('userfile', key($files));

        $this->assertArrayHasKey('bar', $files['userfile']['foo']);

        $file = $files['userfile']['foo']['bar'];
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('fileA.txt', $file->getName());
        $this->assertSame('/tmp/fileA.txt', $file->getTempName());
        $this->assertSame('txt', $file->getClientExtension());
        $this->assertSame('text/plain', $file->getClientMimeType());
        $this->assertSame(124, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testHasFileWithSingleFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
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
                'name'     => 'fileA.txt',
                'type'     => 'text/plain',
                'size'     => 124,
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
            'userfile2' => [
                'name'     => 'fileB.txt',
                'type'     => 'text/csv',
                'size'     => 248,
                'tmp_name' => '/tmp/fileB.txt',
                'error'    => 0,
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
                        'bar' => 'fileA.txt',
                    ],
                ],
                'type' => [
                    'foo' => [
                        'bar' => 'text/plain',
                    ],
                ],
                'size' => [
                    'foo' => [
                        'bar' => 124,
                    ],
                ],
                'tmp_name' => [
                    'foo' => [
                        'bar' => '/tmp/fileA.txt',
                    ],
                ],
                'error' => 0,
            ],
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
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => UPLOAD_ERR_INI_SIZE,
            ],
        ];

        $expected = 'The file "someFile.txt" exceeds your upload_max_filesize ini directive.';

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame($expected, $file->getErrorString());
    }

    public function testErrorStringWithUnknownError()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 123,
            ],
        ];

        $expected = 'The file "someFile.txt" was not uploaded due to an unknown error.';

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame($expected, $file->getErrorString());
    }

    public function testErrorStringWithNoError()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
            ],
        ];

        $expected = 'The file uploaded with success.';

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame($expected, $file->getErrorString());
    }

    //--------------------------------------------------------------------

    public function testError()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => UPLOAD_ERR_INI_SIZE,
            ],
        ];

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame(UPLOAD_ERR_INI_SIZE, $file->getError());
    }

    public function testErrorWithUnknownError()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
            ],
        ];

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame(0, $file->getError());
    }

    public function testErrorWithNoError()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');

        $this->assertSame(UPLOAD_ERR_OK, $file->getError());
    }

    //--------------------------------------------------------------------

    public function testFileReturnsValidSingleFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();
        $file       = $collection->getFile('userfile');
        $this->assertInstanceOf(UploadedFile::class, $file);

        $this->assertSame('someFile.txt', $file->getName());
        $this->assertSame(124, $file->getSize());
    }

    //--------------------------------------------------------------------

    public function testFileNoExistSingleFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'someFile.txt',
                'type'     => 'text/plain',
                'size'     => '124',
                'tmp_name' => '/tmp/myTempFile.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();
        $file       = $collection->getFile('fileuser');
        $this->AssertNull($file);
    }

    //--------------------------------------------------------------------

    public function testFileReturnValidMultipleFiles()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'fileA.txt',
                    'fileB.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/csv',
                ],
                'size' => [
                    '124',
                    '248',
                ],
                'tmp_name' => [
                    '/tmp/fileA.txt',
                    '/tmp/fileB.txt',
                ],
                'error' => 0,
            ],
        ];

        $collection = new FileCollection();

        $file1 = $collection->getFile('userfile.0');
        $this->assertInstanceOf(UploadedFile::class, $file1);
        $this->assertSame('fileA.txt', $file1->getName());
        $this->assertSame('/tmp/fileA.txt', $file1->getTempName());
        $this->assertSame('txt', $file1->getClientExtension());
        $this->assertSame('text/plain', $file1->getClientMimeType());
        $this->assertSame(124, $file1->getSize());

        $file2 = $collection->getFile('userfile.1');
        $this->assertInstanceOf(UploadedFile::class, $file2);
        $this->assertSame('fileB.txt', $file2->getName());
        $this->assertSame('/tmp/fileB.txt', $file2->getTempName());
        $this->assertSame('txt', $file2->getClientExtension());
        $this->assertSame('text/csv', $file2->getClientMimeType());
        $this->assertSame(248, $file2->getSize());
    }

    //--------------------------------------------------------------------

    public function testFileWithMultipleFilesNestedName()
    {
        $_FILES = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatars' => [
                            'fileA.txt',
                            'fileB.txt',
                        ],
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatars' => [
                            'text/plain',
                            'text/plain',
                        ],
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatars' => [
                            125,
                            243,
                        ],
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatars' => [
                            '/tmp/fileA.txt',
                            '/tmp/fileB.txt',
                        ],
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatars' => [
                            0,
                            0,
                        ],
                    ],
                ],
            ],
        ];

        $collection = new FileCollection();

        $file1 = $collection->getFile('my-form.details.avatars.0');
        $this->assertInstanceOf(UploadedFile::class, $file1);
        $this->assertSame('fileA.txt', $file1->getName());
        $this->assertSame('/tmp/fileA.txt', $file1->getTempName());
        $this->assertSame('txt', $file1->getClientExtension());
        $this->assertSame('text/plain', $file1->getClientMimeType());
        $this->assertSame(125, $file1->getSize());

        $file2 = $collection->getFile('my-form.details.avatars.1');
        $this->assertInstanceOf(UploadedFile::class, $file2);
        $this->assertSame('fileB.txt', $file2->getName());
        $this->assertSame('/tmp/fileB.txt', $file2->getTempName());
        $this->assertSame('txt', $file2->getClientExtension());
        $this->assertSame('text/plain', $file2->getClientMimeType());
        $this->assertSame(243, $file2->getSize());
    }

    //--------------------------------------------------------------------

    public function testDoesntHaveFile()
    {
        $_FILES = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatars' => [
                            'fileA.txt',
                            'fileB.txt',
                        ],
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatars' => [
                            'text/plain',
                            'text/plain',
                        ],
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatars' => [
                            125,
                            243,
                        ],
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatars' => [
                            '/tmp/fileA.txt',
                            '/tmp/fileB.txt',
                        ],
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatars' => [
                            0,
                            0,
                        ],
                    ],
                ],
            ],
        ];

        $collection = new FileCollection();

        $this->assertFalse($collection->hasFile('my-form.detailz.avatars.0'));
        $this->assertNull($collection->getFile('my-form.detailz.avatars.0'));
    }

    //--------------------------------------------------------------------

    public function testGetFileMultipleHasNoFile()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'fileA.txt',
                    'fileB.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/csv',
                ],
                'size' => [
                    '124',
                    '248',
                ],
                'tmp_name' => [
                    '/tmp/fileA.txt',
                    '/tmp/fileB.txt',
                ],
                'error' => 0,
            ],
        ];

        $collection = new FileCollection();

        $files = $collection->getFileMultiple('userfiletest');

        $this->assertNull($files);
    }

    //--------------------------------------------------------------------

    public function testGetFileMultipleReturnValidDotNotationSyntax()
    {
        $_FILES = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatars' => [
                            'fileA.txt',
                            'fileB.txt',
                        ],
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatars' => [
                            'text/plain',
                            'text/plain',
                        ],
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatars' => [
                            125,
                            243,
                        ],
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatars' => [
                            '/tmp/fileA.txt',
                            '/tmp/fileB.txt',
                        ],
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatars' => [
                            0,
                            0,
                        ],
                    ],
                ],
            ],
        ];

        $collection = new FileCollection();

        $files = $collection->getFileMultiple('my-form.details.avatars');
        $this->assertIsArray($files);
        $this->assertCount(2, $files);

        $this->assertInstanceOf(UploadedFile::class, $files[0]);
        $this->assertSame('fileA.txt', $files[0]->getName());
        $this->assertSame('/tmp/fileA.txt', $files[0]->getTempName());
        $this->assertSame('txt', $files[0]->getClientExtension());
        $this->assertSame('text/plain', $files[0]->getClientMimeType());
        $this->assertSame(125, $files[0]->getSize());

        $this->assertInstanceOf(UploadedFile::class, $files[1]);
        $this->assertSame('fileB.txt', $files[1]->getName());
        $this->assertSame('/tmp/fileB.txt', $files[1]->getTempName());
        $this->assertSame('txt', $files[1]->getClientExtension());
        $this->assertSame('text/plain', $files[1]->getClientMimeType());
        $this->assertSame(243, $files[1]->getSize());
    }

    //--------------------------------------------------------------------

    public function testGetFileMultipleReturnInvalidDotNotationSyntax()
    {
        $_FILES = [
            'my-form' => [
                'name' => [
                    'details' => [
                        'avatars' => 'fileA.txt',
                    ],
                ],
                'type' => [
                    'details' => [
                        'avatars' => 'text/plain',
                    ],
                ],
                'size' => [
                    'details' => [
                        'avatars' => 243,
                    ],
                ],
                'tmp_name' => [
                    'details' => [
                        'avatars' => '/tmp/fileA.txt',
                    ],
                ],
                'error' => [
                    'details' => [
                        'avatars' => 0,
                    ],
                ],
            ],
        ];

        $collection = new FileCollection();

        $files = $collection->getFileMultiple('my-form.details.avatars');
        $this->assertNull($files);
    }

    //--------------------------------------------------------------------

    public function testGetFileMultipleReturnValidMultipleFiles()
    {
        $_FILES = [
            'userfile' => [
                'name' => [
                    'fileA.txt',
                    'fileB.txt',
                ],
                'type' => [
                    'text/plain',
                    'text/csv',
                ],
                'size' => [
                    '124',
                    '248',
                ],
                'tmp_name' => [
                    '/tmp/fileA.txt',
                    '/tmp/fileB.txt',
                ],
                'error' => 0,
            ],
        ];

        $collection = new FileCollection();

        $files = $collection->getFileMultiple('userfile');
        $this->assertCount(2, $files);
        $this->assertIsArray($files);

        $this->assertInstanceOf(UploadedFile::class, $files[0]);
        $this->assertSame(124, $files[0]->getSize());
        $this->assertSame('fileA.txt', $files[0]->getName());
        $this->assertSame('/tmp/fileA.txt', $files[0]->getTempName());
        $this->assertSame('txt', $files[0]->getClientExtension());
        $this->assertSame('text/plain', $files[0]->getClientMimeType());

        $this->assertInstanceOf(UploadedFile::class, $files[1]);
        $this->assertSame(248, $files[1]->getSize());
        $this->assertSame('fileB.txt', $files[1]->getName());
        $this->assertSame('/tmp/fileB.txt', $files[1]->getTempName());
        $this->assertSame('txt', $files[1]->getClientExtension());
        $this->assertSame('text/csv', $files[1]->getClientMimeType());
    }

    //--------------------------------------------------------------------

    public function testGetFileMultipleReturnInvalidSingleFile()
    {
        $_FILES = [
            'userfile' => [
                'name'     => 'fileA.txt',
                'type'     => 'text/csv',
                'size'     => '248',
                'tmp_name' => '/tmp/fileA.txt',
                'error'    => 0,
            ],
        ];

        $collection = new FileCollection();

        $files = $collection->getFileMultiple('userfile');
        $this->assertNull($files);
    }

    //--------------------------------------------------------------------
}
