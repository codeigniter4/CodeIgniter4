<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class SeederGeneratorTest extends CIUnitTestCase
{
    private $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateSeeder()
    {
        command('make:seeder cars');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/Cars.php');
    }

    public function testGenerateSeederWithOptionSuffix()
    {
        command('make:seeder cars -suffix');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/CarsSeeder.php');
    }
}
