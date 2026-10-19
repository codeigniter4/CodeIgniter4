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

namespace CodeIgniter\CLI;

use CodeIgniter\CLI\Exceptions\CLIException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(Color::class)]
#[Group('Others')]
final class ColorTest extends CIUnitTestCase
{
    public function testApplyWrapsTheTextInTheCodes(): void
    {
        $this->assertSame("\033[0;31mtest\033[0m", Color::apply('test', 'red'));
        $this->assertSame("\033[0;31m\033[42mtest\033[0m", Color::apply('test', 'red', 'green'));
        $this->assertSame("\033[1;37m\033[42m\033[4mtest\033[0m", Color::apply('test', 'white', 'green', 'underline'));
        $this->assertSame("\033[1;37m\033[4mtest\033[0m", Color::apply('test', 'white', '', 'underline'));
    }

    public function testApplyWithoutAForegroundEmitsOnlyTheOtherCodes(): void
    {
        $this->assertSame("\033[42mtest\033[0m", Color::apply('test', '', 'green'));
        $this->assertSame("\033[4mtest\033[0m", Color::apply('test', '', '', 'underline'));
        $this->assertSame('test', Color::apply('test', ''));
    }

    public function testApplyLeavesEmptyTextEmpty(): void
    {
        $this->assertSame('', Color::apply('', 'white', 'green', 'underline'));
    }

    #[DataProvider('provideApplyKeepsColoredSegments')]
    public function testApplyKeepsColoredSegments(string $text, string $expected): void
    {
        $this->assertSame($expected, Color::apply($text, 'red'));
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideApplyKeepsColoredSegments(): iterable
    {
        $green = "\033[0;32mgreen\033[0m";

        yield 'colored segment first' => [$green . ' red', $green . "\033[0;31m red\033[0m"];

        yield 'colored segment last' => ['red ' . $green, "\033[0;31mred \033[0m" . $green];

        yield 'colored segments around' => [$green . ' red ' . $green, $green . "\033[0;31m red \033[0m" . $green];

        yield 'only colored segments' => [$green . $green, $green . $green];

        yield 'bright segment' => ["\033[1;32mok\033[0m failed", "\033[1;32mok\033[0m\033[0;31m failed\033[0m"];

        yield 'background-only segment' => ["\033[42mok\033[0m failed", "\033[42mok\033[0m\033[0;31m failed\033[0m"];

        yield 'segment spanning lines' => ["\033[0;32mline\nbreak\033[0m end", "\033[0;32mline\nbreak\033[0m\033[0;31m end\033[0m"];

        yield 'reset without a matching segment' => ["bold\033[0m", "\033[0;31mbold\033[0m\033[0m"];
    }

    public function testApplyRejectsAnUnknownForeground(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('Invalid "foreground" color: "Foreground"');

        Color::apply('test', 'Foreground');
    }

    public function testApplyRejectsAnUnknownBackground(): void
    {
        $this->expectException(CLIException::class);
        $this->expectExceptionMessage('Invalid "background" color: "Background"');

        Color::apply('test', 'white', 'Background');
    }

    public function testWidthIgnoresTheCodes(): void
    {
        $this->assertSame(0, Color::width(''));
        $this->assertSame(7, Color::width(Color::apply('success', 'green', 'red', 'underline')));
        $this->assertSame(4, Color::width('ほげ'));
    }
}
