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

        $this->time = time();
        $this->createDummyDebugbarJson();
    }

    protected function tearDown(): void
    {
        command('debugbar:clear');
        $this->resetStreamFilterBuffer();

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
            "Debugbar cleared.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }

    #[RequiresOperatingSystem('Darwin|Linux')]
    public function testClearDebugbarWithError(): void
    {
        $path = WRITEPATH . 'debugbar' . DIRECTORY_SEPARATOR . "debugbar_{$this->time}.json";

        // Attempt to make the file itself undeletable
        chmod(dirname($path), 0555);

        command('debugbar:clear');

        // Restore attributes so other tests are not affected.
        chmod(dirname($path), 0755);

        $this->assertFileExists($path);
        $this->assertSame(
            "Error deleting the debugbar JSON files.\n",
            preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()),
        );
    }
}
