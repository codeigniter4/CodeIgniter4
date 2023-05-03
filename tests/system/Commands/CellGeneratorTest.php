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

    public function testGenerateCell()
    {
        command('make:cell RecentCell');

        // Check the class was generated
        $file = APPPATH . 'Cells/RecentCell.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('class RecentCell extends Cell', $contents);

        // Check the view was generated
        $file = APPPATH . 'Cells/recent_cell.php';
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->assertFileExists($file);
    }

    public function testGenerateCellSimpleNameGeneratesError()
    {
        command('make:cell Another');

        $this->assertStringContainsString('You cannot use a single word as the name of the cell.', $this->getStreamFilterBuffer());
        $this->assertFileDoesNotExist(APPPATH . 'Cells/Another.php');
    }
}
