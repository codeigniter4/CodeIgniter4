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

namespace CodeIgniter\Commands;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\StreamFilterTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class TestGeneratorTest extends CIUnitTestCase
{
    use StreamFilterTrait;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetStreamFilterBuffer();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearTestFiles();
        $this->resetStreamFilterBuffer();
    }

    private function clearTestFiles(): void
    {
        $result = str_replace(["\033[0;32m", "\033[0m", "\n"], '', $this->getStreamFilterBuffer());

        $file = str_replace('ROOTPATH' . DIRECTORY_SEPARATOR, ROOTPATH, trim(substr($result, strlen('File created: '))));
        if (is_file($file)) {
            unlink($file);
        }

        $dir = dirname($file) . DIRECTORY_SEPARATOR;
        if (is_dir($dir) && ! in_array($dir, [TESTPATH, TESTPATH . 'system/', TESTPATH . '_support/'], true)) {
            rmdir($dir);
        }
    }

    #[DataProvider('provideGenerateTestFiles')]
    public function testGenerateTestFiles(string $name, string $expectedClass): void
    {
        command(sprintf('make:test %s', $name));

        $expectedTestFile = str_replace('/', DIRECTORY_SEPARATOR, sprintf('%stests/%s.php', ROOTPATH, $expectedClass));
        $expectedMessage  = sprintf('File created: %s', str_replace(ROOTPATH, 'ROOTPATH' . DIRECTORY_SEPARATOR, $expectedTestFile));
        $this->assertStringContainsString($expectedMessage, $this->getStreamFilterBuffer());
        $this->assertFileExists($expectedTestFile);
    }

    /**
     * @return iterable<string, array{0: string, 1: string}>
     */
    public static function provideGenerateTestFiles(): iterable
    {
        yield 'simple class name' => ['Foo', 'FooTest'];

        yield 'namespaced class name' => ['Foo/Bar', 'Foo/BarTest'];

        yield 'class with suffix' => ['Foo/BarTest', 'Foo/BarTest'];

        // the 4 slashes are needed to escape here and in the command
        yield 'namespace style class name' => ['Foo\\\\Bar', 'Foo/BarTest'];
    }
}
