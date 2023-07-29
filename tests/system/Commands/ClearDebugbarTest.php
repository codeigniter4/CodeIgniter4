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
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group Others
 */
final class ClearDebugbarTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private int $time;

    protected function setUp(): void
    {
        parent::setUp();

        $this->time = time();
    }

    protected function createDummyDebugbarJson(): void
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

    public function testClearDebugbarWorks(): void
    {
        // test clean debugbar dir
        $this->assertFileDoesNotExist(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");

        // test dir is now populated with json
        $this->createDummyDebugbarJson();
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");

        command('debugbar:clear');
        $result = $this->getStreamFilterBuffer();

        $this->assertFileDoesNotExist(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . '.gitkeep');
        $this->assertStringContainsString('Debugbar cleared.', $result);
    }
}
