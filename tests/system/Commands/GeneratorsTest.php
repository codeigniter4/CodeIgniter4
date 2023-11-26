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
final class GeneratorsTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    public function testGenerateFileCreated(): void
    {
        command('make:seeder categories');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Database/Seeds/Categories.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileExists(): void
    {
        command('make:filter items');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->resetStreamFilterBuffer();
        command('make:filter items');
        $this->assertStringContainsString('File exists: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Filters/Items.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileOverwritten(): void
    {
        command('make:controller products');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->resetStreamFilterBuffer();
        command('make:controller products -force');
        $this->assertStringContainsString('File overwritten: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Controllers/Products.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileFailsOnUnwritableDirectory(): void
    {
        if (is_windows()) {
            $this->markTestSkipped('chmod does not work as expected on Windows');
        }

        chmod(APPPATH . 'Filters', 0444);

        command('make:filter permissions');
        $this->assertStringContainsString('Error while creating file: ', $this->getStreamFilterBuffer());

        chmod(APPPATH . 'Filters', 0755);
    }

    public function testGenerateFailsOnUndefinedNamespace(): void
    {
        command('make:model cars -namespace CodeIgnite');
        $this->assertStringContainsString('Namespace "CodeIgnite" is not defined.', $this->getStreamFilterBuffer());
    }

    public function testGenerateFileInSubfolders(): void
    {
        command('make:controller admin/user');
        $file = APPPATH . 'Controllers/Admin/User.php';
        $dir  = dirname($file);
        $this->assertFileExists($file);
        $this->assertDirectoryExists($dir);
        if (is_file($file)) {
            unlink($file);
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }

    public function testSuffixingHasNoEffect(): void
    {
        command('make:foo bar --suffix');
        $file1 = APPPATH . 'Commands/Bar.php';
        $file2 = APPPATH . 'Commands/BarCommand.php';
        $dir   = dirname($file1);

        $this->assertFileExists($file1);
        $this->assertFileDoesNotExist($file2);

        if (is_file($file1)) {
            unlink($file1);
        }
        if (is_file($file2)) {
            unlink($file2);
        }
        if (is_dir($dir)) {
            rmdir($dir);
        }
    }
}
