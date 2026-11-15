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

namespace CodeIgniter\AutoReview;

use FilesystemIterator;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class RunRandomTestsTest extends TestCase
{
    private string $project;
    private string $timeoutCommand;

    protected function setUp(): void
    {
        parent::setUp();

        if (is_windows()) {
            $this->markTestSkipped('The random test runner requires Bash and GNU coreutils.');
        }

        exec('command -v timeout || command -v gtimeout', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->markTestSkipped('The random test runner test requires timeout or gtimeout.');
        }

        $this->timeoutCommand = $output[0];
        $this->project        = sys_get_temp_dir() . '/ci4-random-tests-' . bin2hex(random_bytes(4));

        foreach (['.github/scripts', 'vendor/bin', 'tests/system/First', 'tests/system/Second'] as $directory) {
            mkdir($this->project . '/' . $directory, 0777, true);
        }

        copy(ROOTPATH . '.github/scripts/run-random-tests.sh', $this->project . '/.github/scripts/run-random-tests.sh');
        file_put_contents($this->project . '/.github/scripts/random-tests-config.txt', "First\nSecond\n");
        file_put_contents($this->project . '/vendor/bin/phpunit', <<<'BASH'
            #!/usr/bin/env bash
            printf '%s\n' "$1" >> calls.log
            sleep 0.05

            BASH);
        chmod($this->project . '/vendor/bin/phpunit', 0755);
    }

    protected function tearDown(): void
    {
        if (isset($this->project)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->project, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($files as $file) {
                $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
            }

            rmdir($this->project);
        }

        parent::tearDown();
    }

    #[DataProvider('provideRunsEveryComponentOnEachRepetition')]
    public function testRunsEveryComponentOnEachRepetition(int $maxJobs): void
    {
        // Bound the command so a scheduler regression fails instead of hanging PHPUnit.
        exec(sprintf(
            '%s --kill-after=1s 5s bash %s --max-jobs %d --repeat 2 --timeout 0 2>&1',
            escapeshellarg($this->timeoutCommand),
            escapeshellarg($this->project . '/.github/scripts/run-random-tests.sh'),
            $maxJobs,
        ), $output, $exitCode);
        $output = implode("\n", $output);

        $this->assertSame(0, $exitCode, $output);
        $this->assertStringContainsString('All components passed random execution tests!', $output);

        $calls = file($this->project . '/calls.log', FILE_IGNORE_NEW_LINES);

        $this->assertIsArray($calls);
        $this->assertCount(4, $calls);

        foreach (['First', 'Second'] as $component) {
            $this->assertCount(2, array_keys($calls, 'tests/system/' . $component, true));
        }
    }

    /**
     * @return iterable<string, array{int}>
     */
    public static function provideRunsEveryComponentOnEachRepetition(): iterable
    {
        yield 'one job' => [1];

        yield 'two jobs' => [2];
    }
}
