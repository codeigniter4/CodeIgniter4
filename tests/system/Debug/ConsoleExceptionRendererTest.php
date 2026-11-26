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

namespace CodeIgniter\Debug;

use CodeIgniter\CLI\Output;
use CodeIgniter\CLI\Terminal;
use CodeIgniter\CLI\Verbosity;
use CodeIgniter\Debug\Inspection\CodeSnippet;
use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Frames;
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\IniTestTrait;
use CodeIgniter\Test\Mock\MockInputOutput;
use ErrorException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\UsesClass;

/**
 * @internal
 */
#[CoversClass(ConsoleExceptionRenderer::class)]
#[Group('Others')]
#[UsesClass(Backtrace::class)]
#[UsesClass(CodeSnippet::class)]
#[UsesClass(Frame::class)]
#[UsesClass(Frames::class)]
#[UsesClass(Inspector::class)]
final class ConsoleExceptionRendererTest extends CIUnitTestCase
{
    use IniTestTrait;

    public function testCliWrapsArgumentsToTheTerminalWidth(): void
    {
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('getWidth')->willReturn(25);
        $terminal->method('hasColorSupport')->willReturn(false);

        $this->backupIniValues(['zend.exception_ignore_args']);
        ini_set('zend.exception_ignore_args', '0');

        $io     = new MockInputOutput();
        $output = new Output($io, $terminal);
        $output->setVerbosity(Verbosity::Verbose);

        $argument  = str_repeat('界', 20);
        $exception = $this->runWithArgument($argument);

        ConsoleExceptionRenderer::render($exception, 'Wrapped argument', $output);

        $trace = strstr($io->getOutput(), '  1    test.php:12' . PHP_EOL);
        $this->assertNotFalse($trace);
        $firstFrame = strstr($trace, PHP_EOL . PHP_EOL, true);
        $this->assertNotFalse($firstFrame);
        $lines = array_slice(explode(PHP_EOL, $firstFrame), 1);
        $this->assertGreaterThan(1, count($lines));

        foreach ($lines as $line) {
            $this->assertStringStartsWith('       ', $line);
            $this->assertLessThanOrEqual(25, mb_strwidth($line, 'UTF-8'));
        }

        $this->assertSame(sprintf("%s()->runWithArgument('%s')", self::class, $argument), implode('', array_map(static fn (string $line): string => substr($line, 7), $lines)));
    }

    public function testCliSourceExcerptMarksTheRequestedLine(): void
    {
        $terminal = $this->createStub(Terminal::class);
        $terminal->method('getWidth')->willReturn(80);
        $terminal->method('hasColorSupport')->willReturn(false);
        $io = new MockInputOutput();

        $exception = new ErrorException('Missing controller', filename: SUPPORTPATH . 'Controllers/Hello.php', line: 16);

        ConsoleExceptionRenderer::render($exception, 'Missing controller', new Output($io, $terminal));

        $output = $io->getOutput();
        $this->assertStringContainsString('  > 16 | use CodeIgniter\Controller;' . PHP_EOL, $output);
        $this->assertStringContainsString('    18 | class Hello extends Controller' . PHP_EOL, $output);
        $this->assertStringNotContainsString(' |         return', $output);
        $this->assertSame(1, substr_count($output, '  > '));
    }

    private function runWithArgument(string $argument): ErrorException
    {
        return new ErrorException($argument, filename: ROOTPATH . 'test.php', line: 12);
    }
}
