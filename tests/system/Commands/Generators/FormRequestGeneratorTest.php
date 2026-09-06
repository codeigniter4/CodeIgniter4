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

use CodeIgniter\CLI\CLI;
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

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        if (is_dir(APPPATH . 'Requests')) {
            helper('filesystem');
            delete_files(APPPATH . 'Requests', true);
            rmdir(APPPATH . 'Requests');
        }
    }

    public function testGenerateFormRequest(): void
    {
        command('make:request user');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Requests/User.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );
        $this->assertFileExists(APPPATH . 'Requests/User.php');

        $content = file_get_contents(APPPATH . 'Requests/User.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('Defaults to true in FormRequest. Override only when authorization', $content);
    }

    public function testGenerateFormRequestWithSuffix(): void
    {
        command('make:request user --suffix');

        $this->assertFileExists(APPPATH . 'Requests/UserRequest.php');
    }
}
