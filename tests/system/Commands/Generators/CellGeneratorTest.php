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

namespace CodeIgniter\Commands\Generators;

use CodeIgniter\CLI\CLI;
use CodeIgniter\CLI\Commands;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CellGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach ([APPPATH . 'Cells', SYSTEMPATH . 'Cells'] as $dir) {
            if (is_dir($dir)) {
                helper('filesystem');
                delete_files($dir, true, false, true);
                rmdir($dir);
            }
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    private function getContents(string $file): string
    {
        $contents = file_get_contents(APPPATH . 'Cells/' . $file);
        $this->assertIsString($contents);

        return $contents;
    }

    private function assertCellGenerated(string $class, string $view): void
    {
        $this->assertSame(
            sprintf("\nFile created: APPPATH/Cells/%s.php\nFile created: APPPATH/Cells/%s.php\n", $class, $view),
            $this->getUndecoratedBuffer(),
        );
        $this->assertStringContainsString(sprintf('class %s extends Cell', class_basename($class)), $this->getContents($class . '.php'));
        $this->assertSame("<div>\n    <!-- Your HTML here -->\n</div>\n", $this->getContents($view . '.php'));
    }

    public function testGenerateCell(): void
    {
        command('make:cell RecentCell');

        $this->assertCellGenerated('RecentCell', 'recent');
    }

    public function testGenerateCellSimpleName(): void
    {
        command('make:cell Another');

        $this->assertCellGenerated('AnotherCell', 'another');
    }

    public function testGenerateCellWithCellInBetween(): void
    {
        command('make:cell PippoCellular');

        $this->assertCellGenerated('PippoCellularCell', 'pippo_cellular');
    }

    public function testGenerateCellInSubNamespace(): void
    {
        command('make:cell admin/stats');

        $this->assertCellGenerated('Admin/StatsCell', 'Admin/stats');
        $this->assertStringContainsString('namespace App\Cells\Admin;', $this->getContents('Admin/StatsCell.php'));
    }

    public function testExistingClassStillGeneratesMissingView(): void
    {
        command('make:cell RecentCell');
        unlink(APPPATH . 'Cells/recent.php');
        $this->resetStreamFilterBuffer();

        command('make:cell RecentCell');

        $this->assertSame(
            <<<'EOT'
                File exists: "APPPATH/Cells/RecentCell.php"
                File created: APPPATH/Cells/recent.php

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testForceOverwritesBothFiles(): void
    {
        command('make:cell RecentCell');
        $this->resetStreamFilterBuffer();

        command('make:cell RecentCell --force');

        $this->assertSame(
            <<<'EOT'
                File overwritten: "APPPATH/Cells/RecentCell.php"
                File overwritten: "APPPATH/Cells/recent.php"

                EOT,
            $this->getUndecoratedBuffer(),
        );
    }

    public function testDecliningCodeIgniterNamespaceAsksOnceForBothFiles(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['n']);
        CLI::setInputOutput($io);

        $command = new CellGenerator(new Commands());
        $command->setInteractive(true);

        $this->assertSame(EXIT_SUCCESS, $command->run(['foo'], ['namespace' => 'CodeIgniter']));
        $this->assertSame(
            <<<'EOT'

                Warning: Using the "CodeIgniter" namespace will generate the file in the system directory.
                Are you sure you want to continue? [y, n]: n

                Operation has been cancelled.

                EOT,
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()),
        );
        $this->assertFileDoesNotExist(SYSTEMPATH . 'Cells' . DIRECTORY_SEPARATOR . 'FooCell.php');
        $this->assertFileDoesNotExist(SYSTEMPATH . 'Cells' . DIRECTORY_SEPARATOR . 'foo.php');
    }
}
