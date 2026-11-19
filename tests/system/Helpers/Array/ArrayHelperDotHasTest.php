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
    /**
     * @var array<string, mixed>
     */
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

    public function testDotHasWithZeroKeyAndNonArrayValues(): void
    {
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.0.nonexistent', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('0', []));
        $this->assertTrue(ArrayHelper::dotHas('0', ['0' => 'value']));
        $this->assertTrue(ArrayHelper::dotHas('0', ['0' => null]));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.0.name.sub', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.1.age.sub', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.*.0.nonexistent', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.*.age.sub', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('contacts.friends.1.age.*.sub', $this->array));
        $this->assertFalse(ArrayHelper::dotHas('*.name', [['name' => 'Fred'], 'not-an-array']));
    }

    public function testDotHasWithZeroIndexAfterWildCard(): void
    {
        $array = [
            'users' => [
                ['contacts' => [['email' => 'alice@example.com']]],
                ['contacts' => [['email' => 'bob@example.com']]],
            ],
        ];

        $this->assertTrue(ArrayHelper::dotHas('users.*.contacts.0.email', $array));
        $this->assertTrue(ArrayHelper::dotHas('users.*.contacts.*.email', $array));
        $this->assertFalse(ArrayHelper::dotHas('users.*.contacts.0.nonexistent', $array));
        $this->assertFalse(ArrayHelper::dotHas('users.*.contacts.1.email', $array));

        $arrayWithMissingKey = [
            'users' => [
                ['contacts' => [['email' => 'alice@example.com']]],
                ['contacts' => [['name' => 'Bob']]],
            ],
        ];

        $this->assertFalse(ArrayHelper::dotHas('users.*.contacts.0.email', $arrayWithMissingKey));
        $this->assertFalse(ArrayHelper::dotHas('users.*.contacts.*.email', $arrayWithMissingKey));
    }
}
