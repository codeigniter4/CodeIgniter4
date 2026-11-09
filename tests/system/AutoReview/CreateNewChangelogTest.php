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
        $currentVersion     = $this->currentVersion;
        $newVersion         = $this->incrementVersion($currentVersion, $mode);
        $versionWithoutDots = str_replace('.', '', $newVersion);
        $changelogPath      = "./docs/changelogs/v{$newVersion}.md";
        $upgradePath        = "./docs/installation/upgrade_{$versionWithoutDots}.md";
        $trackedPathArgs    = implode(' ', array_map(escapeshellarg(...), [
            './system/CodeIgniter.php',
            './docs/.nav.yml',
            './docs/changelogs/index.md',
            './docs/installation/upgrading.md',
        ]));
        $generatedPathArgs = implode(' ', array_map(escapeshellarg(...), [
            $changelogPath,
            $upgradePath,
        ]));
        $statusCount = trim((string) exec(
            "git status --porcelain -- {$trackedPathArgs} {$generatedPathArgs} | wc -l",
        ));

        if ($statusCount !== '0') {
            $this->markTestSkipped('You have uncommitted operations that will be erased by this test.');
        }

        try {
            $output = [];

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

            $this->assertFileExists($changelogPath);
            $this->assertStringContainsString(
                "# Version {$newVersion}\n",
                $this->getContents($changelogPath),
            );
            $this->assertStringContainsString(
                "**{$newVersion} release of CodeIgniter4**",
                $this->getContents($changelogPath),
            );
            $this->assertStringContainsString(
                "- [Version {$newVersion}](v{$newVersion}.md)",
                $this->getContents('./docs/changelogs/index.md'),
            );
            $this->assertStringContainsString(
                "changelogs/v{$newVersion}.md",
                $this->getContents('./docs/.nav.yml'),
            );

            $this->assertFileExists($upgradePath);
            $this->assertStringContainsString(
                "# Upgrading from {$currentVersion} to {$newVersion}\n",
                $this->getContents($upgradePath),
            );
            $this->assertStringContainsString(
                "(upgrade_{$versionWithoutDots}.md)",
                $this->getContents('./docs/installation/upgrading.md'),
            );
            $this->assertStringContainsString(
                "installation/upgrade_{$versionWithoutDots}.md",
                $this->getContents('./docs/.nav.yml'),
            );
        } finally {
            exec("git restore -- {$trackedPathArgs}");
            exec("git restore -- {$generatedPathArgs} 2>/dev/null");
            exec("git clean -f -- {$generatedPathArgs}");
        }
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
