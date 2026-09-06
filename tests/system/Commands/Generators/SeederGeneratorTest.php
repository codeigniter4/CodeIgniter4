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
final class SeederGeneratorTest extends CIUnitTestCase
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

        foreach (['Cars.php', 'CarsSeeder.php'] as $file) {
            if (is_file(APPPATH . 'Database/Seeds/' . $file)) {
                unlink(APPPATH . 'Database/Seeds/' . $file);
            }
        }
    }

    public function testGenerateSeeder(): void
    {
        command('make:seeder cars');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Database/Seeds/Cars.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );
        $this->assertFileExists(APPPATH . 'Database/Seeds/Cars.php');
    }

    public function testGenerateSeederWithSuffix(): void
    {
        command('make:seeder cars --suffix');

        $this->assertFileExists(APPPATH . 'Database/Seeds/CarsSeeder.php');
    }
}
