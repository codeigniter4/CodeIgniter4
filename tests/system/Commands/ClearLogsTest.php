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

namespace CodeIgniter\Commands;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @internal
 */
#[Group('Others')]
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

        command('logs:clear --force');
        $this->resetStreamFilterBuffer();

        $this->createDummyLogFiles();
    }

    protected function tearDown(): void
    {
        command('logs:clear --force');
        $this->resetStreamFilterBuffer();

        CLI::reset();

        parent::tearDown();
    }

    private function createDummyLogFiles(): void
    {
        $date = $this->date;
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$date}.log";

        // create 10 dummy log files
        for ($i = 0; $i < 10; $i++) {
            $newDate = date('Y-m-d', strtotime("+1 year -{$i} day"));

            $path = str_replace($date, $newDate, $path);
            file_put_contents($path, 'Lorem ipsum');

            $date = $newDate;
        }
    }

    public function testClearLogsUsingForce(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        command('logs:clear --force');

        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . 'index.html');
        $this->assertSame("Logs cleared.\n", preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()));
    }

    public function testClearLogsAbortsClearWithoutForce(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        command('logs:clear');

        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            <<<'EOT'
                Deleting logs aborted.
                If you want, use the "--force" option to force delete all log files.

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput(2) . $io->getOutput(3)),
        );
    }

    public function testClearLogsAbortsClearWithoutForceWithDefaultAnswer(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['']);
        CLI::setInputOutput($io);

        command('logs:clear');

        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            <<<'EOT'
                Deleting logs aborted.
                If you want, use the "--force" option to force delete all log files.

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput(2) . $io->getOutput(3)),
        );
    }

    public function testClearLogsWithoutForceButWithConfirmation(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        command('logs:clear');

        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame("Logs cleared.\n", preg_replace('/\e\[[^m]+m/', '', $io->getOutput(2)));
    }

    #[RequiresOperatingSystem('Darwin|Linux')]
    public function testClearLogsFailsOnChmodFailure(): void
    {
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log";
        file_put_contents($path, 'Lorem ipsum');

        // Attempt to make the file itself undeletable by setting the
        // immutable/uchg flag on supported platforms.
        $immutableSet = false;
        if (str_starts_with(PHP_OS, 'Darwin')) {
            @exec(sprintf('chflags uchg %s', escapeshellarg($path)), $output, $rc);
            $immutableSet = $rc === 0;
        } else {
            // Try chattr on Linux with sudo (for containerized environments)
            @exec('which chattr', $whichOut, $whichRc);

            if ($whichRc === 0) {
                @exec(sprintf('sudo chattr +i %s', escapeshellarg($path)), $output, $rc);
                $immutableSet = $rc === 0;
            }
        }

        if (! $immutableSet) {
            $this->markTestSkipped('Cannot set file immutability in this environment');
        }

        command('logs:clear --force');

        // Restore attributes so other tests are not affected.
        if (str_starts_with(PHP_OS, 'Darwin')) {
            @exec(sprintf('chflags nouchg %s', escapeshellarg($path)));
        } else {
            @exec(sprintf('sudo chattr -i %s', escapeshellarg($path)));
        }

        $this->assertFileExists($path);
        $this->assertSame("Error in deleting the logs files.\n", preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()));
    }
}
