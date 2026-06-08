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

namespace CodeIgniter\Commands\Worker;

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class WorkerCommandsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    /**
     * @var list<string>
     */
    private array $filesToCleanup = [
        'public/frankenphp-worker.php',
        'Caddyfile',
    ];

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->cleanupFiles();

        CLI::reset();
    }

    private function getUndecoratedOutput(string $output): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $output) ?? '';
    }

    private function cleanupFiles(): void
    {
        foreach ($this->filesToCleanup as $file) {
            $path = ROOTPATH . $file;
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }

    public function testWorkerInstallCreatesFiles(): void
    {
        command('worker:install');

        $this->assertFileExists(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileExists(ROOTPATH . 'Caddyfile');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Worker mode files created successfully!', $output);
        $this->assertStringContainsString('File created:', $output);
    }

    public function testWorkerInstallSkipsExistingFilesWithoutForce(): void
    {
        command('worker:install');
        $this->resetStreamFilterBuffer();

        command('worker:install');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Worker mode files already exist', $output);
        $this->assertStringContainsString('Use --force to overwrite', $output);
    }

    public function testWorkerInstallOverwritesWithForce(): void
    {
        command('worker:install');

        $workerFile = ROOTPATH . 'public/frankenphp-worker.php';
        file_put_contents($workerFile, '<?php // Modified content');

        $this->resetStreamFilterBuffer();

        command('worker:install --force');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('File overwritten:', $output);

        $content = file_get_contents($workerFile);
        $this->assertStringNotContainsString('// Modified content', (string) $content);
        $this->assertStringContainsString('FrankenPHP Worker', (string) $content);
    }

    public function testWorkerInstallShowsNextSteps(): void
    {
        command('worker:install');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Next Steps:', $output);
        $this->assertStringContainsString('frankenphp run', $output);
        $this->assertStringContainsString('http://localhost:8080/', $output);
    }

    public function testWorkerUninstallRemovesFiles(): void
    {
        command('worker:install');
        $this->resetStreamFilterBuffer();

        command('worker:uninstall --force');

        $this->assertFileDoesNotExist(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileDoesNotExist(ROOTPATH . 'Caddyfile');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('Worker mode files removed successfully!', $output);
        $this->assertStringContainsString('File removed:', $output);
    }

    public function testWorkerUninstallWithNoFilesToRemove(): void
    {
        $this->cleanupFiles();

        command('worker:uninstall --force');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('No worker mode files found to remove', $output);
    }

    public function testWorkerUninstallListsFilesToRemove(): void
    {
        command('worker:install');
        $this->resetStreamFilterBuffer();

        command('worker:uninstall --force');

        $output = $this->getStreamFilterBuffer();
        $this->assertStringContainsString('The following files will be removed:', $output);
        $this->assertStringContainsString('public/frankenphp-worker.php', $output);
        $this->assertStringContainsString('Caddyfile', $output);
    }

    public function testWorkerUninstallCancelsWithoutForce(): void
    {
        command('worker:install');

        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        command('worker:uninstall');

        $this->assertFileExists(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileExists(ROOTPATH . 'Caddyfile');

        $output = $this->getUndecoratedOutput($io->getOutput());
        $this->assertStringContainsString('Remove the FrankenPHP worker mode files? [y, n]: n', $output);
        $this->assertStringContainsString('Uninstall cancelled.', $output);
    }

    public function testWorkerUninstallWithoutForceButConfirmed(): void
    {
        command('worker:install');

        $io = new MockInputOutput();
        $io->setInputs(['y']);
        CLI::setInputOutput($io);

        command('worker:uninstall');

        $this->assertFileDoesNotExist(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileDoesNotExist(ROOTPATH . 'Caddyfile');

        $output = $this->getUndecoratedOutput($io->getOutput());
        $this->assertStringContainsString('Remove the FrankenPHP worker mode files? [y, n]: y', $output);
        $this->assertStringContainsString('Worker mode files removed successfully!', $output);
    }

    #[DataProvider('provideWorkerUninstallAbortsNonInteractively')]
    public function testWorkerUninstallAbortsNonInteractively(string $flag): void
    {
        command('worker:install');
        $this->resetStreamFilterBuffer();

        command("worker:uninstall {$flag}");

        $this->assertFileExists(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileExists(ROOTPATH . 'Caddyfile');
        $this->assertStringContainsString(
            'Uninstall aborted: pass --force to remove worker mode files in non-interactive mode.',
            $this->getStreamFilterBuffer(),
        );
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideWorkerUninstallAbortsNonInteractively(): iterable
    {
        yield 'long form' => ['--no-interaction'];

        yield 'short form' => ['-N'];
    }

    public function testWorkerInstallAndUninstallCycle(): void
    {
        command('worker:install');
        $this->assertFileExists(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileExists(ROOTPATH . 'Caddyfile');

        command('worker:uninstall --force');
        $this->assertFileDoesNotExist(ROOTPATH . 'public/frankenphp-worker.php');
        $this->assertFileDoesNotExist(ROOTPATH . 'Caddyfile');
    }

    public function testWorkerInstallCreatesValidPHPFile(): void
    {
        command('worker:install');

        $workerFile = ROOTPATH . 'public/frankenphp-worker.php';
        $this->assertFileExists($workerFile);

        $content = file_get_contents($workerFile);
        $this->assertStringStartsWith('<?php', $content);

        $this->assertStringContainsString('frankenphp_handle_request', (string) $content);
        $this->assertStringContainsString('DatabaseConfig::reconnectForWorkerMode', (string) $content);
        $this->assertStringContainsString('DatabaseConfig::cleanupForWorkerMode', (string) $content);
    }

    public function testWorkerInstallCreatesValidCaddyfile(): void
    {
        command('worker:install');

        $caddyfile = ROOTPATH . 'Caddyfile';
        $this->assertFileExists($caddyfile);

        $content = file_get_contents($caddyfile);

        $this->assertStringContainsString('frankenphp', (string) $content);
        $this->assertStringContainsString('worker', (string) $content);
        $this->assertStringContainsString('public/frankenphp-worker.php', (string) $content);
    }
}
