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
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[CoversClass(Table::class)]
#[Group('Others')]
final class TableTest extends CIUnitTestCase
{
    /**
     * @param list<array<array-key, mixed>> $tbody
     * @param list<string>                  $thead
     */
    #[DataProvider('provideRender')]
    public function testRender(array $tbody, array $thead, string $expected): void
    {
        $this->assertSame($expected, (new Table($tbody, $thead))->render());
    }

    /**
     * @return iterable<string, array{list<array<array-key, mixed>>, list<string>, string}>
     */
    public static function provideRender(): iterable
    {
        $head     = ['ID', 'Title'];
        $oneRow   = [['id' => 1, 'foo' => 'bar']];
        $manyRows = [
            ['id' => 1, 'foo' => 'bar'],
            ['id' => 2, 'foo' => 'bar * 2'],
            ['id' => 3, 'foo' => 'bar + bar + bar'],
        ];

        yield 'no rows' => [[], [], ''];

        yield 'empty row' => [[[]], [], '+' . PHP_EOL . '|  |' . PHP_EOL . '+' . PHP_EOL];

        yield 'one row without head' => [
            $oneRow,
            [],
            '+---+-----+' . PHP_EOL
            . '| 1 | bar |' . PHP_EOL
            . '+---+-----+' . PHP_EOL,
        ];

        yield 'one row with head' => [
            $oneRow,
            $head,
            '+----+-------+' . PHP_EOL
            . '| ID | Title |' . PHP_EOL
            . '+----+-------+' . PHP_EOL
            . '| 1  | bar   |' . PHP_EOL
            . '+----+-------+' . PHP_EOL,
        ];

        yield 'many rows without head' => [
            $manyRows,
            [],
            '+---+-----------------+' . PHP_EOL
            . '| 1 | bar             |' . PHP_EOL
            . '| 2 | bar * 2         |' . PHP_EOL
            . '| 3 | bar + bar + bar |' . PHP_EOL
            . '+---+-----------------+' . PHP_EOL,
        ];

        yield 'many rows with head' => [
            $manyRows,
            $head,
            '+----+-----------------+' . PHP_EOL
            . '| ID | Title           |' . PHP_EOL
            . '+----+-----------------+' . PHP_EOL
            . '| 1  | bar             |' . PHP_EOL
            . '| 2  | bar * 2         |' . PHP_EOL
            . '| 3  | bar + bar + bar |' . PHP_EOL
            . '+----+-----------------+' . PHP_EOL,
        ];

        yield 'multibyte letters' => [
            [['id' => 'ほげ', 'foo' => 'bar']],
            ['ID', 'タイトル'],
            '+------+----------+' . PHP_EOL
            . '| ID   | タイトル |' . PHP_EOL
            . '+------+----------+' . PHP_EOL
            . '| ほげ | bar      |' . PHP_EOL
            . '+------+----------+' . PHP_EOL,
        ];

        yield 'colored cells pad by visible width' => [
            [['status' => Color::apply('ok', 'green'), 'name' => 'alpha'], ['status' => 'failed', 'name' => 'b']],
            [],
            '+--------+-------+' . PHP_EOL
            . "| \033[0;32mok\033[0m     | alpha |" . PHP_EOL
            . '| failed | b     |' . PHP_EOL
            . '+--------+-------+' . PHP_EOL,
        ];
    }
}
