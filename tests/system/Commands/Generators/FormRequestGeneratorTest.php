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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FormRequestGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));

        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFormRequest(): void
    {
        command('make:request user');

        $file = APPPATH . 'Requests/User.php';

        $this->assertFileExists($file);
        $this->assertStringContainsString(
            'Defaults to true in FormRequest. Override only when authorization',
            (string) file_get_contents($file),
        );
    }

    public function testGenerateFormRequestWithOptionSuffix(): void
    {
        command('make:request admin -suffix');
        $this->assertFileExists(APPPATH . 'Requests/AdminRequest.php');
    }
}
