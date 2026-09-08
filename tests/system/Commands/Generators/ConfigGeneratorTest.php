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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ConfigGeneratorTest extends CIUnitTestCase
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

        foreach (['Auth.php', 'AuthConfig.php'] as $file) {
            if (is_file(APPPATH . 'Config/' . $file)) {
                unlink(APPPATH . 'Config/' . $file);
            }
        }

        if (is_dir(APPPATH . 'Config/Sub')) {
            helper('filesystem');
            delete_files(APPPATH . 'Config/Sub', true);
            rmdir(APPPATH . 'Config/Sub');
        }

        if (is_file(SUPPORTPATH . 'Config/Auth.php')) {
            unlink(SUPPORTPATH . 'Config/Auth.php');
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    public function testGenerateConfig(): void
    {
        command('make:config auth');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Config/Auth.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );

        $content = file_get_contents(APPPATH . 'Config/Auth.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace Config;', $content);
        $this->assertStringContainsString('class Auth extends BaseConfig', $content);
    }

    public function testGenerateConfigInSubdirectory(): void
    {
        command('make:config sub/auth');

        $content = file_get_contents(APPPATH . 'Config/Sub/Auth.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace Config\\Sub;', $content);
    }

    public function testGenerateConfigWithOtherNamespaceKeepsFullNamespace(): void
    {
        command('make:config auth --namespace Tests\\\\Support');

        $content = file_get_contents(SUPPORTPATH . 'Config/Auth.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('namespace Tests\\Support\\Config;', $content);
    }

    public function testGenerateConfigWithSuffix(): void
    {
        command('make:config auth --suffix');

        $this->assertFileExists(APPPATH . 'Config/AuthConfig.php');
    }
}
