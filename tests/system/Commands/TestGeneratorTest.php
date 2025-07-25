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

use CodeIgniter\CLI\CLI;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockInputOutput;
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

        putenv('NO_COLOR=1');
        CLI::init();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->clearTestFiles();
        $this->resetStreamFilterBuffer();

        putenv('NO_COLOR');
        CLI::init();
    }

    private function clearTestFiles(): void
    {
        preg_match('/File created: (.*)/', $this->getStreamFilterBuffer(), $result);

        $file = str_replace('ROOTPATH' . DIRECTORY_SEPARATOR, ROOTPATH, $result[1] ?? '');
        if (is_file($file)) {
            unlink($file);
        }

        $dir = dirname($file) . DIRECTORY_SEPARATOR;
        if (is_dir($dir) && ! in_array($dir, ['/', TESTPATH, TESTPATH . 'system/', TESTPATH . '_support/'], true)) {
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

    public function testGenerateTestWithEmptyClassName(): void
    {
        $expectedFile = ROOTPATH . 'tests/FooTest.php';

        try {
            $io = new MockInputOutput();
            CLI::setInputOutput($io);

            // Simulate running `make:test` with no input followed by entering `Foo`
            $io->setInputs(['', 'Foo']);
            command('make:test');

            $expectedOutput = 'Test class name : ' . PHP_EOL;
            $expectedOutput .= 'The "Test class name" field is required.' . PHP_EOL;
            $expectedOutput .= 'Test class name : Foo' . PHP_EOL . PHP_EOL;
            $expectedOutput .= 'File created: ROOTPATH/tests/FooTest.php' . PHP_EOL . PHP_EOL;
            $this->assertSame($expectedOutput, $io->getOutput());
            $this->assertFileExists($expectedFile);
        } finally {
            if (is_file($expectedFile)) {
                unlink($expectedFile);
            }

            CLI::resetInputOutput();
        }
    }
}
