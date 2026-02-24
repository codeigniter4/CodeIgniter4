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

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayHelperDotHasTest extends CIUnitTestCase
{
    private array $array = [
        'contacts' => [
            'friends' => [
                ['name' => 'Fred Flinstone', 'age' => 20],
                ['age' => 21], // 'name' key does not exist
            ],
        ],
    ];

    public function testDotHas(): void
    {
        $this->assertFalse(ArrayHelper::dotHas('', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('not', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.friends', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('not.friends', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.friends.0.name', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.1.name', $this->array));
    }

    public function testDotHasWithEndingWildCard(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "contacts.*"');

        $this->assertTrue(ArrayHelper::dotHas('contacts.*', $this->array));
    }

    public function testDotHasWithDoubleWildCard(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "contacts.*.*.age"');

        $this->assertTrue(ArrayHelper::dotHas('contacts.*.*.age', $this->array));
    }

    public function testDotHasWithWildCard(): void
    {
        $this->assertTrue(ArrayHelper::dotHas('*.friends', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.friends.*.age', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.*.name', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('*.friends.*.age', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('*.friends.*.name', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.*.0.age', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.*.1.age', $this->array));
        $this->assertTrue(ArrayHelper::dotHas('contacts.*.0.name', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.*.1.name', $this->array));
    }
}
