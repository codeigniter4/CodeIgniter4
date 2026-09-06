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
final class ValidationGeneratorTest extends CIUnitTestCase
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

        if (is_dir(APPPATH . 'Validation')) {
            helper('filesystem');
            delete_files(APPPATH . 'Validation', true);
            rmdir(APPPATH . 'Validation');
        }
    }

    public function testGenerateValidation(): void
    {
        command('make:validation user');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Validation/User.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );
        $this->assertFileExists(APPPATH . 'Validation/User.php');
    }

    public function testGenerateValidationWithSuffix(): void
    {
        command('make:validation user --suffix');

        $this->assertFileExists(APPPATH . 'Validation/UserValidation.php');
    }
}
