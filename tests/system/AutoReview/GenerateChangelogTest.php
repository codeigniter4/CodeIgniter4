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

use Nexus\PHPUnit\Tachycardia\Attribute\TimeLimit;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class GenerateChangelogTest extends TestCase
{
    public function testUsageErrorWithoutArguments(): void
    {
        exec('php ./admin/generate-changelog.php 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function testUsageErrorWithInvalidVersion(): void
    {
        exec('php ./admin/generate-changelog.php 4.7 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function testRejectsVersionAlreadyInChangelog(): void
    {
        $version = $this->latestChangelogVersion();

        exec("php ./admin/generate-changelog.php {$version} 2>&1", $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString(
            "CHANGELOG.md already contains an entry for v{$version}.",
            implode("\n", $output),
        );
    }

    #[TimeLimit(10.0)]
    public function testDryRunGeneratesEntryWithoutWriting(): void
    {
        $this->skipUnlessGhIsAuthenticated();

        $version = $this->incrementPatch($this->latestChangelogVersion());

        exec("php ./admin/generate-changelog.php {$version} --dry-run 2>&1", $output, $exitCode);
        $outputString = implode("\n", $output);

        $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: {$outputString}");
        $this->assertStringContainsString(
            "## [v{$version}](https://github.com/codeigniter4/CodeIgniter4/tree/v{$version})",
            $outputString,
        );
        $this->assertMatchesRegularExpression(
            '~\[Full Changelog\]\(https://github\.com/codeigniter4/CodeIgniter4/compare/v[\d.]+\.\.\.v' . preg_quote($version, '~') . '\)~',
            $outputString,
        );
        $this->assertSame('', trim((string) exec('git status --porcelain -- CHANGELOG.md')));
    }

    #[TimeLimit(10.0)]
    public function testDryRunIncludesSecurityFixesFromDetailedChangelog(): void
    {
        $this->skipUnlessGhIsAuthenticated();

        $version = $this->latestVersionWithSecuritySection();

        if ($version === null) {
            $this->markTestSkipped('No detailed changelog with a SECURITY section found.');
        }

        exec("php ./admin/generate-changelog.php {$version} --dry-run 2>&1", $output, $exitCode);
        $outputString = implode("\n", $output);

        $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: {$outputString}");
        $this->assertMatchesRegularExpression('/### Security\n\n\* /', $outputString);
    }

    #[TimeLimit(10.0)]
    public function testAddsEntryToChangelog(): void
    {
        $this->skipUnlessGhIsAuthenticated();

        if (trim((string) exec('git status --porcelain -- CHANGELOG.md')) !== '') {
            $this->markTestSkipped('You have uncommitted changes to CHANGELOG.md that will be erased by this test.');
        }

        $version = $this->incrementPatch($this->latestChangelogVersion());

        try {
            exec("php ./admin/generate-changelog.php {$version} 2>&1", $output, $exitCode);
            $outputString = implode("\n", $output);

            $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: {$outputString}");
            $this->assertStringContainsString("Added the v{$version} entry to CHANGELOG.md.", $outputString);

            $changelog = (string) file_get_contents('./CHANGELOG.md');
            $this->assertStringStartsWith(
                "# Changelog\n\n## [v{$version}](https://github.com/codeigniter4/CodeIgniter4/tree/v{$version})",
                $changelog,
            );
        } finally {
            exec('git restore -- CHANGELOG.md');
        }
    }

    private function skipUnlessGhIsAuthenticated(): void
    {
        exec('gh auth status 2>&1', $output, $exitCode);

        if ($exitCode !== 0) {
            $this->markTestSkipped(
                'The GitHub CLI is not available or not authenticated. This test is expected '
                . 'to be skipped in CI and runs only where `gh` is authenticated, such as on '
                . 'a maintainer\'s machine.',
            );
        }
    }

    private function latestChangelogVersion(): string
    {
        $changelog = (string) file_get_contents('./CHANGELOG.md');

        if (preg_match('/^## \[v(\d+\.\d+\.\d+)\]/m', $changelog, $matches) !== 1) {
            $this->fail('Could not find a version entry in CHANGELOG.md.');
        }

        return $matches[1];
    }

    private function latestVersionWithSecuritySection(): ?string
    {
        $versions = [];
        $paths    = glob('./user_guide_src/source/changelogs/v*.rst');

        if ($paths === false) {
            return null;
        }

        foreach ($paths as $path) {
            if (preg_match('/v(\d+\.\d+\.\d+)\.rst$/', $path, $matches) !== 1) {
                continue;
            }

            if (str_contains((string) file_get_contents($path), "\nSECURITY\n")) {
                $versions[] = $matches[1];
            }
        }

        if ($versions === []) {
            return null;
        }

        usort($versions, version_compare(...));

        return end($versions);
    }

    private function incrementPatch(string $version): string
    {
        $parts = explode('.', $version);

        return sprintf('%d.%d.%d', $parts[0], $parts[1], ++$parts[2]);
    }
}
