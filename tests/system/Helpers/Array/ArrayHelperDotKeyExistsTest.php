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
use InvalidArgumentException;

/**
 * @group Others
 *
 * @internal
 */
final class ArrayHelperDotKeyExistsTest extends CIUnitTestCase
{
    private array $array = [
        'contacts' => [
            'friends' => [
                ['name' => 'Fred Flinstone', 'age' => 20],
                ['age' => 21], // 'name' key does not exist
            ],
        ],
    ];

    public function testDotKeyExists(): void
    {
        $this->assertFalse(ArrayHelper::dotKeyExists('', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('not', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.friends', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('not.friends', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.friends.0.name', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('contacts.friends.1.name', $this->array));
    }

    public function testDotKeyExistsWithEndingWildCard(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "contacts.*"');

        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.*', $this->array));
    }

    public function testDotKeyExistsWithDoubleWildCard(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "contacts.*.*.age"');

        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.*.*.age', $this->array));
    }

    public function testDotKeyExistsWithWildCard(): void
    {
        $this->assertTrue(ArrayHelper::dotKeyExists('*.friends', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.friends.*.age', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('contacts.friends.*.name', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('*.friends.*.age', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('*.friends.*.name', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.*.0.age', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.*.1.age', $this->array));
        $this->assertTrue(ArrayHelper::dotKeyExists('contacts.*.0.name', $this->array));
        $this->assertFalse(ArrayHelper::dotKeyExists('contacts.*.1.name', $this->array));
    }
}
