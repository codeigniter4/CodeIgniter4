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

namespace CodeIgniter\Commands\Housekeeping;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
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

        CLI::reset();

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
        $this->assertSame(
            sprintf("\nLog files in %s cleared.\n", clean_path(WRITEPATH . 'logs')),
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    public function testClearLogsCancelsWithoutForce(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        command('logs:clear');

        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            sprintf(
                <<<'EOT'
                    Delete all log files in %s? [n, y]: n
                    Log deletion cancelled.

                    EOT,
                clean_path(WRITEPATH . 'logs'),
            ),
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()),
        );
    }

    public function testClearLogsCancelsWithoutForceWithDefaultAnswer(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['']);
        CLI::setInputOutput($io);

        $space = ' ';

        command('logs:clear');

        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            sprintf(
                <<<EOT
                    Delete all log files in %s? [n, y]:{$space}
                    Log deletion cancelled.

                    EOT,
                clean_path(WRITEPATH . 'logs'),
            ),
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()),
        );
    }

    #[DataProvider('provideClearLogsAbortsNonInteractively')]
    public function testClearLogsAbortsNonInteractively(string $flag): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        command("logs:clear {$flag}");

        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            <<<'EOT'

                Log deletion aborted: pass --force to delete log files in non-interactive mode.

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideClearLogsAbortsNonInteractively(): iterable
    {
        yield 'long form' => ['--no-interaction'];

        yield 'short form' => ['-N'];
    }

    public function testClearLogsWithoutForceButWithConfirmation(): void
    {
        $this->assertFileExists(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");

        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        command('logs:clear');

        $this->assertFileDoesNotExist(WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log");
        $this->assertSame(
            sprintf(
                <<<'EOT'
                    Delete all log files in %1$s? [n, y]: y
                    Log files in %1$s cleared.

                    EOT,
                clean_path(WRITEPATH . 'logs'),
            ),
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()),
        );
    }

    #[RequiresOperatingSystem('Darwin|Linux')]
    public function testClearLogsFailsOnChmodFailure(): void
    {
        $path = WRITEPATH . 'logs' . DIRECTORY_SEPARATOR . "log-{$this->date}.log";
        file_put_contents($path, 'Lorem ipsum');

        // Attempt to make the file itself undeletable
        chmod(dirname($path), 0555);

        command('logs:clear --force');

        // Restore attributes so other tests are not affected.
        chmod(dirname($path), 0755);

        $this->assertFileExists($path);
        $this->assertSame(
            sprintf("\nFailed to delete log files in %s.\n", clean_path(WRITEPATH . 'logs')),
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }
}
