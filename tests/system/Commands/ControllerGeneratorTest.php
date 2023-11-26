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
final class ControllerGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function tearDown(): void
    {
        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());
        $file   = str_replace('APPPATH' . DIRECTORY_SEPARATOR, APPPATH, trim(substr($result, 14)));
        if (is_file($file)) {
            unlink($file);
        }
    }

    protected function getFileContents(string $filepath): string
    {
        if (! is_file($filepath)) {
            return '';
        }

        return file_get_contents($filepath) ?: '';
    }

    public function testGenerateController(): void
    {
        command('make:controller user');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Controllers/User.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends BaseController', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionBare(): void
    {
        command('make:controller blog -bare');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Controllers/Blog.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends Controller', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionRestful(): void
    {
        command('make:controller order -restful');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Controllers/Order.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends ResourceController', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionRestfulPresenter(): void
    {
        command('make:controller pay -restful presenter');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $file = APPPATH . 'Controllers/Pay.php';
        $this->assertFileExists($file);
        $this->assertStringContainsString('extends ResourcePresenter', $this->getFileContents($file));
    }

    public function testGenerateControllerWithOptionSuffix(): void
    {
        command('make:controller dashboard -suffix');
        $this->assertStringContainsString('File created: ', $this->getStreamFilterBuffer());
        $this->assertFileExists(APPPATH . 'Controllers/DashboardController.php');
    }
}
