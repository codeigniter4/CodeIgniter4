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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

/**
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class UpdateUpgradeGuideTest extends TestCase
{
    private string $nextVersion;
    private string $upgradePath;

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

        $parts = explode('.', trim($output[0], 'v'));

        $this->nextVersion = sprintf('%d.%d.%d', $parts[0], $parts[1], ++$parts[2]);
        $this->upgradePath = sprintf(
            './user_guide_src/source/installation/upgrade_%s.rst',
            str_replace('.', '', $this->nextVersion),
        );
    }

    public function testUsageErrorWithoutArguments(): void
    {
        exec('php ./admin/update-upgrade-guide.php 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function testUsageErrorWithInvalidVersion(): void
    {
        exec('php ./admin/update-upgrade-guide.php 4.7 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('Usage:', implode("\n", $output));
    }

    public function testErrorWhenUpgradeGuideIsMissing(): void
    {
        exec('php ./admin/update-upgrade-guide.php 9.9.8 2>&1', $output, $exitCode);

        $this->assertSame(1, $exitCode);
        $this->assertStringContainsString('is not found', implode("\n", $output));
    }

    public function testDryRunPrintsSectionsWithoutWriting(): void
    {
        $this->assertFileExists($this->upgradePath);

        exec(sprintf('php ./admin/update-upgrade-guide.php %s --dry-run 2>&1', $this->nextVersion), $output, $exitCode);
        $outputString = implode("\n", $output);

        $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: {$outputString}");
        $this->assertMatchesRegularExpression('/Checking the project space files changed since v\d+\.\d+\.\d+\./', $outputString);
        $this->assertStringContainsString('All Changes', $outputString);
        $this->assertSame('', trim((string) exec("git status --porcelain -- {$this->upgradePath}")));
    }

    public function testUpdatesUpgradeGuide(): void
    {
        $this->assertFileExists($this->upgradePath);

        if (trim((string) exec("git status --porcelain -- {$this->upgradePath}")) !== '') {
            $this->markTestSkipped('You have uncommitted changes to the upgrade guide that will be erased by this test.');
        }

        try {
            exec(sprintf('php ./admin/update-upgrade-guide.php %s 2>&1', $this->nextVersion), $output, $exitCode);
            $outputString = implode("\n", $output);

            $this->assertSame(0, $exitCode, "Script exited with code {$exitCode}. Output: {$outputString}");
            $this->assertStringContainsString('"All Changes" section', $outputString);

            $contents   = (string) file_get_contents($this->upgradePath);
            $allChanges = substr($contents, (int) strpos($contents, "All Changes\n"));

            $this->assertStringNotContainsString('- @TODO', $contents);
            $this->assertMatchesRegularExpression('/^- \S/m', $allChanges);
        } finally {
            exec("git restore -- {$this->upgradePath}");
        }
    }
}
