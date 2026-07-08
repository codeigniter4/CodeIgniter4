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
final class CheckPrLabelsTest extends TestCase
{
    public function testUsageErrorWithUnknownArgument(): void
    {
        exec('php ./admin/check-pr-labels.php foo 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function testUsageErrorWithInvalidVersion(): void
    {
        exec('php ./admin/check-pr-labels.php 4.7 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    #[TimeLimit(10.0)]
    public function testChecksAgainstLatestRelease(): void
    {
        $this->skipUnlessGhIsAuthenticated();

        exec('php ./admin/check-pr-labels.php 2>&1', $output, $exitCode);
        $outputString = implode("\n", $output);

        $this->assertContains($exitCode, [0, 1], "Script exited with code {$exitCode}. Output: {$outputString}");
        $this->assertMatchesRegularExpression('/Checking PRs merged since v\d+\.\d+\.\d+/', $outputString);
        $this->assertMatchesRegularExpression('/Found \d+ merged PRs\./', $outputString);
    }

    #[TimeLimit(10.0)]
    public function testChecksSinceGivenRelease(): void
    {
        $this->skipUnlessGhIsAuthenticated();

        $version = $this->latestChangelogVersion();

        exec("php ./admin/check-pr-labels.php {$version} 2>&1", $output, $exitCode);
        $outputString = implode("\n", $output);

        $this->assertContains($exitCode, [0, 1], "Script exited with code {$exitCode}. Output: {$outputString}");
        $this->assertStringContainsString("Checking PRs merged since v{$version} (", $outputString);
        $this->assertMatchesRegularExpression('/Found \d+ merged PRs\./', $outputString);
    }

    private function latestChangelogVersion(): string
    {
        $changelog = (string) file_get_contents('./CHANGELOG.md');

        if (preg_match('/^## \[v(\d+\.\d+\.\d+)\]/m', $changelog, $matches) !== 1) {
            $this->fail('Could not find a version entry in CHANGELOG.md.');
        }

        return $matches[1];
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
}
