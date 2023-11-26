<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;

/**
 * @internal
 *
 * @group Others
 */
final class CellGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        $dirName = APPPATH . DIRECTORY_SEPARATOR . 'Cells';
        // remove dir
        if (is_dir($dirName)) {
            $files = array_diff(scandir($dirName), ['.', '..']);

            foreach ($files as $file) {
                (is_dir("{$dirName}/{$file}")) ? rmdir("{$dirName}/{$file}") : unlink("{$dirName}/{$file}");
            }
            rmdir($dirName);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! is_file($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateCell(): void
    {
        command('make:cell RecentCell');

        // Check the class was generated
        $file = APPPATH . 'Cells/RecentCell.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertStringContainsString('class RecentCell extends Cell', $this->getFileContents($file));

        // Check the view was generated
        $file = APPPATH . 'Cells/recent.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertSame("<div>\n    <!-- Your HTML here -->\n</div>\n", $this->getFileContents($file));
    }

    public function testGenerateCellSimpleName(): void
    {
        command('make:cell Another');

        // Check the class was generated
        $file = APPPATH . 'Cells/AnotherCell.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertStringContainsString('class AnotherCell extends Cell', $this->getFileContents($file));

        // Check the view was generated
        $file = APPPATH . 'Cells/another.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertSame("<div>\n    <!-- Your HTML here -->\n</div>\n", $this->getFileContents($file));
    }

    public function testGenerateCellWithCellInBetween(): void
    {
        command('make:cell PippoCellular');

        // Check the class was generated
        $file = APPPATH . 'Cells/PippoCellularCell.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertStringContainsString('class PippoCellularCell extends Cell', $this->getFileContents($file));

        // Check the view was generated
        $file = APPPATH . 'Cells/pippo_cellular.php';
        $this->assertStringContainsString('File created: ' . clean_path($file), $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
        $this->assertSame("<div>\n    <!-- Your HTML here -->\n</div>\n", $this->getFileContents($file));
    }
}
