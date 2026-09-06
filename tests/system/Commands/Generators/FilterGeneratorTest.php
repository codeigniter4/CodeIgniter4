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
final class FilterGeneratorTest extends CIUnitTestCase
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

        foreach (['Admin.php', 'AdminFilter.php'] as $file) {
            if (is_file(APPPATH . 'Filters/' . $file)) {
                unlink(APPPATH . 'Filters/' . $file);
            }
        }
    }

    public function testGenerateFilter(): void
    {
        command('make:filter admin');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Filters/Admin.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );
        $this->assertFileExists(APPPATH . 'Filters/Admin.php');
    }

    public function testGenerateFilterWithSuffix(): void
    {
        command('make:filter admin --suffix');

        $this->assertFileExists(APPPATH . 'Filters/AdminFilter.php');
    }
}
