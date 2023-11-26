<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Validation;
use Config\Services;
use InvalidArgumentException;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 */
final class FileRulesTest extends CIUnitTestCase
{
    private Validation $validation;
    private array $config = [
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
        $this->resetServices();
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
                'size'     => 1_024_000,
                'type'     => 'image/png',
                'error'    => UPLOAD_ERR_OK,
                'width'    => 640,
                'height'   => 400,
            ],
            'photo' => [
                'tmp_name' => TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                'name'     => 'my-photo.png',
                'size'     => 4614,
                'type'     => 'image/png',
                'error'    => UPLOAD_ERR_INI_SIZE,
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
                    1_024_000,
                ],
                'type' => [
                    'image/png',
                    'image/png',
                ],
                'error' => [
                    UPLOAD_ERR_OK,
                    UPLOAD_ERR_OK,
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
                    1_024_000,
                ],
                'type' => [
                    'image/png',
                    'image/png',
                ],
                'error' => [
                    UPLOAD_ERR_INI_SIZE,
                    UPLOAD_ERR_OK,
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

    public function testUploadedTrue(): void
    {
        $this->validation->setRules(['avatar' => 'uploaded[avatar]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testUploadedFalse(): void
    {
        $this->validation->setRules(['avatar' => 'uploaded[userfile]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testUploadedArrayReturnsTrue(): void
    {
        $this->validation->setRules(['images' => 'uploaded[images]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testUploadedArrayReturnsFalse(): void
    {
        $this->validation->setRules(['photos' => 'uploaded[photos]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSize(): void
    {
        $this->validation->setRules(['avatar' => 'max_size[avatar,100]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxSizeBigFile(): void
    {
        $this->validation->setRules(['bigfile' => 'max_size[bigfile,9999]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxSizeFail(): void
    {
        $this->validation->setRules(['avatar' => 'max_size[avatar,4]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeFailDueToUploadMaxFilesizeExceededInPhpIni(): void
    {
        $this->validation->setRules(['photo' => 'max_size[photo,100]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeBigFileFail(): void
    {
        $this->validation->setRules(['bigfile' => 'max_size[bigfile,10]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeBad(): void
    {
        $this->validation->setRules(['avatar' => 'max_size[userfile,50]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxSizeInvalidParam(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid max_size parameter: "avatar.100"');

        $this->validation->setRules(['avatar' => 'max_size[avatar.100]']);
        $this->validation->run([]);
    }

    public function testMaxDims(): void
    {
        $this->validation->setRules(['avatar' => 'max_dims[avatar,640,480]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMaxDimsFail(): void
    {
        $this->validation->setRules(['avatar' => 'max_dims[avatar,600,480]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMaxDimsBad(): void
    {
        $this->validation->setRules(['avatar' => 'max_dims[unknown,640,480]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testIsImage(): void
    {
        $this->validation->setRules(['avatar' => 'is_image[avatar]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testIsntImage(): void
    {
        $_FILES['stuff'] = [
            'tmp_name' => TESTPATH . '_support/Validation/uploads/abc77tz',
            'name'     => 'address.book',
            'size'     => 12345,
            'type'     => 'application/address',
            'error'    => UPLOAD_ERR_OK,
        ];

        $this->validation->setRules(['avatar' => 'is_image[stuff]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testAlsoIsntImage(): void
    {
        $this->validation->setRules(['avatar' => 'is_image[unknown]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMimeTypeOk(): void
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[avatar,image/jpg,image/jpeg,image/gif,image/png]',
        ]);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMimeTypeNotOk(): void
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[avatar,application/xls,application/doc,application/ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMimeTypeImpossible(): void
    {
        $this->validation->setRules([
            'avatar' => 'mime_in[unknown,application/xls,application/doc,application/ppt]',
        ]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testExtensionOk(): void
    {
        $this->validation->setRules(['avatar' => 'ext_in[avatar,jpg,jpeg,gif,png]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testExtensionNotOk(): void
    {
        $this->validation->setRules(['avatar' => 'ext_in[avatar,xls,doc,ppt]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testExtensionImpossible(): void
    {
        $this->validation->setRules(['avatar' => 'ext_in[unknown,xls,doc,ppt]']);
        $this->assertFalse($this->validation->run([]));
    }
}
