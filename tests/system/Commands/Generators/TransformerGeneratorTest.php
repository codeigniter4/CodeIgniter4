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
final class TransformerGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    protected function setUp(): void
    {
        parent::setUp();

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        if (is_dir(APPPATH . 'Transformers')) {
            helper('filesystem');
            delete_files(APPPATH . 'Transformers', true);
            rmdir(APPPATH . 'Transformers');
        }
    }

    public function testGenerateTransformer(): void
    {
        command('make:transformer user');

        $this->assertSame(
            PHP_EOL . 'File created: ' . clean_path(APPPATH . 'Transformers/User.php') . PHP_EOL,
            $this->getUndecoratedBuffer(),
        );
        $this->assertFileExists(APPPATH . 'Transformers/User.php');

        $content = file_get_contents(APPPATH . 'Transformers/User.php');
        $this->assertIsString($content);
        $this->assertStringContainsString('extends BaseTransformer', $content);
    }

    public function testGenerateTransformerWithSuffix(): void
    {
        command('make:transformer user --suffix');

        $this->assertFileExists(APPPATH . 'Transformers/UserTransformer.php');
    }
}
