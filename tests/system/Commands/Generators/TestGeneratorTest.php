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

        CLI::reset();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        CLI::reset();

        foreach (['FooTest.php', 'system/FooTest.php', '_support/FooTest.php'] as $file) {
            if (is_file(ROOTPATH . 'tests/' . $file)) {
                unlink(ROOTPATH . 'tests/' . $file);
            }
        }

        if (is_dir(ROOTPATH . 'tests/Foo')) {
            helper('filesystem');
            delete_files(ROOTPATH . 'tests/Foo', true, false, true);
            rmdir(ROOTPATH . 'tests/Foo');
        }
    }

    private function getUndecoratedBuffer(): string
    {
        return preg_replace('/\e\[[^m]+m/', '', $this->getStreamFilterBuffer()) ?? '';
    }

    #[DataProvider('provideGenerateTestFiles')]
    public function testGenerateTestFiles(string $name, string $expectedFile, string $expectedNamespace): void
    {
        command('make:test ' . $name);

        $this->assertSame(sprintf("\nFile created: ROOTPATH/tests/%s\n", $expectedFile), $this->getUndecoratedBuffer());

        $contents = file_get_contents(ROOTPATH . 'tests/' . $expectedFile);
        $this->assertIsString($contents);
        $this->assertStringContainsString(sprintf('namespace %s;', $expectedNamespace), $contents);
        $this->assertStringContainsString(sprintf('class %s extends CIUnitTestCase', basename($expectedFile, '.php')), $contents);
    }

    /**
     * @return iterable<string, array{0: string, 1: string, 2: string}>
     */
    public static function provideGenerateTestFiles(): iterable
    {
        yield 'simple class name' => ['Foo', 'FooTest.php', 'Tests'];

        yield 'namespaced class name' => ['Foo/Bar', 'Foo/BarTest.php', 'Tests\Foo'];

        yield 'class with suffix' => ['Foo/BarTest', 'Foo/BarTest.php', 'Tests\Foo'];

        yield 'namespace style class name' => ['Foo\\\\Bar', 'Foo/BarTest.php', 'Tests\Foo'];

        yield 'fully qualified framework class' => ['CodeIgniter\\\\Foo', 'system/FooTest.php', 'CodeIgniter'];

        yield 'fully qualified support class' => ['Tests\\\\Support\\\\Foo', '_support/FooTest.php', 'Tests\Support'];

        yield 'explicit namespace' => ['Foo --namespace Tests\\\\Support', '_support/FooTest.php', 'Tests\Support'];
    }

    public function testUndefinedNamespaceFails(): void
    {
        command('make:test Foo --namespace Bogus');

        $this->assertSame("\nNamespace \"Bogus\" is not defined.\n", $this->getUndecoratedBuffer());
        $this->assertFileDoesNotExist(ROOTPATH . 'tests/FooTest.php');
    }

    public function testGenerateTestWithEmptyClassName(): void
    {
        $io = new MockInputOutput();
        $io->setInputs(['', 'Foo']);
        CLI::setInputOutput($io);

        command('make:test');

        $this->assertSame(
            "Test class name : \nThe \"Test class name\" field is required.\nTest class name : Foo\n\nFile created: ROOTPATH/tests/FooTest.php\n",
            preg_replace('/\e\[[^m]+m/', '', $io->getOutput()),
        );
        $this->assertFileExists(ROOTPATH . 'tests/FooTest.php');
    }
}
