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
final class ClearLogsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private string $date;

    protected function setUp(): void
    {
        parent::setUp();

        // test runs on other tests may log errors since default threshold
        // is now 4, so set this to a safe distance
        $this->date = date('Y-m-d', strtotime('+1 year'));
    }

    protected function createDummyLogFiles(): void
    {
        $date = $this->date;
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$date}.log";

        // create 10 dummy log files
        for ($i = 0; $i < 10; $i++) {
            $newDate = date('Y-m-d', strtotime("+1 year -{$i} day"));
            $path    = str_replace($date, $newDate, $path);
            file_put_contents($path, 'Lorem ipsum');

            $date = $newDate;
        }
    }

    public function testClearLogsWorks(): void
    {
        // test clean logs dir
        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        // test dir is now populated with logs
        $this->createDummyLogFiles();
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        command('logs:clear -force');
        $result = $this->getStreamFilterBuffer();

        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'index.html');
        $this->assertStringContainsString('Logs cleared.', $result);
    }
}
