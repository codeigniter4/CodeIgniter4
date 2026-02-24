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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ArrayHelperDotModifyTest extends CIUnitTestCase
{
    public function testDotSetCreatesNestedPath(): void
    {
        $array = [];

        ArrayHelper::dotSet($array, 'user.profile.id', 123);

        $this->assertSame(['user' => ['profile' => ['id' => 123]]], $array);
    }

    public function testDotSetOverwritesLeafValue(): void
    {
        $array = ['user' => ['profile' => ['id' => 123]]];

        ArrayHelper::dotSet($array, 'user.profile.id', 456);

        $this->assertSame(456, $array['user']['profile']['id']);
    }

    public function testDotSetWithEscapedDotKey(): void
    {
        $array = [];

        ArrayHelper::dotSet($array, 'config.api\.version', 'v1');

        $this->assertSame('v1', $array['config']['api.version']);
    }

    /**
     * @param array<array-key, mixed> $array
     */
    #[DataProvider('provideDotHas')]
    public function testDotHas(string $index, array $array, bool $expected): void
    {
        $this->assertSame($expected, ArrayHelper::dotHas($index, $array));
    }

    /**
     * @return iterable<string, array{index: string, array: array<array-key, mixed>, expected: bool}>
     */
    public static function provideDotHas(): iterable
    {
        yield from [
            'null value at leaf' => [
                'index'    => 'user.nickname',
                'array'    => ['user' => ['nickname' => null]],
                'expected' => true,
            ],
            'path does not exist' => [
                'index'    => 'user.email',
                'array'    => ['user' => ['id' => 123]],
                'expected' => false,
            ],
            'non-existent numeric key' => [
                'index'    => '0.name',
                'array'    => ['other' => 'x'],
                'expected' => false,
            ],
            'existing numeric key' => [
                'index'    => '0.name',
                'array'    => [['name' => 'a']],
                'expected' => true,
            ],
            'zero value at leaf' => [
                'index'    => 'user.score',
                'array'    => ['user' => ['score' => 0]],
                'expected' => true,
            ],
            'string zero at leaf' => [
                'index'    => 'user.code',
                'array'    => ['user' => ['code' => '0']],
                'expected' => true,
            ],
            'escaped dot in key' => [
                'index'    => 'config.api\.version',
                'array'    => ['config' => ['api.version' => 'v1']],
                'expected' => true,
            ],
            'escaped dot key does not exist' => [
                'index'    => 'config.api\.version',
                'array'    => ['config' => ['api' => ['version' => 'v1']]],
                'expected' => false,
            ],
        ];
    }

    public function testDotHasSupportsWildcard(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'a'],
                ['id' => 2, 'name' => 'b'],
            ],
        ];

        $this->assertTrue(ArrayHelper::dotHas('users.*.id', $array));
        $this->assertFalse(ArrayHelper::dotHas('users.*.email', $array));
    }

    public function testDotSetSupportsWildcard(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'a'],
                ['id' => 2, 'name' => 'b'],
            ],
        ];

        ArrayHelper::dotSet($array, 'users.*.role', 'member');

        $this->assertSame('member', $array['users'][0]['role']);
        $this->assertSame('member', $array['users'][1]['role']);
    }

    public function testDotSetSupportsWildcardSkipsNonArrayElements(): void
    {
        $array = [
            'users' => [
                ['name' => 'a'],
                'invalid-entry',
                ['name' => 'b'],
            ],
        ];

        ArrayHelper::dotSet($array, 'users.*.role', 'member');

        $this->assertSame('member', $array['users'][0]['role']);
        $this->assertSame('invalid-entry', $array['users'][1]);
        $this->assertSame('member', $array['users'][2]['role']);
    }

    public function testDotUnsetRemovesNestedValue(): void
    {
        $array = ['user' => ['profile' => ['id' => 123, 'name' => 'john']]];

        $this->assertTrue(ArrayHelper::dotUnset($array, 'user.profile.id'));

        $this->assertFalse(ArrayHelper::dotHas('user.profile.id', $array));
        $this->assertSame('john', $array['user']['profile']['name']);
    }

    public function testDotUnsetIsNoOpWhenPathDoesNotExist(): void
    {
        $array = ['user' => ['id' => 123]];

        $this->assertFalse(ArrayHelper::dotUnset($array, 'user.profile.id'));

        $this->assertSame(['user' => ['id' => 123]], $array);
    }

    public function testDotUnsetWithEscapedDotKey(): void
    {
        $array = ['config' => ['api.version' => 'v1', 'region' => 'eu']];

        $this->assertTrue(ArrayHelper::dotUnset($array, 'config.api\.version'));

        $this->assertSame(['config' => ['region' => 'eu']], $array);
    }

    public function testDotUnsetSupportsWildcard(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'a'],
                ['id' => 2, 'name' => 'b'],
            ],
        ];

        $this->assertTrue(ArrayHelper::dotUnset($array, 'users.*.id'));
        $this->assertFalse(ArrayHelper::dotHas('users.*.id', $array));
        $this->assertSame('a', $array['users'][0]['name']);
        $this->assertSame('b', $array['users'][1]['name']);
    }

    public function testDotUnsetSupportsWildcardReturnsFalseWhenNoKeysRemoved(): void
    {
        $array = [
            'users' => [
                ['name' => 'a'],
                ['name' => 'b'],
            ],
        ];

        $this->assertFalse(ArrayHelper::dotUnset($array, 'users.*.id'));
    }

    public function testDotUnsetSupportsEndingWildcard(): void
    {
        $array = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertTrue(ArrayHelper::dotUnset($array, 'user.*'));
        $this->assertSame(['user' => [], 'meta' => ['request_id' => 'abc']], $array);
    }

    public function testDotUnsetWithSingleWildcardClearsWholeArray(): void
    {
        $array = [
            'user' => ['id' => 123],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertTrue(ArrayHelper::dotUnset($array, '*'));
        $this->assertSame([], $array);
    }

    public function testDotSetThrowsExceptionForInvalidWildcardPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "users.*"');

        $array = [];
        ArrayHelper::dotSet($array, 'users.*', 1);
    }

    public function testDotHasThrowsExceptionForInvalidWildcardPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "users.*"');

        ArrayHelper::dotHas('users.*', []);
    }

    public function testDotUnsetThrowsExceptionForInvalidWildcardPattern(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must set key right after "*". Invalid index: "users.*.*.id"');

        $array = [];
        ArrayHelper::dotUnset($array, 'users.*.*.id');
    }

    public function testDotOnlyReturnsNestedStructureForSinglePath(): void
    {
        $array = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $expected = [
            'user' => [
                'id' => 123,
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, 'user.id'));
    }

    public function testDotOnlyMergesMultiplePaths(): void
    {
        $array = [
            'user' => [
                'id'    => 123,
                'name'  => 'john',
                'email' => 'john@example.com',
            ],
        ];

        $expected = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, ['user.id', 'user.name']));
    }

    public function testDotOnlySupportsWildcard(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'a'],
                ['id' => 2, 'name' => 'b'],
            ],
        ];

        $expected = [
            'users' => [
                ['id' => 1],
                ['id' => 2],
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, 'users.*.id'));
    }

    public function testDotOnlySupportsEndingWildcard(): void
    {
        $array = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $expected = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, 'user.*'));
    }

    public function testDotOnlySupportsEscapedDotKey(): void
    {
        $array = [
            'config' => [
                'api.version' => 'v1',
                'region'      => 'eu',
            ],
        ];

        $expected = [
            'config' => [
                'api.version' => 'v1',
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, 'config.api\.version'));
    }

    public function testDotExceptRemovesNestedPath(): void
    {
        $array = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $expected = [
            'user' => [
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertSame($expected, ArrayHelper::dotExcept($array, 'user.id'));
    }

    public function testDotExceptSupportsWildcard(): void
    {
        $array = [
            'users' => [
                ['id' => 1, 'name' => 'a'],
                ['id' => 2, 'name' => 'b'],
            ],
        ];

        $expected = [
            'users' => [
                ['name' => 'a'],
                ['name' => 'b'],
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotExcept($array, 'users.*.id'));
    }

    public function testDotExceptSupportsEndingWildcard(): void
    {
        $array = [
            'user' => [
                'id'   => 123,
                'name' => 'john',
            ],
            'meta' => ['request_id' => 'abc'],
        ];

        $expected = [
            'user' => [],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertSame($expected, ArrayHelper::dotExcept($array, 'user.*'));
    }

    public function testDotExceptWithEscapedDotKey(): void
    {
        $array = [
            'config' => [
                'api.version' => 'v1',
                'region'      => 'eu',
            ],
        ];

        $expected = [
            'config' => [
                'region' => 'eu',
            ],
        ];

        $this->assertSame($expected, ArrayHelper::dotExcept($array, 'config.api\.version'));
    }

    public function testDotOnlyWithSingleWildcardReturnsWholeArray(): void
    {
        $array = [
            'user' => ['id' => 123],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertSame($array, ArrayHelper::dotOnly($array, '*'));
    }

    public function testDotExceptWithSingleWildcardReturnsEmptyArray(): void
    {
        $array = [
            'user' => ['id' => 123],
            'meta' => ['request_id' => 'abc'],
        ];

        $this->assertSame([], ArrayHelper::dotExcept($array, '*'));
    }

    public function testDotSetWithNumericKey(): void
    {
        $array = [['name' => 'a'], ['name' => 'b']];

        ArrayHelper::dotSet($array, '0.name', 'x');

        $this->assertSame('x', $array[0]['name']);
        $this->assertSame('b', $array[1]['name']);
    }

    public function testDotUnsetWithNumericKey(): void
    {
        $array = [['name' => 'a', 'role' => 'admin'], ['name' => 'b']];

        $this->assertTrue(ArrayHelper::dotUnset($array, '0.role'));
        $this->assertFalse(ArrayHelper::dotHas('0.role', $array));
        $this->assertSame('a', $array[0]['name']);
    }

    public function testDotOnlyWithNumericKey(): void
    {
        $array = [['name' => 'a', 'role' => 'admin'], ['name' => 'b']];

        $expected = [['name' => 'a']];

        $this->assertSame($expected, ArrayHelper::dotOnly($array, '0.name'));
    }

    public function testDotExceptWithNumericKey(): void
    {
        $array = [['name' => 'a', 'role' => 'admin'], ['name' => 'b']];

        $expected = [['name' => 'a'], ['name' => 'b']];

        $this->assertSame($expected, ArrayHelper::dotExcept($array, '0.role'));
    }
}
