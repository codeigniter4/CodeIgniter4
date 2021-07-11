<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class CommandGeneratorTest extends CIUnitTestCase
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

        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', CITestStreamFilter::$buffer);
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        $dir    = dirname($file);

        if (is_file($file)) {
            unlink($file);
        }
        if (is_dir($dir) && strpos($dir, 'Commands') !== false) {
            rmdir($dir);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! file_exists($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateCommand()
    {
        command('make:command deliver');
        $file = APPPATH . 'Commands/Deliver.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('protected $group = \'CodeIgniter\';', $contents);
        $this->assertStringContainsString('protected $name = \'command:name\';', $contents);
    }

    public function testGenerateCommandWithOptionCommand()
    {
        command('make:command deliver -command clear:sessions');
        $file = APPPATH . 'Commands/Deliver.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('protected $name = \'clear:sessions\';', $contents);
        $this->assertStringContainsString('protected $usage = \'clear:sessions [arguments] [options]\';', $contents);
    }

    public function testGenerateCommandWithOptionTypeBasic()
    {
        command('make:command deliver -type basic');
        $file = APPPATH . 'Commands/Deliver.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('protected $group = \'CodeIgniter\';', $contents);
        $this->assertStringContainsString('protected $name = \'command:name\';', $contents);
    }

    public function testGenerateCommandWithOptionTypeGenerator()
    {
        command('make:command deliver -type generator');
        $file = APPPATH . 'Commands/Deliver.php';
        $this->assertFileExists($file);
        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('protected $group = \'Generators\';', $contents);
        $this->assertStringContainsString('protected $name = \'command:name\';', $contents);
    }

    public function testGenerateCommandWithOptionGroup()
    {
        command('make:command deliver -group Deliverables');
        $file = APPPATH . 'Commands/Deliver.php';
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists($file);

        $contents = $this->getFileContents($file);
        $this->assertStringContainsString('protected $group = \'Deliverables\';', $contents);
    }

    public function testGenerateCommandWithOptionSuffix()
    {
        command('make:command publish -suffix');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Commands/PublishCommand.php';
        $this->assertFileExists($file);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4495
     */
    public function testGeneratorPreservesCase(): void
    {
        command('make:model TestModule');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Models/TestModule.php');
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4495
     */
    public function testGeneratorPreservesCaseButChangesComponentName(): void
    {
        command('make:controller TestModulecontroller');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/TestModuleController.php');
    }
}
