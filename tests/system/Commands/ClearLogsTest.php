<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class ClearLogsTest extends CIUnitTestCase
{
    protected $streamFilter;
    protected $date;

    protected function setUp(): void
    {
        parent::setUp();

        CITestStreamFilter::$buffer = '';
        $this->streamFilter         = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter         = stream_filter_append(STDERR, 'CITestStreamFilter');

        // test runs on other tests may log errors since default threshold
        // is now 4, so set this to a safe distance
        $this->date = date('Y-m-d', strtotime('+1 year'));
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    protected function createDummyLogFiles()
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

    public function testClearLogsWorks()
    {
        // test clean logs dir
        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        // test dir is now populated with logs
        $this->createDummyLogFiles();
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        command('logs:clear -force');
        $result = CITestStreamFilter::$buffer;

        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'index.html');
        $this->assertStringContainsString('Logs cleared.', $result);
    }
}
