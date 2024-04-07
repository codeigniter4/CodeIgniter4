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

namespace CodeIgniter\Helpers\Array;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @group Others
 *
 * @internal
 */
final class ArrayHelperSortValuesByNaturalTest extends CIUnitTestCase
{
    private array $arrayWithStringValues = [
        'apple10',
        'banana',
        'apple1',
        'банан',
        'Banana',
        'Apple',
        100000,
        'яблоко',
        1200,
        13000,
        'Банан',
        'Яблоко',
        'apple',
    ];
    private array $arrayWithArrayValues = [
        ['apple', 'Banana'],
        ['apple10', 'Apple'],
        ['Яблоко', 1200],
        [13000, 'Банан'],
        ['Apple', 'apple1'],
        ['banana', 'банан'],
        [100000, 13000],
        ['Banana', 'Яблоко'],
        ['Банан', 'banana'],
        [1200, 'apple'],
        ['apple1', 'apple10'],
        ['яблоко', 100000],
        ['банан', 'яблоко'],
    ];

    public function testSortWithStringValues(): void
    {
        shuffle($this->arrayWithStringValues);

        ArrayHelper::sortValuesByNatural($this->arrayWithStringValues);

        $this->assertSame([
            1200,
            13000,
            100000,
            'Apple',
            'Banana',
            'apple',
            'apple1',
            'apple10',
            'banana',
            'Банан',
            'Яблоко',
            'банан',
            'яблоко',
        ], $this->arrayWithStringValues);
    }

    public function testSortWithArrayValues(): void
    {
        shuffle($this->arrayWithArrayValues);

        // For first index
        ArrayHelper::sortValuesByNatural($this->arrayWithArrayValues, 0);

        $this->assertSame([
            [1200, 'apple'],
            [13000, 'Банан'],
            [100000, 13000],
            ['Apple', 'apple1'],
            ['Banana', 'Яблоко'],
            ['apple', 'Banana'],
            ['apple1', 'apple10'],
            ['apple10', 'Apple'],
            ['banana', 'банан'],
            ['Банан', 'banana'],
            ['Яблоко', 1200],
            ['банан', 'яблоко'],
            ['яблоко', 100000],
        ], $this->arrayWithArrayValues);

        shuffle($this->arrayWithArrayValues);

        // For other index
        ArrayHelper::sortValuesByNatural($this->arrayWithArrayValues, 1);

        $this->assertSame([
            ['Яблоко', 1200],
            [100000, 13000],
            ['яблоко', 100000],
            ['apple10', 'Apple'],
            ['apple', 'Banana'],
            [1200, 'apple'],
            ['Apple', 'apple1'],
            ['apple1', 'apple10'],
            ['Банан', 'banana'],
            [13000, 'Банан'],
            ['Banana', 'Яблоко'],
            ['banana', 'банан'],
            ['банан', 'яблоко'],
        ], $this->arrayWithArrayValues);
    }
}
