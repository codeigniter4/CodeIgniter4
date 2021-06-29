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
use function dirname;
use function is_dir;
use function is_file;
use function rmdir;
use function str_replace;
use function stream_filter_append;
use function stream_filter_remove;
use function substr;
use function trim;
use function unlink;
use const DIRECTORY_SEPARATOR;
use const STDERR;
use const STDOUT;

/**
 * @internal
 */
final class EntityGeneratorTest extends CIUnitTestCase
{
    protected $streamFilter;

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
        $dir    = dirname($file);
        if (is_file($file)) {
            unlink($file);
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    public function testGenerateEntity()
    {
        command('make:entity user');
        $this->assertFileExists(APPPATH . 'Entities/User.php');
    }

    public function testGenerateEntityWithOptionSuffix()
    {
        command('make:entity user -suffix');
        $this->assertFileExists(APPPATH . 'Entities/UserEntity.php');
    }
}
