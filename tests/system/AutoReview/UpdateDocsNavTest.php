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
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * @internal
 */
#[CoversNothing]
#[Group('AutoReview')]
final class UpdateDocsNavTest extends TestCase
{
    private const NAV = <<<'YAML'
        nav:
          - Home: index.md
          - "Installation":
              - installation/index.md
              - "Change Logs":
                  - changelogs/index.md
                  - changelogs/v1.2.0.md
                  - changelogs/v1.0.0.md
              - "Upgrading":
                  - installation/upgrading.md
                  - installation/upgrade_100.md
        YAML;

    private string $docs;

    protected function setUp(): void
    {
        parent::setUp();

        $this->docs = sys_get_temp_dir() . '/docs-nav-' . bin2hex(random_bytes(4));

        $this->write('.nav.yml', self::NAV . "\n");
        $this->write('index.md', "# Home\n");
        $this->write('installation/index.md', "- [v1.2.0](../changelogs/v1.2.0.md)\n- [Upgrade](upgrade_100.md)\n");
        $this->write('changelogs/index.md', "- [v1.3.0](v1.3.0.md)\n- [v1.2.0](v1.2.0.md)\n- [v1.1.0](v1.1.0.md)\n- [v1.0.0](v1.0.0.md)\n");
        $this->write('installation/upgrading.md', "- [Upgrade 1.0.0](upgrade_100.md)\n- [Upgrade 1.1.0](upgrade_110.md)\n");

        foreach (['changelogs/v1.2.0.md', 'changelogs/v1.0.0.md', 'installation/upgrade_100.md'] as $page) {
            $this->write($page, "# Page\n");
        }
    }

    protected function tearDown(): void
    {
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->docs, FilesystemIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST,
        );

        foreach ($files as $file) {
            $file->isDir() ? rmdir($file->getPathname()) : unlink($file->getPathname());
        }

        rmdir($this->docs);

        parent::tearDown();
    }

    public function testErrorWhenNavIsMissing(): void
    {
        unlink($this->docs . '/.nav.yml');

        $result = $this->runScript('');

        $this->assertSame(1, $result['exitCode']);
        $this->assertStringContainsString('is not found', $result['output']);
    }

    public function testCheckPassesWhenNavIsComplete(): void
    {
        $this->write('changelogs/v1.3.0.md', "# Page\n");
        $this->write('changelogs/v1.1.0.md', "# Page\n");
        $this->write('installation/upgrade_110.md', "# Page\n");
        $this->write('.nav.yml', str_replace(
            "          - changelogs/v1.2.0.md\n          - changelogs/v1.0.0.md\n",
            "          - changelogs/v1.3.0.md\n          - changelogs/v1.2.0.md\n          - changelogs/v1.1.0.md\n          - changelogs/v1.0.0.md\n",
            self::NAV . "\n          - installation/upgrade_110.md\n",
        ));

        $result = $this->runScript('--check');

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame('', $result['output']);
    }

    public function testCheckReportsMissingAndStalePagesWithoutChangingTheNav(): void
    {
        $this->write('changelogs/v1.3.0.md', "# Page\n");
        unlink($this->docs . '/installation/upgrade_100.md');

        $result = $this->runScript('--check');

        $this->assertSame(1, $result['exitCode']);
        $this->assertStringContainsString('changelogs/v1.3.0.md is missing from the nav', $result['output']);
        $this->assertStringContainsString('The nav lists installation/upgrade_100.md, which does not exist', $result['output']);
        $this->assertSame(self::NAV . "\n", file_get_contents($this->docs . '/.nav.yml'));
    }

    public function testMissingPagesAreInsertedInTheOrderOfTheSectionIndex(): void
    {
        $this->write('changelogs/v1.3.0.md', "# Page\n");
        $this->write('changelogs/v1.1.0.md', "# Page\n");
        $this->write('installation/upgrade_110.md', "# Page\n");

        $result = $this->runScript('');

        $this->assertSame(0, $result['exitCode']);
        $this->assertSame(
            <<<'YAML'
                nav:
                  - Home: index.md
                  - "Installation":
                      - installation/index.md
                      - "Change Logs":
                          - changelogs/index.md
                          - changelogs/v1.3.0.md
                          - changelogs/v1.2.0.md
                          - changelogs/v1.1.0.md
                          - changelogs/v1.0.0.md
                      - "Upgrading":
                          - installation/upgrading.md
                          - installation/upgrade_100.md
                          - installation/upgrade_110.md

                YAML,
            file_get_contents($this->docs . '/.nav.yml'),
        );
    }

    public function testPageThatNoSectionLinksToIsAddedAtTheEnd(): void
    {
        $this->write('extra.md', "# Page\n");

        $result = $this->runScript('');

        $this->assertSame(0, $result['exitCode']);
        $this->assertStringContainsString('Move it by hand', $result['output']);
        $this->assertStringEndsWith("  - extra.md\n", (string) file_get_contents($this->docs . '/.nav.yml'));
    }

    /**
     * @return array{exitCode: int, output: string}
     */
    private function runScript(string $options): array
    {
        exec(sprintf('php ./admin/update-docs-nav.php %s --docs=%s 2>&1', $options, escapeshellarg($this->docs)), $output, $exitCode);

        return ['exitCode' => $exitCode, 'output' => implode("\n", $output)];
    }

    private function write(string $path, string $contents): void
    {
        $file = $this->docs . '/' . $path;

        if (! is_dir(dirname($file))) {
            mkdir(dirname($file), 0777, true);
        }

        file_put_contents($file, $contents);
    }
}
