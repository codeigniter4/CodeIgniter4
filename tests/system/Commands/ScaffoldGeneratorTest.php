<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class ScaffoldGeneratorTest extends CIUnitTestCase
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

    protected function getFileContents(string $filepath): string
    {
        if (! file_exists($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testCreateComponentProducesManyFiles()
    {
        command('make:scaffold people');

        $dir       = '\\' . DIRECTORY_SEPARATOR;
        $migration = "APPPATH{$dir}Database{$dir}Migrations{$dir}(.*)\\.php";
        preg_match('/' . $migration . '/u', CITestStreamFilter::$buffer, $matches);
        $matches[0] = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]);

        // Files check
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/People.php');
        $this->assertFileExists(APPPATH . 'Models/People.php');
        $this->assertStringContainsString('_People.php', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/People.php');

        // Options check
        unlink(APPPATH . 'Controllers/People.php');
        unlink(APPPATH . 'Models/People.php');
        unlink($matches[0]);
        unlink(APPPATH . 'Database/Seeds/People.php');
    }

    public function testCreateComponentWithManyOptions()
    {
        command('make:scaffold user -restful -return entity');

        $dir       = '\\' . DIRECTORY_SEPARATOR;
        $migration = "APPPATH{$dir}Database{$dir}Migrations{$dir}(.*)\\.php";
        preg_match('/' . $migration . '/u', CITestStreamFilter::$buffer, $matches);
        $matches[0] = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]);

        // Files check
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/User.php');
        $this->assertStringContainsString('_User.php', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/User.php');
        $this->assertFileExists(APPPATH . 'Entities/User.php');
        $this->assertFileExists(APPPATH . 'Models/User.php');

        // Options check
        $this->assertStringContainsString('extends ResourceController', $this->getFileContents(APPPATH . 'Controllers/User.php'));

        // Clean up
        unlink(APPPATH . 'Controllers/User.php');
        unlink($matches[0]);
        unlink(APPPATH . 'Database/Seeds/User.php');
        unlink(APPPATH . 'Entities/User.php');
        rmdir(APPPATH . 'Entities');
        unlink(APPPATH . 'Models/User.php');
    }

    public function testCreateComponentWithOptionSuffix()
    {
        command('make:scaffold order -suffix');

        $dir       = '\\' . DIRECTORY_SEPARATOR;
        $migration = "APPPATH{$dir}Database{$dir}Migrations{$dir}(.*)\\.php";
        preg_match('/' . $migration . '/u', CITestStreamFilter::$buffer, $matches);
        $matches[0] = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]);

        // Files check
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/OrderController.php');
        $this->assertStringContainsString('_OrderMigration.php', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/OrderSeeder.php');
        $this->assertFileExists(APPPATH . 'Models/OrderModel.php');

        // Clean up
        unlink(APPPATH . 'Controllers/OrderController.php');
        unlink($matches[0]);
        unlink(APPPATH . 'Database/Seeds/OrderSeeder.php');
        unlink(APPPATH . 'Models/OrderModel.php');
    }

    public function testCreateComponentWithOptionForce()
    {
        command('make:controller fixer');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertStringContainsString('extends BaseController', $this->getFileContents(APPPATH . 'Controllers/Fixer.php'));
        $this->assertFileExists(APPPATH . 'Controllers/Fixer.php');
        CITestStreamFilter::$buffer = '';

        command('make:scaffold fixer -bare -force');

        $dir       = '\\' . DIRECTORY_SEPARATOR;
        $migration = "APPPATH{$dir}Database{$dir}Migrations{$dir}(.*)\\.php";
        preg_match('/' . $migration . '/u', CITestStreamFilter::$buffer, $matches);
        $matches[0] = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]);

        // Files check
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/Fixer.php');
        $this->assertStringContainsString('_Fixer.php', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/Fixer.php');
        $this->assertFileExists(APPPATH . 'Models/Fixer.php');

        // Options check
        $this->assertStringContainsString('extends Controller', $this->getFileContents(APPPATH . 'Controllers/Fixer.php'));
        $this->assertStringContainsString('File overwritten: ', CITestStreamFilter::$buffer);

        // Clean up
        unlink(APPPATH . 'Controllers/Fixer.php');
        unlink($matches[0]);
        unlink(APPPATH . 'Database/Seeds/Fixer.php');
        unlink(APPPATH . 'Models/Fixer.php');
    }

    public function testCreateComponentWithOptionNamespace()
    {
        command('make:scaffold product -namespace App');

        $dir       = '\\' . DIRECTORY_SEPARATOR;
        $migration = "APPPATH{$dir}Database{$dir}Migrations{$dir}(.*)\\.php";
        preg_match('/' . $migration . '/u', CITestStreamFilter::$buffer, $matches);
        $matches[0] = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, $matches[0]);

        // Files check
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/Product.php');
        $this->assertStringContainsString('_Product.php', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Database/Seeds/Product.php');
        $this->assertFileExists(APPPATH . 'Models/Product.php');

        // Options check
        $this->assertStringContainsString('namespace App\Controllers;', $this->getFileContents(APPPATH . 'Controllers/Product.php'));
        $this->assertStringContainsString('namespace App\Database\Migrations;', $this->getFileContents($matches[0]));
        $this->assertStringContainsString('namespace App\Database\Seeds;', $this->getFileContents(APPPATH . 'Database/Seeds/Product.php'));
        $this->assertStringContainsString('namespace App\Models;', $this->getFileContents(APPPATH . 'Models/Product.php'));

        // Clean up
        unlink(APPPATH . 'Controllers/Product.php');
        unlink($matches[0]);
        unlink(APPPATH . 'Database/Seeds/Product.php');
        unlink(APPPATH . 'Models/Product.php');
    }
}
