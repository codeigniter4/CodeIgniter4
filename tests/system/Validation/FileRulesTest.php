<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class FileRulesTest extends CIUnitTestCase
{
    /**
     * @var Validation
     */
    protected $validation;

    protected $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();

        $_FILES = [
            'avatar' => [
                'tmp_name' => TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                'name'     => 'my-avatar.png',
                'size'     => 4614,
                'type'     => 'image/png',
                'error'    => 0,
                'width'    => 640,
                'height'   => 400,
            ],
            'bigfile' => [
                'tmp_name' => TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                'name'     => 'my-big-file.png',
                'size'     => 1024000,
                'type'     => 'image/png',
                'error'    => 0,
                'width'    => 640,
                'height'   => 400,
            ],
            'photo' => [
                'tmp_name' => TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                'name'     => 'my-photo.png',
                'size'     => 4614,
                'type'     => 'image/png',
                'error'    => 1, // upload_max_filesize exceeded
                'width'    => 640,
                'height'   => 400,
            ],
            'images' => [
                'tmp_name' => [
                    TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                    TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                ],
                'name' => [
                    'my_avatar.png',
                    'my_bigfile.png',
                ],
                'size' => [
                    4614,
                    1024000,
                ],
                'type' => [
                    'image/png',
                    'image/png',
                ],
                'error' => [
                    0,
                    0,
                ],
                'width' => [
                    640,
                    640,
                ],
                'height' => [
                    400,
                    400,
                ],
            ],
            'photos' => [
                'tmp_name' => [
                    TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                    TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                ],
                'name' => [
                    'my_avatar.png',
                    'my_bigfile.png',
                ],
                'size' => [
                    4614,
                    1024000,
                ],
                'type' => [
                    'image/png',
                    'image/png',
                ],
                'error' => [
                    1,
                    0,
                ],
                'width' => [
                    640,
                    640,
                ],
                'height' => [
                    400,
                    400,
                ],
            ],
        ];
    }

    public function testUploadedTrue()
    {
        $this->validation->setRules([
            'avatar' => 'uploaded[avatar]',
        ]);

        $this->assertTrue($this->validation->run([]));
    }

    public function testUploadedFalse()
    {
        $this->validation->setRules([
            'avatar' => 'uploaded[userfile]',
        ]);

        $this->assertFalse($this->validation->run([]));
    }

    public function testUploadedArrayReturnsTrue()
    {
        $this->validation->setRules([
            'images' => 'uploaded[images]',
        ]);

        $this->assertTrue($this->validation->run([]));
    }

    public function testUploadedArrayReturnsFalse()
    {
        $this->validation->setRules([
            'photos' => 'uploaded[photos]',
        ]);

        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSize()
    {
        $this->validation->setRules([
            'avatar' => 'max_size[avatar,100]',
        ]);

        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxSizeBigFile()
    {
        $this->validation->setRules([
            'bigfile' => 'max_size[bigfile,9999]',
        ]);

        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxSizeFail()
    {
        $this->validation->setRules([
            'avatar' => 'max_size[avatar,4]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeFailDueToUploadMaxFilesizeExceededInPhpIni()
    {
        $this->validation->setRules([
            'photo' => 'max_size[photo,100]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeBigFileFail()
    {
        $this->validation->setRules([
            'bigfile' => 'max_size[bigfile,10]',
        ]);

        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeBad()
    {
        $this->validation->setRules([
            'avatar' => 'max_size[userfile,50]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxDims()
    {
        $this->validation->setRules([
            'avatar' => 'max_dims[avatar,640,480]',
        ]);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxDimsFail()
    {
        $this->validation->setRules([
            'avatar' => 'max_dims[avatar,600,480]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxDimsBad()
    {
        $this->validation->setRules([
            'avatar' => 'max_dims[unknown,640,480]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testIsImage()
    {
        $this->validation->setRules([
            'avatar' => 'is_image[avatar]',
        ]);
        $this->assertTrue($this->validation->run([]));
    }

    public function testIsntImage()
    {
        $_FILES['stuff'] = [
            'tmp_name' => TESTPATH . '_support/Validation/uploads/abc77tz',
            'name'     => 'address.book',
            'size'     => 12345,
            'type'     => 'application/address',
            'error'    => 0,
        ];
        $this->validation->setRules([
            'avatar' => 'is_image[stuff]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testAlsoIsntImage()
    {
        $this->validation->setRules([
            'avatar' => 'is_image[unknown]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMimeTypeOk()
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[avatar,image/jpg,image/jpeg,image/gif,image/png]',
        ]);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMimeTypeNotOk()
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[avatar,application/xls,application/doc,application/ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMimeTypeImpossible()
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[unknown,application/xls,application/doc,application/ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testExtensionOk()
    {
        $this->validation->setRules([
            'avatar' => 'ext_in[avatar,jpg,jpeg,gif,png]',
        ]);
        $this->assertTrue($this->validation->run([]));
    }

    public function testExtensionNotOk()
    {
        $this->validation->setRules([
            'avatar' => 'ext_in[avatar,xls,doc,ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testExtensionImpossible()
    {
        $this->validation->setRules([
            'avatar' => 'ext_in[unknown,xls,doc,ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }
}
