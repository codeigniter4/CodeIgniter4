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
final class ArrayHelperDuplicateCheckTest extends CIUnitTestCase
{
    private array $array = [
        [
            'name' => 'Fred Flinstone',
            'age'  => 20,
        ],
        [
            'name' => 'Brad Pierce',
            'age'  => 30,
        ],
        [
            'name' => 'Fred Flinstone',
            'age'  => 70,
        ],
        [
            'name' => 'Michelle Stone',
            'age'  => 30,
        ],
        [
            'name' => 'Michael Bram',
            'age'  => 40,
        ],
    ];

    public function testSingleColumn(): void
    {
        $this->assertSame([
            2 => [
                'name' => 'Fred Flinstone',
            ],
        ], ArrayHelper::arrayDuplicatesBy('name', $this->array));
    }

    public function testMultipleColumn(): void
    {
        $this->assertSame([
            2 => [
                'name' => 'Fred Flinstone',
            ],
            3 => [
                'age' => 30,
            ],
        ], ArrayHelper::arrayDuplicatesBy(['name', 'age'], $this->array));
    }
}
