<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Config\Services;
use CodeIgniter\EnvironmentDetector;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Validation;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @no-final
 */
#[Group('Others')]
class FileRulesTest extends CIUnitTestCase
{
    protected Validation $validation;

    /**
     * @var array<string, array<int|string, array<string, string>|string>>
     */
    protected array $config = [
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

        $this->validation = new Validation((object) $this->config, service('renderer'));
        $this->validation->reset();

        service('superglobals')->setFilesArray([
            'avatar' => [
                'tmp_name' => TESTPATH . '_support/Validation/uploads/phpUxc0ty',
                'name'     => 'my-avatar.png',
                'size'     => 4614,
                'type'     => 'image/png',
                'error'    => UPLOAD_ERR_OK,
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
        ]);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        service('superglobals')->setFilesArray([]);
        Services::resetSingle('environment');
    }

    public function testUploadedPassesForSingleValidFile(): void
    {
        $this->validation->setRules(['avatar' => 'uploaded[avatar]']);
        $this->assertTrue($this->validation->run([]));
    }

    #[PreserveGlobalState(false)]
    #[RunInSeparateProcess]
    public function testUploadedFailsInProductionWhenFileWasNotHttpUpload(): void
    {
        // Counterpart to testUploadedPassesForSingleValidFile: the same fixture
        // passes in the testing env but must fail in production, where isValid()
        // enforces is_uploaded_file(). Runs in a separate process because the
        // namespace-level is_uploaded_file() override in FileMovingTest.php would
        // otherwise leak in and make the fixture appear to be a valid upload.
        Services::injectMock('environment', new EnvironmentDetector('production'));

        $this->validation->setRules(['avatar' => 'uploaded[avatar]']);

        $this->assertFalse($this->validation->run([]));
    }

    public function testUploadedFailsWhenFileIsMissingFromRequest(): void
    {
        $this->validation->setRules(['avatar' => 'uploaded[userfile]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testUploadedPassesWhenAllFilesInArrayAreValid(): void
    {
        $this->validation->setRules(['images' => 'uploaded[images]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testUploadedFailsWhenAnyFileInArrayHasUploadError(): void
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

    public function testMinDims(): void
    {
        $this->validation->setRules(['avatar' => 'min_dims[avatar,320,240]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testMinDimsFail(): void
    {
        $this->validation->setRules(['avatar' => 'min_dims[avatar,800,600]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testMinDimsBad(): void
    {
        $this->validation->setRules(['avatar' => 'min_dims[unknown,640,480]']);
        $this->assertFalse($this->validation->run([]));
    }

    public function testIsImage(): void
    {
        $this->validation->setRules(['avatar' => 'is_image[avatar]']);
        $this->assertTrue($this->validation->run([]));
    }

    public function testIsImageFailsForMismatchedClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'shell.php');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testIsImageFailsForNonImageClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'document.pdf');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testIsImageAllowsImageClientExtensionThatDoesNotMatchContent(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar.jpg');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertTrue($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testIsImageAllowsExtensionlessClientFilename(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'blob');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertTrue($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testIsImageAllowsSvg(): void
    {
        $payload = $this->createSvgPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar.svg', 'image/svg+xml');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertTrue($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testIsImageFailsForNonImageContent(): void
    {
        $payload = $this->createPhpPayload();

        try {
            // An image extension is not enough; the file content must be an image too.
            $this->setUploadedAvatar($payload, 'fake.gif');

            $this->validation->setRules(['avatar' => 'is_image[avatar]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
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

    public function testMimeTypeFailsForMismatchedClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'shell.php');

            $this->validation->setRules(['avatar' => 'mime_in[avatar,image/gif]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testMimeTypeFailsForIncompatibleClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'document.pdf');

            $this->validation->setRules(['avatar' => 'mime_in[avatar,image/gif]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testMimeTypeFailsForAllowedClientExtensionThatDoesNotMatchContent(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar.jpg');

            $this->validation->setRules(['avatar' => 'mime_in[avatar,image/gif,image/jpeg]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testMimeTypeAllowsExtensionlessClientFilename(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'blob');

            $this->validation->setRules(['avatar' => 'mime_in[avatar,image/gif]']);
            $this->assertTrue($this->validation->run([]));
        } finally {
            unlink($payload);
        }
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

    public function testExtensionOkWithMatchingClientExtensionAndMimeType(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar.gif');

            $this->validation->setRules(['avatar' => 'ext_in[avatar,gif]']);
            $this->assertTrue($this->validation->run([]));
        } finally {
            unlink($payload);
        }
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

    public function testExtensionFailsForMismatchedClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'shell.php');

            $this->validation->setRules(['avatar' => 'ext_in[avatar,gif]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testExtensionFailsForAllowedButMimeIncompatibleClientExtension(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar.jpg');

            $this->validation->setRules(['avatar' => 'ext_in[avatar,jpg,gif]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    public function testExtensionFailsForExtensionlessClientFilename(): void
    {
        $payload = $this->createGifPayload();

        try {
            $this->setUploadedAvatar($payload, 'my-avatar');

            $this->validation->setRules(['avatar' => 'ext_in[avatar,gif]']);
            $this->assertFalse($this->validation->run([]));
        } finally {
            unlink($payload);
        }
    }

    private function createGifPayload(): string
    {
        $payload = tempnam(sys_get_temp_dir(), 'ci4-upload-poc-');
        $this->assertIsString($payload);

        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw==', true);
        $this->assertIsString($gif);

        file_put_contents($payload, $gif);

        return $payload;
    }

    private function createPhpPayload(): string
    {
        $payload = tempnam(sys_get_temp_dir(), 'ci4-upload-poc-');
        $this->assertIsString($payload);

        file_put_contents($payload, "<?php echo 'pwned'; ?>\n");

        return $payload;
    }

    private function createSvgPayload(): string
    {
        $payload = tempnam(sys_get_temp_dir(), 'ci4-upload-poc-');
        $this->assertIsString($payload);

        file_put_contents($payload, '<?xml version="1.0"?><svg xmlns="http://www.w3.org/2000/svg" width="1" height="1"></svg>');

        return $payload;
    }

    private function setUploadedAvatar(string $payload, string $name, string $clientMimeType = 'image/gif'): void
    {
        service('superglobals')->setFilesArray([
            'avatar' => [
                'tmp_name' => $payload,
                'name'     => $name,
                'size'     => filesize($payload),
                'type'     => $clientMimeType,
                'error'    => UPLOAD_ERR_OK,
            ],
        ]);
    }
}
