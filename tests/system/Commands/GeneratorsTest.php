<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class GeneratorsTest extends CIUnitTestCase
{
    protected $streamFilter;

    protected function setUp(): void
    {
        CITestStreamFilter::$buffer = '';

        $this->streamFilter = stream_filter_append(STDOUT, 'CITestStreamFilter');
        $this->streamFilter = stream_filter_append(STDERR, 'CITestStreamFilter');
    }

    protected function tearDown(): void
    {
        stream_filter_remove($this->streamFilter);
    }

    public function testGenerateFileCreated()
    {
        command('make:seeder categories');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Database/Seeds/Categories.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileExists()
    {
        command('make:filter items');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        CITestStreamFilter::$buffer = '';
        command('make:filter items');
        $this->assertStringContainsString('File exists: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Filters/Items.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileOverwritten()
    {
        command('make:controller products');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        CITestStreamFilter::$buffer = '';
        command('make:controller products -force');
        $this->assertStringContainsString('File overwritten: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Controllers/Products.php';
        if (is_file($file)) {
            unlink($file);
        }
    }

    public function testGenerateFileFailsOnUnwritableDirectory()
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            $this->markTestSkipped('chmod does not work as expected on Windows');
        }

        chmod(APPPATH . 'Filters', 0444);

        command('make:filter permissions');
        $this->assertStringContainsString('Error while creating file: ', CITestStreamFilter::$buffer);

        chmod(APPPATH . 'Filters', 0755);
    }

    public function testGenerateFailsOnUndefinedNamespace()
    {
        command('make:model cars -namespace CodeIgnite');
        $this->assertStringContainsString('Namespace "CodeIgnite" is not defined.', CITestStreamFilter::$buffer);
    }

    public function testGenerateFileInSubfolders()
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
