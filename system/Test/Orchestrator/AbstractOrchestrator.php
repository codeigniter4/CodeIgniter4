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

namespace CodeIgniter\Test\Orchestrator;

use CodeIgniter\Exceptions\RuntimeException;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Base for the orchestrator of a throwaway application skeleton that a package tests against.
 */
abstract class AbstractOrchestrator
{
    /**
     * Seconds that a timed-out process gets to exit on SIGTERM before it is killed.
     */
    private const KILL_GRACE_PERIOD = 2.0;

    /**
     * Absolute path of the skeleton, suffixed with `ORCHESTRATOR_TEST_TOKEN`, or else a valid `TEST_TOKEN`, so parallel runs do not collide.
     */
    public function getPath(): string
    {
        $token = getenv('ORCHESTRATOR_TEST_TOKEN');

        if (is_string($token) && ! $this->isValidToken($token)) {
            throw new RuntimeException(
                'ORCHESTRATOR_TEST_TOKEN may only contain letters, digits, hyphens and underscores.',
            );
        }

        if ($token === false) {
            $token = getenv('TEST_TOKEN');
            $token = is_string($token) && $this->isValidToken($token) ? $token : '';
        }

        return sprintf(
            '%s/build/orchestrator%s',
            rtrim($this->getPackageRoot(), '\\/'),
            is_string($token) && $token !== '' ? '-' . $token : '',
        );
    }

    /**
     * Builds the skeleton from the framework that this class ships with, replacing any previous one.
     */
    final public function build(): void
    {
        $framework = $this->getFrameworkPath();
        $path      = $this->getPath();

        require_once $framework . '/system/Helpers/filesystem_helper.php';

        $this->ensureUnlinkedParent();
        $this->removePath($path);

        foreach (['app', 'public', 'writable'] as $directory) {
            directory_mirror($framework . '/' . $directory, $path . '/' . $directory);
        }

        $this->failUnless(copy($framework . '/spark', $path . '/spark'), 'copy spark');
        $this->pointPathsAtTheFramework();
        $this->writeAutoloader();
        $this->writeEnv();

        foreach ($this->getFixtures() as $fixtures) {
            directory_mirror($fixtures, $path);
        }

        $this->afterBuild();
    }

    /**
     * Builds the skeleton, then boots the framework in this process against it for `CIUnitTestCase` tests.
     *
     * Call it from the PHPUnit bootstrap file. The path constants are process-wide, so a process hosts one skeleton.
     */
    final public function boot(): void
    {
        $this->build();

        $path      = realpath($this->getPath()) . DIRECTORY_SEPARATOR;
        $testPath  = rtrim($this->getPackageRoot(), '\\/') . '/tests' . DIRECTORY_SEPARATOR;
        $constants = [
            'HOMEPATH'   => $path,
            'CONFIGPATH' => $path . 'app/Config' . DIRECTORY_SEPARATOR,
            'PUBLICPATH' => $path . 'public' . DIRECTORY_SEPARATOR,
            'TESTPATH'   => $testPath,
        ];

        if (is_dir($testPath . '_support')) {
            $constants['SUPPORTPATH'] = $testPath . '_support' . DIRECTORY_SEPARATOR;
        }

        foreach ($constants as $name => $value) {
            if (! defined($name)) {
                define($name, $value);
            }
        }

        require $this->getFrameworkPath() . '/system/Test/bootstrap.php';
    }

    /**
     * Runs spark in the skeleton and returns its outcome.
     *
     * @param list<string>               $arguments
     * @param array<string, string|null> $environment Variables to set, or to unset when `null`
     * @param float|null                 $timeout     Seconds after which the process is terminated, or `null` to wait indefinitely
     */
    public function spark(array $arguments, string $stdin = '', array $environment = [], ?float $timeout = null): SparkResult
    {
        $input  = tmpfile();
        $stdout = tmpfile();
        $stderr = tmpfile();

        if ($input === false || $stdout === false || $stderr === false) {
            throw new RuntimeException('Cannot create the temporary streams for spark.');
        }

        fwrite($input, $stdin);
        rewind($input);

        $process = proc_open(
            [PHP_BINARY, $this->getPath() . '/spark', ...$arguments],
            [0 => $input, 1 => $stdout, 2 => $stderr],
            $pipes,
            $this->getPath(),
            array_filter([...getenv(), ...$environment], is_string(...)),
        );

        if ($process === false) {
            throw new RuntimeException('Cannot start spark for the orchestrated application.');
        }

        [$exitCode, $timedOut] = $this->wait($process, $timeout);

        rewind($stdout);
        rewind($stderr);

        return new SparkResult(
            $exitCode,
            (string) stream_get_contents($stdout),
            (string) stream_get_contents($stderr),
            $timedOut,
        );
    }

    /**
     * Writes a file of the skeleton, creating its directory when missing.
     */
    public function write(string $path, string $contents): void
    {
        $file = $this->resolve($path);
        $root = $this->getRealRoot();

        if ($root === '') {
            throw new RuntimeException(
                sprintf('Cannot write %s before the orchestrated application is built.', $path),
            );
        }

        $this->ensureInside($file, $root, $path);

        if (! is_dir(dirname($file))) {
            $this->failUnless(mkdir(dirname($file), 0755, true), sprintf('create the directory of %s', $path));
        }

        $this->failUnless(file_put_contents($file, $contents) !== false, sprintf('write %s', $path));
    }

    /**
     * Deletes a file or a directory tree of the skeleton, if present.
     */
    public function delete(string $path): void
    {
        $target = $this->resolve($path);
        $root   = $this->getRealRoot();

        if ($root === '') {
            return;
        }

        $this->ensureInside(dirname($target), $root, $path);
        $this->removePath($target);
    }

    /**
     * Removes the skeleton, if present.
     */
    final public function clean(): void
    {
        $this->ensureUnlinkedParent();
        $this->removePath($this->getPath());
    }

    /**
     * Absolute path of the package under test, the directory holding its `vendor/autoload.php`.
     */
    abstract protected function getPackageRoot(): string;

    /**
     * Absolute paths of directories mirrored onto the skeleton, in order, after the framework files.
     *
     * @return list<string>
     */
    protected function getFixtures(): array
    {
        return [];
    }

    /**
     * Settings written to the skeleton's `.env`, keyed by name.
     *
     * @return array<non-empty-string, string>
     */
    protected function getEnv(): array
    {
        return [];
    }

    /**
     * Runs at the end of `build()`, once the skeleton is complete.
     */
    protected function afterBuild(): void
    {
    }

    /**
     * Absolute path of the framework that the skeleton is copied from.
     */
    protected function getFrameworkPath(): string
    {
        return dirname(__DIR__, 3);
    }

    private function isValidToken(string $token): bool
    {
        return preg_match('/\A[A-Za-z0-9_-]*\z/', $token) === 1;
    }

    private function resolve(string $path): string
    {
        $segments = preg_split('#[\\\\/]+#', $path, -1, PREG_SPLIT_NO_EMPTY);

        if ($segments === false || $segments === [] || in_array('..', $segments, true)) {
            throw new RuntimeException(
                sprintf('Cannot use "%s" as a path inside the orchestrated application.', $path),
            );
        }

        return $this->getPath() . '/' . implode('/', $segments);
    }

    /**
     * @return string The resolved skeleton root, or an empty string when the skeleton does not exist
     */
    private function getRealRoot(): string
    {
        $root = rtrim($this->getPath(), '\\/');

        $this->ensureUnlinkedParent();

        if (is_link($root)) {
            throw new RuntimeException(
                sprintf('Cannot use the symlinked %s as the orchestrated application.', $root),
            );
        }

        $real = realpath($root);

        return $real === false ? '' : $real;
    }

    private function ensureUnlinkedParent(): void
    {
        $parent = dirname(rtrim($this->getPath(), '\\/'));

        if (is_link($parent)) {
            throw new RuntimeException(
                sprintf('Cannot place the orchestrated application in the symlinked %s.', $parent),
            );
        }
    }

    private function ensureInside(string $path, string $root, string $given): void
    {
        while (! file_exists($path) && ! is_link($path) && dirname($path) !== $path) {
            $path = dirname($path);
        }

        $real = realpath($path);

        if ($real === false || ($real !== $root && ! str_starts_with($real, $root . DIRECTORY_SEPARATOR))) {
            throw new RuntimeException(sprintf('Cannot reach %s outside the orchestrated application.', $given));
        }
    }

    private function removePath(string $path): void
    {
        $path = rtrim($path, '\\/');

        if (is_link($path) || is_file($path)) {
            $this->removeEntry($path);
        } elseif (is_dir($path)) {
            $entries = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST,
            );

            foreach ($entries as $entry) {
                if ($entry->isDir() && ! $entry->isLink()) {
                    $this->failUnless(rmdir($entry->getPathname()), sprintf('remove %s', $entry->getPathname()));
                } else {
                    $this->removeEntry($entry->getPathname());
                }
            }

            $this->failUnless(rmdir($path), sprintf('remove %s', $path));
        }
    }

    private function removeEntry(string $path): void
    {
        $this->failUnless(
            PHP_OS_FAMILY === 'Windows' && is_dir($path) ? rmdir($path) : unlink($path),
            sprintf('remove %s', $path),
        );
    }

    /**
     * Wait for the given process to finish, with an optional timeout.
     * Returns the exit code and whether the process was terminated for exceeding the timeout.
     *
     * @param resource $process
     *
     * @return array{int, bool}
     */
    private function wait(mixed $process, ?float $timeout): array
    {
        if ($timeout === null) {
            return [proc_close($process), false];
        }

        $deadline = microtime(true) + $timeout;
        $exitCode = null;
        $timedOut = false;
        $killAt   = null;

        while ($exitCode === null) {
            $status = proc_get_status($process);

            if (! $status['running']) {
                $exitCode = $status['exitcode'];

                continue;
            }

            $now = microtime(true);

            if (! $timedOut && $now >= $deadline) {
                $timedOut = true;
                $killAt   = $now + self::KILL_GRACE_PERIOD;
                proc_terminate($process);
            } elseif ($killAt !== null && $now >= $killAt) {
                $killAt = null;
                proc_terminate($process, 9);
            }

            usleep(1000);
        }

        proc_close($process);

        return [$exitCode, $timedOut];
    }

    private function pointPathsAtTheFramework(): void
    {
        $paths    = $this->getPath() . '/app/Config/Paths.php';
        $contents = file_get_contents($paths);

        $this->failUnless($contents !== false, 'read Paths.php');

        $rewritten = str_replace(
            "__DIR__ . '/../../system'",
            var_export($this->getFrameworkPath() . '/system', true),
            (string) $contents,
            $count,
        );

        $this->failUnless($count === 1, 'find the system directory in Paths.php');
        $this->failUnless(write_file($paths, $rewritten), 'rewrite Paths.php');
    }

    private function writeAutoloader(): void
    {
        $this->failUnless(mkdir($this->getPath() . '/vendor', 0755, true), 'create vendor');
        $this->failUnless(write_file($this->getPath() . '/vendor/autoload.php', sprintf(
            "<?php\n\nreturn require %s;\n",
            var_export(rtrim($this->getPackageRoot(), '\\/') . '/vendor/autoload.php', true),
        )), 'write the autoloader shim');
    }

    private function writeEnv(): void
    {
        $contents = '';

        foreach (['CI_ENVIRONMENT' => 'development', ...$this->getEnv()] as $name => $value) {
            if (preg_match('/\A[A-Za-z0-9_.\\\\-]+\z/', $name) !== 1) {
                throw new RuntimeException(sprintf(
                    'Cannot write the .env setting "%s", whose name may only contain letters, digits, ".", "_", "-" and "\\".',
                    $name,
                ));
            }

            if (strpbrk($value, "\r\n") !== false) {
                throw new RuntimeException(
                    sprintf('Cannot write the .env setting %s, whose value contains a line break.', $name),
                );
            }

            $contents .= sprintf("%s = \"%s\"\n", $name, str_replace(['\\', '"'], ['\\\\', '\\"'], $value));
        }

        $this->failUnless(write_file($this->getPath() . '/.env', $contents), 'write .env');
    }

    private function failUnless(bool $succeeded, string $step): void
    {
        if (! $succeeded) {
            throw new RuntimeException(sprintf('Cannot %s for the orchestrated application.', $step));
        }
    }
}
