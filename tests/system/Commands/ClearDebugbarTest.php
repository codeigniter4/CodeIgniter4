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
 *
 * @group Others
 */
final class ClearDebugbarTest extends CIUnitTestCase
{
    private $streamFilter;
    private $time;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');
        $this->time                 = time();
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    protected function createDummyDebugbarJson()
    {
        $time = $this->time;
        $path = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$time}.json";

        // create 10 dummy debugbar json files
        for ($i = 0; $i < 10; $i++) {
            $path = str_replace($time, $time - $i, $path);
            file_put_contents($path, "{}\n");

            $time -= $i;
        }
    }

    public function testClearDebugbarWorks()
    {
        // test clean debugbar dir
        $this->assertFileDoesNotExist(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");

        // test dir is now populated with json
        $this->createDummyDebugbarJson();
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");

        command('debugbar:clear');
        $result = CITestStreamFilter::$buffer;

        $this->assertFileDoesNotExist(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . '.gitkeep');
        $this->assertStringContainsString('Debugbar cleared.', $result);
    }
}
