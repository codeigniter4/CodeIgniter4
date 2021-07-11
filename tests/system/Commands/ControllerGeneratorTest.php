<?php

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Filters\CITestStreamFilter;

/**
 * @internal
 */
final class ControllerGeneratorTest extends CIUnitTestCase
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
        if (is_file($file)) {
            unlink($file);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! file_exists($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateController()
    {
        command('make:controller user');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Controllers/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends BaseController', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionBare()
    {
        command('make:controller blog -bare');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Controllers/Blog.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends Controller', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionRestful()
    {
        command('make:controller order -restful');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Controllers/Order.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends ResourceController', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionRestfulPresenter()
    {
        command('make:controller pay -restful presenter');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $file = APPPATH . 'Controllers/Pay.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends ResourcePresenter', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionSuffix()
    {
        command('make:controller dashboard -suffix');
        $this->assertStringContainsString('File created: ', CITestStreamFilter::$buffer);
        $this->assertFileExists(APPPATH . 'Controllers/DashboardController.php');
    }
}
