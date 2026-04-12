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
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresOperatingSystem;

/**
 * @internal
 */
#[Group('Others')]
final class ClearDebugbarTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private int $time;

    protected function setUp(): void
    {
        parent::setUp();

        command('debugbar:clear');
        $this->resetStreamFilterBuffer();

        CLI::reset();

        $this->time = time();
        $this->createDummyDebugbarJson();
    }

    protected function tearDown(): void
    {
        command('debugbar:clear');
        $this->resetStreamFilterBuffer();

        CLI::reset();

        parent::tearDown();
    }

    private function createDummyDebugbarJson(): void
    {
        $time = $this->time;
        $path = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$time}.json";

        // create 10 dummy debugbar json files
        for ($i = 0; $i < 10; $i++) {
            $path = str_replace((string) $time, (string) ($time - $i), $path);
            file_put_contents($path, "{}\n");

            $time -= $i;
        }
    }

    public function testClearDebugbarWorks(): void
    {
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");

        command('debugbar:clear');

        $this->assertFileDoesNotExist(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json");
        $this->assertFileExists(WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . 'index.html');
        $this->assertSame(
            "\nDebugbar cleared.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    #[RequiresOperatingSystem('Darwin|Linux')]
    public function testClearDebugbarWithError(): void
    {
        $path = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json";

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

        command('debugbar:clear');

        // Restore attributes so other tests are not affected.
        if (str_starts_with(PHP_OS, 'Darwin')) {
            @exec(sprintf('chflags nouchg %s', escapeshellarg($path)));
        } else {
            @exec(sprintf('sudo chattr -i %s', escapeshellarg($path)));
        }

        $this->assertFileExists($path);
        $this->assertSame(
            "\nError deleting the debugbar JSON files.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }
}
