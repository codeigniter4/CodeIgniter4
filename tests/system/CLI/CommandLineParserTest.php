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

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CommandLineParserTest extends CIUnitTestCase
{
    /**
     * @param list<string>               $tokens
     * @param list<string>               $arguments
     * @param array<string, string|null> $options
     */
    #[DataProvider('provideParseCommand')]
    public function testParseCommand(array $tokens, array $arguments, array $options): void
    {
        $parser = new CommandLineParser(['spark', ...$tokens]);

        $this->assertSame($arguments, $parser->getArguments());
        $this->assertSame($options, $parser->getOptions());
    }

    /**
     * @return iterable<string, array{0: list<string>, 1: list<string>, 2: array<string, string|null>}>
     */
    public static function provideParseCommand(): iterable
    {
        yield 'no arguments or options' => [
            [],
            [],
            [],
        ];

        yield 'arguments only' => [
            ['foo', 'bar'],
            ['foo', 'bar'],
            [],
        ];

        yield 'options only' => [
            ['--foo', '1', '--bar', '2'],
            [],
            ['foo' => '1', 'bar' => '2'],
        ];

        yield 'arguments and options' => [
            ['foo', '--bar', '2', 'baz', '--qux', '3'],
            ['foo', 'baz'],
            ['bar' => '2', 'qux' => '3'],
        ];

        yield 'options with null value' => [
            ['--foo', '--bar', '2'],
            [],
            ['foo' => null, 'bar' => '2'],
        ];

        yield 'options before double hyphen' => [
            ['b', 'c', '--key', 'value', '--', 'd'],
            ['b', 'c', 'd'],
            ['key' => 'value'],
        ];

        yield 'options after double hyphen' => [
            ['b', 'c', '--', '--key', 'value', 'd'],
            ['b', 'c', '--key', 'value', 'd'],
            [],
        ];

        yield 'options before and after double hyphen' => [
            ['b', 'c', '--key', 'value', '--', '--p2', 'value 2', 'd'],
            ['b', 'c', '--p2', 'value 2', 'd'],
            ['key' => 'value'],
        ];

        yield 'double hyphen only' => [
            ['b', 'c', '--', 'd'],
            ['b', 'c', 'd'],
            [],
        ];

        yield 'options before segments with double hyphen' => [
            ['--key', 'value', '--foo', '--', 'b', 'c', 'd'],
            ['b', 'c', 'd'],
            ['key' => 'value', 'foo' => null],
        ];

        yield 'options before segments with double hyphen and no options' => [
            ['--', 'b', 'c', 'd'],
            ['b', 'c', 'd'],
            [],
        ];
    }
}
