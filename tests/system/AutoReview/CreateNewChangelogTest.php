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

use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class CreateNewChangelogTest extends TestCase
{
    private string $currentVersion;

    protected function setUp(): void
    {
        parent::setUp();

        exec('git describe --tags --abbrev=0 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->markTestSkipped(sprintf(
                "Unable to get the latest git tag.\nOutput: %s",
                implode("\n", $output),
            ));
        }

        // Current tag should already have the next patch docs done, so for testing purposes,
        // we will treat the next patch version as the current version.
        $this->currentVersion = $this->incrementVersion(trim($output[0], 'v'));
    }

    #[DataProvider('provideCreateNewChangelog')]
    public function testCreateNewChangelog(string $mode): void
    {
        $output = exec('git status --porcelain | wc -l');

        if ($output !== '0') {
            $this->markTestSkipped('You have uncommitted operations that will be erased by this test.');
        }

        $currentVersion = $this->currentVersion;
        $newVersion     = $this->incrementVersion($currentVersion, $mode);

        exec(
            sprintf('php ./admin/create-new-changelog.php %s %s --dry-run', $currentVersion, $newVersion),
            $output,
            $exitCode,
        );

        $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: " . implode("\n", $output));

        $this->assertStringContainsString(
            "public const CI_VERSION = '{$newVersion}-dev';",
            $this->getContents('./system/CodeIgniter.php'),
        );

        $this->assertFileExists("./user_guide_src/source/changelogs/v{$newVersion}.rst");
        $this->assertStringContainsString(
            "Version {$newVersion}",
            $this->getContents("./user_guide_src/source/changelogs/v{$newVersion}.rst"),
        );
        $this->assertStringContainsString(
            "**{$newVersion} release of CodeIgniter4**",
            $this->getContents("./user_guide_src/source/changelogs/v{$newVersion}.rst"),
        );
        $this->assertStringContainsString(
            $newVersion,
            $this->getContents('./user_guide_src/source/changelogs/index.rst'),
        );

        $versionWithoutDots = str_replace('.', '', $newVersion);
        $this->assertFileExists("./user_guide_src/source/installation/upgrade_{$versionWithoutDots}.rst");
        $this->assertStringContainsString(
            "Upgrading from {$currentVersion} to {$newVersion}",
            $this->getContents("./user_guide_src/source/installation/upgrade_{$versionWithoutDots}.rst"),
        );
        $this->assertStringContainsString(
            "upgrade_{$versionWithoutDots}",
            $this->getContents('./user_guide_src/source/installation/upgrading.rst'),
        );

        // cleanup added and modified files
        exec('git restore .');
        exec('git clean -fd');
    }

    /**
     * @return iterable<string, array{0: string}>
     */
    public static function provideCreateNewChangelog(): iterable
    {
        yield 'patch update' => ['patch'];

        yield 'minor update' => ['minor'];

        yield 'major update' => ['major'];
    }

    private function incrementVersion(string $version, string $mode = 'patch'): string
    {
        $parts = explode('.', $version);

        return match ($mode) {
            'major' => sprintf('%d.0.0', ++$parts[0]),
            'minor' => sprintf('%d.%d.0', $parts[0], ++$parts[1]),
            'patch' => sprintf('%d.%d.%d', $parts[0], $parts[1], ++$parts[2]),
            default => $this->fail('Invalid version increment mode. Use "major", "minor", or "patch".'),
        };
    }

    private function getContents(string $path): string
    {
        $contents = @file_get_contents($path);

        if ($contents === false) {
            $this->fail("Failed to read file contents from {$path}.");
        }

        return $contents;
    }
}
