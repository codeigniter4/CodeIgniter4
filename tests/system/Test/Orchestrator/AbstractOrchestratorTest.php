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

use Closure;
use CodeIgniter\Config\DotEnv;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\Test\CIUnitTestCase;
use Override;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\MockObject;
use Tests\Support\Orchestrator\OrchestratorStub;

/**
 * @internal
 */
#[CoversClass(AbstractOrchestrator::class)]
#[CoversClass(SparkResult::class)]
#[Group('Others')]
final class AbstractOrchestratorTest extends CIUnitTestCase
{
    private static string $buildPath;
    private static OrchestratorStub $orchestrator;

    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        self::$buildPath = sys_get_temp_dir() . '/ci4-orchestrator-' . bin2hex(random_bytes(4));
        mkdir(self::$buildPath . '/vendor', 0755, true);
        file_put_contents(
            self::$buildPath . '/vendor/autoload.php',
            sprintf("<?php\n\nreturn require %s;\n", var_export(ROOTPATH . 'vendor/autoload.php', true)),
        );

        self::$orchestrator = new OrchestratorStub(self::$buildPath);
        self::$orchestrator->build();
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        self::$orchestrator->clean();

        helper('filesystem');
        delete_files(self::$buildPath, true, false, true);
        rmdir(self::$buildPath);

        parent::tearDownAfterClass();
    }

    public function testBuildCreatesTheSkeleton(): void
    {
        foreach ([
            'app/Config/Paths.php',
            'public/index.php',
            'spark',
            '.env',
            'vendor/autoload.php',
            'writable',
        ] as $entry) {
            $this->assertFileExists(self::$orchestrator->getPath() . '/' . $entry);
        }
    }

    public function testBuildCopiesOnlyTheShippedWritableSkeleton(): void
    {
        $stray = WRITEPATH . 'orchestrator-stray-' . bin2hex(random_bytes(4));

        mkdir($stray);
        file_put_contents($stray . '/runtime.log', 'runtime state');
        file_put_contents($stray . '.txt', 'runtime state');

        try {
            self::$orchestrator->build();
        } finally {
            unlink($stray . '/runtime.log');
            rmdir($stray);
            unlink($stray . '.txt');
        }

        $writable = self::$orchestrator->getPath() . '/writable';

        foreach (['.htaccess', 'index.html', 'cache/index.html', 'debugbar/index.html', 'logs/index.html', 'session/index.html', 'uploads/index.html'] as $entry) {
            $this->assertFileExists($writable . '/' . $entry);
        }

        $this->assertFileDoesNotExist($writable . '/' . basename($stray));
        $this->assertFileDoesNotExist($writable . '/' . basename($stray) . '.txt');
    }

    public function testBuildPointsPathsAtTheFramework(): void
    {
        $contents = (string) file_get_contents(self::$orchestrator->getPath() . '/app/Config/Paths.php');

        $this->assertStringContainsString(var_export(rtrim(SYSTEMPATH, '\\/'), true), $contents);
        $this->assertStringNotContainsString("__DIR__ . '/../../system'", $contents);
    }

    public function testBuildPointsAFixturePathsFileAtTheFramework(): void
    {
        $root     = self::$buildPath . '/paths-fixture-root';
        $fixtures = self::$buildPath . '/paths-fixture';

        $this->createPackageRoot($root);
        mkdir($fixtures . '/app/Config', 0755, true);
        copy(ROOTPATH . 'app/Config/Paths.php', $fixtures . '/app/Config/Paths.php');

        $orchestrator = new OrchestratorStub($root, '', [], [$fixtures]);
        $orchestrator->build();

        $contents = (string) file_get_contents($orchestrator->getPath() . '/app/Config/Paths.php');

        $this->assertStringContainsString(var_export(rtrim(SYSTEMPATH, '\\/'), true), $contents);
        $this->assertSame(0, $orchestrator->spark(['list'])->exitCode);
    }

    public function testBootDefinesThePathsAndLoadsTheSkeletonInAFreshProcess(): void
    {
        $root = self::$buildPath . '/boot-root';

        $this->createPackageRoot($root);
        mkdir($root . '/tests/_support', 0755, true);
        file_put_contents($root . '/boot.php', sprintf(
            <<<'PHP'
                <?php

                require %s;
                require %s;

                (new Tests\Support\Orchestrator\OrchestratorStub(%s))->boot();

                echo json_encode([
                    'HOMEPATH'    => HOMEPATH,
                    'APPPATH'     => APPPATH,
                    'PUBLICPATH'  => PUBLICPATH,
                    'WRITEPATH'   => WRITEPATH,
                    'SYSTEMPATH'  => SYSTEMPATH,
                    'TESTPATH'    => TESTPATH,
                    'SUPPORTPATH' => SUPPORTPATH,
                    'env'         => env('orchestrator.quoted'),
                    'fixture'     => is_file(APPPATH . 'Commands/OrchestratorProbe.php'),
                ]);

                PHP,
            var_export(ROOTPATH . 'vendor/autoload.php', true),
            var_export(SUPPORTPATH . 'Orchestrator/OrchestratorStub.php', true),
            var_export($root, true),
        ));

        [$exitCode, $output] = $this->runInFreshProcess($root . '/boot.php');
        $this->assertSame(0, $exitCode, $output);

        $result = json_decode($output, true);
        $this->assertIsArray($result, $output);

        $skeleton = realpath((new OrchestratorStub($root))->getPath()) . DIRECTORY_SEPARATOR;

        $this->assertSame([
            'HOMEPATH'    => $skeleton,
            'APPPATH'     => $skeleton . 'app' . DIRECTORY_SEPARATOR,
            'PUBLICPATH'  => $skeleton . 'public' . DIRECTORY_SEPARATOR,
            'WRITEPATH'   => $skeleton . 'writable' . DIRECTORY_SEPARATOR,
            'SYSTEMPATH'  => SYSTEMPATH,
            'TESTPATH'    => $root . '/tests' . DIRECTORY_SEPARATOR,
            'SUPPORTPATH' => $root . '/tests/_support' . DIRECTORY_SEPARATOR,
            'env'         => 'a b #c "d" \\e',
            'fixture'     => true,
        ], $result);
    }

    public function testBootConsoleBootsItsOwnSkeletonThroughUtilBootstrap(): void
    {
        $root = self::$buildPath . '/console-root';

        $this->createPackageRoot($root);
        mkdir($root . '/tests/_support', 0755, true);
        file_put_contents($root . '/console.php', sprintf(
            <<<'PHP'
                <?php

                require %s;
                require %s;

                define('VENDORPATH', '/predefined/vendor/');

                $orchestrator = new Tests\Support\Orchestrator\OrchestratorStub(%s);
                $orchestrator->bootConsole(rebuild: ($argv[1] ?? '') === 'rebuild');

                echo json_encode([
                    'path'        => $orchestrator->getPath(),
                    'HOMEPATH'    => HOMEPATH,
                    'APPPATH'     => APPPATH,
                    'SYSTEMPATH'  => SYSTEMPATH,
                    'TESTPATH'    => TESTPATH,
                    'SUPPORTPATH' => SUPPORTPATH,
                    'VENDORPATH'  => VENDORPATH,
                    'env'         => env('orchestrator.quoted'),
                    'request'     => service('request') instanceof CodeIgniter\HTTP\CLIRequest,
                    'builds'      => $orchestrator->afterBuildCalls,
                ]);

                PHP,
            var_export(ROOTPATH . 'vendor/autoload.php', true),
            var_export(SUPPORTPATH . 'Orchestrator/OrchestratorStub.php', true),
            var_export($root, true),
        ));

        [$exitCode, $output] = $this->runInFreshProcess($root . '/console.php');
        $this->assertSame(0, $exitCode, $output);

        $result = json_decode($output, true);
        $this->assertIsArray($result, $output);
        $this->assertArrayHasKey('path', $result);
        $this->assertIsString($result['path']);

        $skeleton = realpath($result['path']) . DIRECTORY_SEPARATOR;
        $this->assertStringContainsString('/build/orchestrator-console', $result['path']);

        $this->assertNotSame((new OrchestratorStub($root))->getPath(), $result['path']);
        $this->assertSame([
            'path'        => $result['path'],
            'HOMEPATH'    => $skeleton,
            'APPPATH'     => $skeleton . 'app' . DIRECTORY_SEPARATOR,
            'SYSTEMPATH'  => SYSTEMPATH,
            'TESTPATH'    => $root . '/tests' . DIRECTORY_SEPARATOR,
            'SUPPORTPATH' => $root . '/tests/_support' . DIRECTORY_SEPARATOR,
            'VENDORPATH'  => '/predefined/vendor/',
            'env'         => 'a b #c "d" \\e',
            'request'     => true,
            'builds'      => 1,
        ], $result);

        file_put_contents($result['path'] . '/stale', '');

        $this->assertSame([0, 0], $this->countBuilds($this->runInFreshProcess($root . '/console.php')));
        $this->assertFileExists($result['path'] . '/stale');

        unlink($result['path'] . '/.orchestrator-built');

        $this->assertSame([0, 1], $this->countBuilds($this->runInFreshProcess($root . '/console.php')));
        $this->assertFileDoesNotExist($result['path'] . '/stale');

        file_put_contents($result['path'] . '/stale', '');

        $this->assertSame([0, 1], $this->countBuilds($this->runInFreshProcess($root . '/console.php', 'rebuild')));
        $this->assertFileDoesNotExist($result['path'] . '/stale');
        $this->assertDirectoryDoesNotExist((new OrchestratorStub($root))->getPath());
    }

    public function testBootConsoleLeavesAFailedBuildUnmarked(): void
    {
        $orchestrator = $this->createOrchestratorWithAFailingBuild(self::$buildPath . '/console-failure-root');

        try {
            $orchestrator->bootConsole();
            $this->fail('bootConsole() should have rethrown the build failure.');
        } catch (RuntimeException $e) {
            $this->assertSame('The hook failed.', $e->getMessage());
        }

        $this->assertStringContainsString('/build/orchestrator-console', $orchestrator->getPath());
        $this->assertDirectoryExists($orchestrator->getPath());
        $this->assertFileDoesNotExist($orchestrator->getPath() . '/.orchestrator-built');
    }

    public function testBootRefusesAnOrchestratorInConsoleMode(): void
    {
        $orchestrator = $this->createOrchestratorWithAFailingBuild(self::$buildPath . '/console-boot-root');

        try {
            $orchestrator->bootConsole();
        } catch (RuntimeException $e) {
            $this->assertSame('The hook failed.', $e->getMessage());
        }

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot call boot() after bootConsole().');

        $orchestrator->boot();
    }

    public function testPathSuffixesAnOverriddenSkeletonPath(): void
    {
        $root = self::$buildPath . '/console-override-root';

        $this->createPackageRoot($root);

        $orchestrator = $this->getMockBuilder(AbstractOrchestrator::class)
            ->onlyMethods(['getSkeletonPath', 'getPackageRoot', 'afterBuild'])
            ->getMock();
        $orchestrator->method('getSkeletonPath')->willReturn($root . '/skeleton/');
        $orchestrator->method('getPackageRoot')->willReturn($root);
        $orchestrator->method('afterBuild')->willThrowException(new RuntimeException('The hook failed.'));

        $this->withTokens(['ORCHESTRATOR_TEST_TOKEN' => '7'], function () use ($orchestrator, $root): void {
            $this->assertSame($root . '/skeleton-7', $orchestrator->getPath());

            try {
                $orchestrator->bootConsole();
                $this->fail('bootConsole() should have rethrown the build failure.');
            } catch (RuntimeException $e) {
                $this->assertSame('The hook failed.', $e->getMessage());
            }

            $this->assertSame($root . '/skeleton-console-7', $orchestrator->getPath());
        });
    }

    public function testGetPathConstantsDescribeTheBuiltSkeleton(): void
    {
        $skeleton = realpath(self::$orchestrator->getPath()) . DIRECTORY_SEPARATOR;

        $this->assertSame([
            'HOMEPATH'   => $skeleton,
            'CONFIGPATH' => $skeleton . 'app/Config' . DIRECTORY_SEPARATOR,
            'PUBLICPATH' => $skeleton . 'public' . DIRECTORY_SEPARATOR,
            'TESTPATH'   => self::$buildPath . '/tests' . DIRECTORY_SEPARATOR,
        ], self::$orchestrator->getPathConstants());
    }

    public function testGetPathConstantsRefuseAMissingSkeleton(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Cannot read the path constants before the orchestrated application is built.');

        (new OrchestratorStub(self::$buildPath . '/unbuilt'))->getPathConstants();
    }

    public function testBuildWritesTheEnvFile(): void
    {
        $this->assertSame(
            <<<'EOT'
                CI_ENVIRONMENT = "development"
                logger.threshold = "0"
                orchestrator.quoted = "a b #c \"d\" \\e"

                EOT,
            file_get_contents(self::$orchestrator->getPath() . '/.env'),
        );
    }

    public function testEnvValuesRoundTripThroughDotEnv(): void
    {
        $dotEnv = new DotEnv(self::$orchestrator->getPath());
        $lines  = file(self::$orchestrator->getPath() . '/.env', FILE_IGNORE_NEW_LINES);
        $values = [];

        foreach ($lines as $line) {
            [$name, $value] = $dotEnv->normaliseVariable($line);
            $values[$name]  = $value;
        }

        $this->assertSame([
            'CI_ENVIRONMENT'      => 'development',
            'logger.threshold'    => '0',
            'orchestrator.quoted' => 'a b #c "d" \\e',
        ], $values);
    }

    public function testBuildAcceptsConfigStyleEnvNames(): void
    {
        $skeleton = self::$buildPath . '/config-names';
        $env      = ['Config\App.baseURL' => 'http://localhost/', 'database.tests-x.DB_Driver' => 'SQLite3'];

        (new OrchestratorStub(self::$buildPath, $skeleton, $env))->build();

        $this->assertStringContainsString(
            "Config\\App.baseURL = \"http://localhost/\"\n",
            (string) file_get_contents($skeleton . '/.env'),
        );

        (new OrchestratorStub(self::$buildPath, $skeleton))->clean();
    }

    /**
     * @param array<string, string> $env
     */
    #[DataProvider('provideBuildRefusesUnsafeEnvSettings')]
    public function testBuildRefusesUnsafeEnvSettings(array $env): void
    {
        $skeleton     = self::$buildPath . '/unsafe-env';
        $orchestrator = new OrchestratorStub(self::$buildPath, $skeleton, $env);

        try {
            $orchestrator->build();
            $this->fail('The unsafe .env setting should have been refused.');
        } catch (RuntimeException) {
            $this->assertFileDoesNotExist($skeleton . '/.env');
        } finally {
            $orchestrator->clean();
        }
    }

    /**
     * @return iterable<string, array{array<string, string>}>
     */
    public static function provideBuildRefusesUnsafeEnvSettings(): iterable
    {
        yield 'line feed in value' => [['LABEL' => "x\nORCHESTRATOR_INJECTED=proof"]];

        yield 'carriage return in value' => [['LABEL' => "x\rY"]];

        yield 'line feed in name' => [["LABEL\nINJECTED" => 'x']];

        yield 'assignment in name' => [['ORCHESTRATOR_INJECTED=proof #' => 'x']];

        yield 'export prefix in name' => [['export LABEL' => 'x']];

        yield 'quotes in name' => [['"LABEL"' => 'x']];
    }

    public function testBuildMirrorsFixturesInOrderAndRunsTheHook(): void
    {
        $this->assertFileExists(self::$orchestrator->getPath() . '/app/Commands/OrchestratorProbe.php');
        $this->assertFileExists(self::$orchestrator->getPath() . '/extra.txt');
        $this->assertSame("second\n", file_get_contents(self::$orchestrator->getPath() . '/override.txt'));
        $this->assertGreaterThan(0, self::$orchestrator->afterBuildCalls);
    }

    public function testBuildReplacesThePreviousSkeleton(): void
    {
        self::$orchestrator->write('stale.txt', 'old');
        self::$orchestrator->build();

        $this->assertFileDoesNotExist(self::$orchestrator->getPath() . '/stale.txt');
    }

    public function testBuildAndCleanRefuseASymlinkedBuildDirectory(): void
    {
        $root    = self::$buildPath . '/linked-build-root';
        $outside = self::$buildPath . '/outside-build-dir';

        mkdir($outside . '/orchestrator', 0755, true);
        file_put_contents($outside . '/orchestrator/keep', 'keep');
        mkdir($root);

        if (! @symlink($outside, $root . '/build')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        $orchestrator = new OrchestratorStub($root);

        try {
            $operations = [
                'build'  => $orchestrator->build(...),
                'clean'  => $orchestrator->clean(...),
                'write'  => static fn () => $orchestrator->write('keep', 'proof'),
                'delete' => static fn () => $orchestrator->delete('keep'),
            ];

            foreach ($operations as $method => $operation) {
                try {
                    $operation();
                    $this->fail(sprintf('%s() through a symlinked build directory should have been refused.', $method));
                } catch (RuntimeException) {
                    $this->assertSame('keep', file_get_contents($outside . '/orchestrator/keep'));
                }
            }
        } finally {
            unlink($root . '/build');
        }
    }

    public function testBuildAndCleanRefuseASymlinkedAncestorBelowThePackageRoot(): void
    {
        $root    = self::$buildPath . '/linked-ancestor-root';
        $outside = self::$buildPath . '/outside-ancestor';

        mkdir($outside . '/nested/orchestrator', 0755, true);
        file_put_contents($outside . '/nested/orchestrator/keep', 'keep');
        mkdir($root);

        if (! @symlink($outside, $root . '/build')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        $orchestrator = new OrchestratorStub($root, $root . '/build/nested/orchestrator');

        try {
            $operations = [
                'build'  => $orchestrator->build(...),
                'clean'  => $orchestrator->clean(...),
                'write'  => static fn () => $orchestrator->write('keep', 'proof'),
                'delete' => static fn () => $orchestrator->delete('keep'),
            ];

            foreach ($operations as $method => $operation) {
                try {
                    $operation();
                    $this->fail(sprintf('%s() through a symlinked ancestor should have been refused.', $method));
                } catch (RuntimeException) {
                    $this->assertSame('keep', file_get_contents($outside . '/nested/orchestrator/keep'));
                }
            }
        } finally {
            unlink($root . '/build');
        }
    }

    public function testDeleteAndBuildUnlinkNestedSymlinksWithoutFollowingThem(): void
    {
        $outside = self::$buildPath . '/outside-nested';
        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');

        self::$orchestrator->write('tree/file.txt', 'x');

        if (! @symlink($outside, self::$orchestrator->getPath() . '/tree/link')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        self::$orchestrator->delete('tree');

        $this->assertDirectoryDoesNotExist(self::$orchestrator->getPath() . '/tree');
        $this->assertFileExists($outside . '/keep.txt');

        self::$orchestrator->write('tree/file.txt', 'x');
        symlink($outside, self::$orchestrator->getPath() . '/tree/link');

        self::$orchestrator->build();

        $this->assertDirectoryDoesNotExist(self::$orchestrator->getPath() . '/tree');
        $this->assertFileExists($outside . '/keep.txt');
    }

    public function testBuildReplacesASymlinkedSkeletonWithoutFollowingIt(): void
    {
        $root         = self::$buildPath . '/linked';
        $outside      = self::$buildPath . '/outside-build';
        $orchestrator = new OrchestratorStub($root);

        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');
        mkdir($root . '/build', 0755, true);

        if (! @symlink($outside, $orchestrator->getPath())) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        mkdir($root . '/vendor');
        file_put_contents(
            $root . '/vendor/autoload.php',
            sprintf("<?php\n\nreturn require %s;\n", var_export(ROOTPATH . 'vendor/autoload.php', true)),
        );

        $orchestrator->build();

        $this->assertFileExists($outside . '/keep.txt');
        $this->assertFalse(is_link($orchestrator->getPath()));
        $this->assertFileExists($orchestrator->getPath() . '/spark');
    }

    public function testBuildReplacesASymlinkedSkeletonGivenWithATrailingSlash(): void
    {
        $root    = self::$buildPath . '/slashed-root';
        $outside = self::$buildPath . '/outside-slashed-build';

        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');
        mkdir($root . '/vendor', 0755, true);
        file_put_contents(
            $root . '/vendor/autoload.php',
            sprintf("<?php\n\nreturn require %s;\n", var_export(ROOTPATH . 'vendor/autoload.php', true)),
        );

        if (! @symlink($outside, $root . '/skeleton')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        $orchestrator = new OrchestratorStub($root, $root . '/skeleton/');

        $orchestrator->build();

        $this->assertFileExists($outside . '/keep.txt');
        $this->assertFalse(is_link($root . '/skeleton'));
        $this->assertFileExists($root . '/skeleton/spark');
    }

    /**
     * @param array<string, string|null> $tokens
     */
    #[DataProvider('providePathIsSuffixedWithTheTestToken')]
    public function testPathIsSuffixedWithTheTestToken(array $tokens, string $suffix): void
    {
        $this->withTokens($tokens, function () use ($suffix): void {
            $this->assertSame(self::$buildPath . '/build/orchestrator' . $suffix, self::$orchestrator->getPath());
        });
    }

    /**
     * @return iterable<string, array{array<string, string|null>, string}>
     */
    public static function providePathIsSuffixedWithTheTestToken(): iterable
    {
        yield 'no token' => [['ORCHESTRATOR_TEST_TOKEN' => null, 'TEST_TOKEN' => null], ''];

        yield 'own token' => [['ORCHESTRATOR_TEST_TOKEN' => '7', 'TEST_TOKEN' => null], '-7'];

        yield 'runner token' => [['ORCHESTRATOR_TEST_TOKEN' => null, 'TEST_TOKEN' => '4'], '-4'];

        yield 'own token wins' => [['ORCHESTRATOR_TEST_TOKEN' => '7', 'TEST_TOKEN' => '4'], '-7'];

        yield 'invalid runner token ignored' => [['ORCHESTRATOR_TEST_TOKEN' => null, 'TEST_TOKEN' => '../x'], ''];
    }

    public function testPathRejectsATokenWithPathComponents(): void
    {
        $this->expectException(RuntimeException::class);

        $this->withTokens(['ORCHESTRATOR_TEST_TOKEN' => '../../elsewhere'], static function (): void {
            self::$orchestrator->getPath();
        });
    }

    public function testWriteCreatesMissingDirectoriesAndDeleteRemovesThem(): void
    {
        self::$orchestrator->write('nested/dir/file.txt', 'hello');
        $this->assertSame('hello', file_get_contents(self::$orchestrator->getPath() . '/nested/dir/file.txt'));

        self::$orchestrator->delete('nested');
        $this->assertDirectoryDoesNotExist(self::$orchestrator->getPath() . '/nested');

        self::$orchestrator->delete('nested');
    }

    public function testDeleteRemovesASymlinkWithoutFollowingIt(): void
    {
        $outside = self::$buildPath . '/outside';
        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');

        if (! @symlink($outside, self::$orchestrator->getPath() . '/link')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        self::$orchestrator->delete('link');

        $this->assertFalse(is_link(self::$orchestrator->getPath() . '/link'));
        $this->assertFileExists($outside . '/keep.txt');
    }

    public function testDeleteWithATrailingSlashRemovesTheSymlinkOnly(): void
    {
        $outside = self::$buildPath . '/outside-slash';
        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');

        if (! @symlink($outside, self::$orchestrator->getPath() . '/slashed')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        self::$orchestrator->delete('slashed/');

        $this->assertFalse(is_link(self::$orchestrator->getPath() . '/slashed'));
        $this->assertFileExists($outside . '/keep.txt');
    }

    public function testDeleteRefusesPathsThatLeaveTheSkeleton(): void
    {
        $outside = self::$buildPath . '/outside-escape';
        mkdir($outside);
        file_put_contents($outside . '/keep.txt', 'keep');

        if (! @symlink($outside, self::$orchestrator->getPath() . '/escape')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        try {
            foreach (['escape/keep.txt', '../outside-escape/keep.txt'] as $path) {
                try {
                    self::$orchestrator->delete($path);
                    $this->fail(sprintf('Deleting %s should have been refused.', $path));
                } catch (RuntimeException) {
                    $this->assertFileExists($outside . '/keep.txt');
                }
            }
        } finally {
            unlink(self::$orchestrator->getPath() . '/escape');
        }
    }

    public function testWriteRefusesTraversal(): void
    {
        try {
            self::$orchestrator->write('../../outside.txt', 'proof');
            $this->fail('Writing outside the skeleton should have been refused.');
        } catch (RuntimeException) {
            $this->assertFileDoesNotExist(self::$buildPath . '/outside.txt');
        }
    }

    public function testWriteRefusesSymlinksLeavingTheSkeleton(): void
    {
        $outside = self::$buildPath . '/outside-write';
        mkdir($outside);

        if (! @symlink($outside, self::$orchestrator->getPath() . '/wlink')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        symlink($outside . '/missing.txt', self::$orchestrator->getPath() . '/dangling');

        try {
            foreach (['wlink/redirected.txt', 'dangling'] as $path) {
                try {
                    self::$orchestrator->write($path, 'proof');
                    $this->fail(sprintf('Writing %s should have been refused.', $path));
                } catch (RuntimeException) {
                    $this->assertSame([], array_values(array_diff((array) scandir($outside), ['.', '..'])));
                }
            }
        } finally {
            unlink(self::$orchestrator->getPath() . '/wlink');
            unlink(self::$orchestrator->getPath() . '/dangling');
        }
    }

    public function testWriteAndDeleteRefuseASymlinkedSkeleton(): void
    {
        $outside = self::$buildPath . '/outside-root';
        mkdir($outside);
        file_put_contents($outside . '/file.txt', 'keep');

        if (! @symlink($outside, self::$buildPath . '/linked-skeleton')) {
            $this->markTestSkipped('Symlinks are unavailable.');
        }

        $orchestrator = new OrchestratorStub(self::$buildPath, self::$buildPath . '/linked-skeleton');

        try {
            $orchestrator->delete('file.txt');
            $this->fail('Deleting through a symlinked skeleton should have been refused.');
        } catch (RuntimeException) {
            $this->assertFileExists($outside . '/file.txt');
        }

        try {
            $orchestrator->write('new.txt', 'proof');
            $this->fail('Writing through a symlinked skeleton should have been refused.');
        } catch (RuntimeException) {
            $this->assertFileDoesNotExist($outside . '/new.txt');
        } finally {
            unlink(self::$buildPath . '/linked-skeleton');
        }
    }

    public function testCleanRemovesTheSkeletonAndToleratesItsAbsence(): void
    {
        $orchestrator = new OrchestratorStub(self::$buildPath, self::$buildPath . '/cleaned');
        $orchestrator->build();

        $orchestrator->clean();
        $this->assertDirectoryDoesNotExist(self::$buildPath . '/cleaned');

        $orchestrator->clean();
        $this->assertDirectoryDoesNotExist(self::$buildPath . '/cleaned');
    }

    public function testSparkRunsACommand(): void
    {
        $result = self::$orchestrator->spark(['list']);

        $this->assertSame(0, $result->exitCode);
        $this->assertStringContainsString('orchestrator:probe', $result->stdout);
    }

    public function testSparkForwardsStdinEnvironmentAndReportsFailure(): void
    {
        $result = self::$orchestrator->spark(['orchestrator:probe'], 'input', ['ORCHESTRATOR_PROBE' => 'set']);

        $this->assertSame(3, $result->exitCode);
        $this->assertStringEndsWith('stdin=input env=set', $result->stdout);
        $this->assertSame('probe-error', $result->stderr);
    }

    public function testSparkUnsetsVariablesGivenAsNull(): void
    {
        putenv('ORCHESTRATOR_PROBE=inherited');

        try {
            $result = self::$orchestrator->spark(['orchestrator:probe'], '', ['ORCHESTRATOR_PROBE' => null]);
        } finally {
            putenv('ORCHESTRATOR_PROBE');
        }

        $this->assertStringEndsWith('stdin= env=', $result->stdout);
    }

    public function testSparkTerminatesAProcessThatExceedsTheTimeout(): void
    {
        $started = microtime(true);
        $result  = self::$orchestrator->spark(['orchestrator:probe'], '', ['ORCHESTRATOR_SLEEP' => '1'], 1.0);

        $this->assertTrue($result->timedOut);
        $this->assertLessThan(20, microtime(true) - $started);
    }

    public function testSparkKillsAProcessThatIgnoresTermination(): void
    {
        if (! extension_loaded('pcntl')) {
            $this->markTestSkipped('The pcntl extension is required to ignore SIGTERM.');
        }

        $started = microtime(true);
        $result  = self::$orchestrator->spark(['orchestrator:probe'], '', ['ORCHESTRATOR_STUBBORN' => '1'], 1.0);

        $this->assertTrue($result->timedOut);
        $this->assertLessThan(20, microtime(true) - $started);
    }

    public function testSparkDoesNotFlagAProcessThatFinishesWithinTheTimeout(): void
    {
        $result = self::$orchestrator->spark(['orchestrator:probe'], '', [], 60.0);

        $this->assertFalse($result->timedOut);
        $this->assertSame(3, $result->exitCode);
    }

    public function testSparkDrainsLargeOutputOfBothStreams(): void
    {
        $result = self::$orchestrator->spark(
            ['orchestrator:probe'],
            str_repeat('i', 200_000),
            ['ORCHESTRATOR_FLOOD' => '1'],
        );

        $this->assertSame(3, $result->exitCode);
        $this->assertStringEndsWith(str_repeat('o', 300_000), $result->stdout);
        $this->assertStringEndsWith(str_repeat('e', 300_000), $result->stderr);
    }

    private function createOrchestratorWithAFailingBuild(string $root): AbstractOrchestrator&MockObject
    {
        $this->createPackageRoot($root);

        $orchestrator = $this->getMockBuilder(AbstractOrchestrator::class)
            ->onlyMethods(['getPackageRoot', 'afterBuild'])
            ->getMock();
        $orchestrator->method('getPackageRoot')->willReturn($root);
        $orchestrator->method('afterBuild')->willThrowException(new RuntimeException('The hook failed.'));

        return $orchestrator;
    }

    /**
     * @return array{int, string}
     */
    private function runInFreshProcess(string $script, string ...$arguments): array
    {
        exec(sprintf(
            '%s 2>&1',
            implode(' ', array_map(escapeshellarg(...), [PHP_BINARY, $script, ...$arguments])),
        ), $output, $exitCode);

        return [$exitCode, implode("\n", $output)];
    }

    /**
     * @param array{int, string} $run
     *
     * @return array{int, mixed}
     */
    private function countBuilds(array $run): array
    {
        [$exitCode, $output] = $run;

        $result = json_decode($output, true);

        return [$exitCode, is_array($result) ? $result['builds'] : $output];
    }

    private function createPackageRoot(string $root): void
    {
        mkdir($root . '/vendor', 0755, true);
        file_put_contents(
            $root . '/vendor/autoload.php',
            sprintf("<?php\n\nreturn require %s;\n", var_export(ROOTPATH . 'vendor/autoload.php', true)),
        );
    }

    /**
     * @param array<string, string|null> $tokens
     * @param Closure(): void            $test
     */
    private function withTokens(array $tokens, Closure $test): void
    {
        $originals = [];

        foreach ($tokens as $name => $value) {
            $originals[$name] = getenv($name);
            putenv($value === null ? $name : $name . '=' . $value);
        }

        try {
            $test();
        } finally {
            foreach ($originals as $name => $value) {
                putenv($value === false ? $name : $name . '=' . $value);
            }
        }
    }
}
