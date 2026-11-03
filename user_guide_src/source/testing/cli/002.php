<?php

use App\Commands\AppReport;
use CodeIgniter\CLI\Output;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;

final class AppReportTest extends CIUnitTestCase
{
    public function testReport(): void
    {
        $io = new MockInputOutput();

        $exitCode = (new AppReport(service('commands')))->run([], [], new Output($io));

        $this->assertSame(EXIT_SUCCESS, $exitCode);
        $this->assertStringContainsString('2 rows.', $io->getOutput());
    }
}
