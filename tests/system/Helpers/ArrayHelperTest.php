<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Helpers;

use CodeIgniter\Test\CIUnitTestCase;
use ErrorException;
use ValueError;

/**
 * @internal
 *
 * @group Others
 */
final class ArrayHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('array');
    }

    public function testArrayDotSimple(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertSame(23, dot_array_search('foo.bar', $data));
    }

    public function testArrayDotTooManyLevels(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('foo.bar.baz', $data));
    }

    public function testArrayDotTooManyLevelsWithWildCard(): void
    {
        $data = [
            'a' => [],
        ];

        $this->assertNull(dot_array_search('a.*.c', $data));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5369
     */
    public function testArrayDotValueIsListArray(): void
    {
        $data = [
            'arr' => [1, 2, 3],
        ];

        $this->assertNull(dot_array_search('arr.*.index', $data));
    }

    public function testArrayDotEscape(): void
    {
        $data = [
            'foo' => [
                'bar.baz' => 23,
            ],
            'foo.bar' => [
                'baz' => 42,
            ],
        ];

        $this->assertSame(23, dot_array_search('foo.bar\.baz', $data));
        $this->assertSame(42, dot_array_search('foo\.bar.baz', $data));
    }

    public function testArraySearchDotMultiLevels(): void
    {
        $data1 = [
            'bar' => [
                ['foo' => 'baz'],
            ],
        ];
        $this->assertSame('baz', dot_array_search('bar.*.foo', $data1));

        $data2 = [
            'bar' => [
                ['foo' => 'bizz'],
                ['foo' => 'buzz'],
            ],
        ];
        $this->assertSame(['bizz', 'buzz'], dot_array_search('bar.*.foo', $data2));

        $data3 = [
            'baz' => 'none',
        ];
        $this->assertNull(dot_array_search('bar.*.foo', $data3));
    }

    public function testArrayDotReturnNullEmptyArray(): void
    {
        $data = [];

        $this->assertNull(dot_array_search('foo.bar', $data));
    }

    public function testArrayDotReturnNullMissingValue(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('foo.baz', $data));
    }

    public function testArrayDotReturnNullEmptyIndex(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('', $data));
    }

    public function testArrayDotEarlyIndex(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertSame(['bar' => 23], dot_array_search('foo', $data));
    }

    public function testArrayDotWildcard(): void
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $this->assertSame(23, dot_array_search('foo.*.baz', $data));
    }

    public function testArrayDotWildcardWithMultipleChoices(): void
    {
        $data = [
            'foo' => [
                'buzz' => [
                    'fizz' => 11,
                ],
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $this->assertSame(11, dot_array_search('foo.*.fizz', $data));
        $this->assertSame(23, dot_array_search('foo.*.baz', $data));
    }

    public function testArrayDotNestedNotFound(): void
    {
        $data = [
            'foo' => [
                'buzz' => [
                    'fizz' => 11,
                ],
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $this->assertNull(dot_array_search('foo.*.notthere', $data));
    }

    public function testArrayDotIgnoresLastWildcard(): void
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $this->assertSame(['baz' => 23], dot_array_search('foo.bar.*', $data));
    }

    /**
     * @dataProvider provideArrayDeepSearch
     *
     * @param int|string        $key
     * @param array|string|null $expected
     */
    public function testArrayDeepSearch($key, $expected): void
    {
        $data = [
            'key1' => 'Value 1',
            'key5' => [
                'key51' => 'Value 5.1',
            ],
            'key6' => [
                'key61' => [
                    'key61' => 'Value 6.1',
                    'key64' => [
                        42       => 'Value 42',
                        'key641' => 'Value 6.4.1',
                        'key644' => [
                            'key6441' => 'Value 6.4.4.1',
                        ],
                    ],
                ],
            ],
        ];

        $result = array_deep_search($key, $data);

        $this->assertSame($expected, $result);
    }

    public function testArrayDeepSearchReturnNullEmptyArray(): void
    {
        $data = [];

        $this->assertNull(array_deep_search('key644', $data));
    }

    /**
     * @dataProvider provideSortByMultipleKeys
     */
    public function testArraySortByMultipleKeysWithArray(array $data, array $sortColumns, array $expected): void
    {
        $success = array_sort_by_multiple_keys($data, $sortColumns);

        $this->assertTrue($success);
        $this->assertSame($expected, array_column($data, 'name'));
    }

    /**
     * @dataProvider provideSortByMultipleKeys
     */
    public function testArraySortByMultipleKeysWithObjects(array $data, array $sortColumns, array $expected): void
    {
        // Morph to objects
        foreach ($data as $index => $dataSet) {
            $data[$index] = (object) $dataSet;
        }

        $success = array_sort_by_multiple_keys($data, $sortColumns);

        $this->assertTrue($success);
        $this->assertSame($expected, array_column((array) $data, 'name'));
    }

    /**
     * @dataProvider provideSortByMultipleKeys
     */
    public function testArraySortByMultipleKeysFailsEmptyParameter(array $data, array $sortColumns, array $expected): void
    {
        // Both filled
        $success = array_sort_by_multiple_keys($data, $sortColumns);

        $this->assertTrue($success);

        // Empty $sortColumns
        $success = array_sort_by_multiple_keys($data, []);

        $this->assertFalse($success);

        // Empty &$array
        $data    = [];
        $success = array_sort_by_multiple_keys($data, $sortColumns);

        $this->assertFalse($success);
    }

    /**
     * @dataProvider provideSortByMultipleKeys
     *
     * @param mixed $data
     */
    public function testArraySortByMultipleKeysFailsInconsistentArraySizes($data): void
    {
        // PHP 8 changes this error type
        if (PHP_VERSION_ID >= 80000) {
            $this->expectException(ValueError::class);
        } else {
            $this->expectException(ErrorException::class);
        }

        $this->expectExceptionMessage('Array sizes are inconsistent');

        $sortColumns = [
            'team.orders' => SORT_ASC,
            'positions'   => SORT_ASC,
        ];

        array_sort_by_multiple_keys($data, $sortColumns);
    }

    public static function provideArrayDeepSearch(): iterable
    {
        return [
            [
                'key6441',
                'Value 6.4.4.1',
            ],
            [
                'key64421',
                null,
            ],
            [
                42,
                'Value 42',
            ],
            [
                'key644',
                ['key6441' => 'Value 6.4.4.1'],
            ],
            [
                '',
                null,
            ],
        ];
    }

    public static function provideSortByMultipleKeys(): iterable
    {
        $seed = [
            0 => [
                'name'     => 'John',
                'position' => 3,
                'team'     => [
                    'order' => 2,
                ],
            ],
            1 => [
                'name'     => 'Maria',
                'position' => 4,
                'team'     => [
                    'order' => 1,
                ],
            ],
            2 => [
                'name'     => 'Frank',
                'position' => 1,
                'team'     => [
                    'order' => 1,
                ],
            ],
        ];

        return [
            [
                $seed,
                [
                    'name' => SORT_STRING,
                ],
                [
                    'Frank',
                    'John',
                    'Maria',
                ],
            ],
            [
                $seed,
                [
                    'team.order' => SORT_ASC,
                    'position'   => SORT_ASC,
                ],
                [
                    'Frank',
                    'Maria',
                    'John',
                ],
            ],
        ];
    }

    /**
     * @dataProvider provideArrayFlattening
     */
    public function testArrayFlattening(array $input, array $expected): void
    {
        $this->assertSame($expected, array_flatten_with_dots($input));
    }

    public static function provideArrayFlattening(): iterable
    {
        yield 'normal' => [
            [
                'id'   => '12',
                'user' => [
                    'first_name' => 'john',
                    'last_name'  => 'smith',
                    'age'        => '26 years',
                ],
            ],
            [
                'id'              => '12',
                'user.first_name' => 'john',
                'user.last_name'  => 'smith',
                'user.age'        => '26 years',
            ],
        ];

        yield 'many-levels' => [
            [
                'foo' => 1,
                'bar' => [
                    'bax' => [
                        'baz' => 2,
                        'biz' => 3,
                    ],
                ],
                'baz' => [
                    'fizz' => 4,
                ],
            ],
            [
                'foo'         => 1,
                'bar.bax.baz' => 2,
                'bar.bax.biz' => 3,
                'baz.fizz'    => 4,
            ],
        ];

        yield 'with-empty-arrays' => [
            [
                'foo' => 'bar',
                'baz' => [],
                'bar' => [
                    'fizz' => 'buzz',
                    'nope' => 'yeah',
                    'why'  => [],
                ],
            ],
            [
                'foo'      => 'bar',
                'baz'      => [],
                'bar.fizz' => 'buzz',
                'bar.nope' => 'yeah',
                'bar.why'  => [],
            ],
        ];

        yield 'with-empty-string-index' => [
            [
                'foo' => 1,
                ''    => [
                    'bar' => 2,
                    'baz' => 3,
                ],
                ['fizz' => 4],
                ['buzz' => 5],
            ],
            [
                'foo'    => 1,
                '.bar'   => 2,
                '.baz'   => 3,
                '0.fizz' => 4,
                '1.buzz' => 5,
            ],
        ];

        yield 'empty-array-and-lists' => [
            [
                'bar' => [
                    ['foo' => 'baz'],
                    ['foo' => []],
                ],
            ],
            [
                'bar.0.foo' => 'baz',
                'bar.1.foo' => [],
            ],
        ];
    }

    /**
     * @dataProvider provideArrayGroupByIncludeEmpty
     */
    public function testArrayGroupByIncludeEmpty(array $indexes, array $data, array $expected): void
    {
        $actual = array_group_by($data, $indexes, true);

        $this->assertSame($expected, $actual, 'array including empty not the same');
    }

    /**
     * @dataProvider provideArrayGroupByExcludeEmpty
     */
    public function testArrayGroupByExcludeEmpty(array $indexes, array $data, array $expected): void
    {
        $actual = array_group_by($data, $indexes, false);

        $this->assertSame($expected, $actual, 'array excluding empty not the same');
    }

    public static function provideArrayGroupByIncludeEmpty(): iterable
    {
        yield 'simple group-by test' => [
            ['color'],
            [
                [
                    'id'    => 1,
                    'item'  => 'ball',
                    'color' => 'blue',
                ],
                [
                    'id'    => 2,
                    'item'  => 'book',
                    'color' => 'red',
                ],
                [
                    'id'   => 3,
                    'item' => 'bird',
                    'age'  => 5,
                ],
                [
                    'id'    => 4,
                    'item'  => 'jeans',
                    'color' => 'blue',
                ],
            ],
            [
                'blue' => [
                    [
                        'id'    => 1,
                        'item'  => 'ball',
                        'color' => 'blue',
                    ],
                    [
                        'id'    => 4,
                        'item'  => 'jeans',
                        'color' => 'blue',
                    ],
                ],
                'red' => [
                    [
                        'id'    => 2,
                        'item'  => 'book',
                        'color' => 'red',
                    ],
                ],
                '' => [
                    [
                        'id'   => 3,
                        'item' => 'bird',
                        'age'  => 5,
                    ],
                ],
            ],
        ];

        yield '2 index data' => [
            ['gender', 'country'],
            [
                [
                    'id'         => 1,
                    'first_name' => 'Scarface',
                    'gender'     => 'Male',
                    'country'    => 'Germany',
                ],
                [
                    'id'         => 2,
                    'first_name' => 'Fletch',
                    'gender'     => 'Male',
                    'country'    => 'France',
                ],
                [
                    'id'         => 3,
                    'first_name' => 'Wrennie',
                    'gender'     => 'Female',
                    'country'    => 'France',
                ],
                [
                    'id'         => 4,
                    'first_name' => 'Virgilio',
                    'gender'     => 'Male',
                    'country'    => 'France',
                ],
                [
                    'id'         => 5,
                    'first_name' => 'Cathlene',
                    'gender'     => 'Polygender',
                    'country'    => 'France',
                ],
                [
                    'id'         => 6,
                    'first_name' => 'Far',
                    'gender'     => 'Male',
                    'country'    => 'Canada',
                ],
                [
                    'id'         => 7,
                    'first_name' => 'Dolores',
                    'gender'     => 'Female',
                    'country'    => 'Canada',
                ],
                [
                    'id'         => 8,
                    'first_name' => 'Sissy',
                    'gender'     => 'Female',
                    'country'    => null,
                ],
                [
                    'id'         => 9,
                    'first_name' => 'Chlo',
                    'gender'     => 'Female',
                    'country'    => 'France',
                ],
                [
                    'id'         => 10,
                    'first_name' => 'Gabbie',
                    'gender'     => 'Male',
                    'country'    => 'Canada',
                ],
            ],
            [
                'Male' => [
                    'Germany' => [
                        [
                            'id'         => 1,
                            'first_name' => 'Scarface',
                            'gender'     => 'Male',
                            'country'    => 'Germany',
                        ],
                    ],
                    'France' => [
                        [
                            'id'         => 2,
                            'first_name' => 'Fletch',
                            'gender'     => 'Male',
                            'country'    => 'France',
                        ],
                        [
                            'id'         => 4,
                            'first_name' => 'Virgilio',
                            'gender'     => 'Male',
                            'country'    => 'France',
                        ],
                    ],
                    'Canada' => [
                        [
                            'id'         => 6,
                            'first_name' => 'Far',
                            'gender'     => 'Male',
                            'country'    => 'Canada',
                        ],
                        [
                            'id'         => 10,
                            'first_name' => 'Gabbie',
                            'gender'     => 'Male',
                            'country'    => 'Canada',
                        ],
                    ],
                ],
                'Female' => [
                    'France' => [
                        [
                            'id'         => 3,
                            'first_name' => 'Wrennie',
                            'gender'     => 'Female',
                            'country'    => 'France',
                        ],
                        [
                            'id'         => 9,
                            'first_name' => 'Chlo',
                            'gender'     => 'Female',
                            'country'    => 'France',
                        ],
                    ],
                    'Canada' => [
                        [
                            'id'         => 7,
                            'first_name' => 'Dolores',
                            'gender'     => 'Female',
                            'country'    => 'Canada',
                        ],
                    ],
                    '' => [
                        [
                            'id'         => 8,
                            'first_name' => 'Sissy',
                            'gender'     => 'Female',
                            'country'    => null,
                        ],
                    ],
                ],
                'Polygender' => [
                    'France' => [
                        [
                            'id'         => 5,
                            'first_name' => 'Cathlene',
                            'gender'     => 'Polygender',
                            'country'    => 'France',
                        ],
                    ],
                ],
            ],
        ];

        yield 'nested data with dot syntax' => [
            ['gender', 'hr.department'],
            [
                [
                    'id'         => 1,
                    'first_name' => 'Urbano',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => 'Canada',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 2,
                    'first_name' => 'Case',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Marketing',
                    ],
                ],
                [
                    'id'         => 3,
                    'first_name' => 'Emera',
                    'gender'     => 'Female',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 4,
                    'first_name' => 'Richy',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 5,
                    'first_name' => 'Mandy',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 6,
                    'first_name' => 'Risa',
                    'gender'     => 'Female',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 7,
                    'first_name' => 'Alfred',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 8,
                    'first_name' => 'Tabby',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Marketing',
                    ],
                ],
                [
                    'id'         => 9,
                    'first_name' => 'Ario',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 10,
                    'first_name' => 'Somerset',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'Germany',
                        'department' => 'Marketing',
                    ],
                ],
            ],
            [
                '' => [
                    'Engineering' => [
                        [
                            'id'         => 1,
                            'first_name' => 'Urbano',
                            'gender'     => null,
                            'hr'         => [
                                'country'    => 'Canada',
                                'department' => 'Engineering',
                            ],
                        ],
                    ],
                    'Sales' => [
                        [
                            'id'         => 4,
                            'first_name' => 'Richy',
                            'gender'     => null,
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Sales',
                            ],
                        ],
                        [
                            'id'         => 5,
                            'first_name' => 'Mandy',
                            'gender'     => null,
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Sales',
                            ],
                        ],
                    ],
                ],
                'Male' => [
                    'Marketing' => [
                        [
                            'id'         => 2,
                            'first_name' => 'Case',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Marketing',
                            ],
                        ],
                        [
                            'id'         => 8,
                            'first_name' => 'Tabby',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Marketing',
                            ],
                        ],
                        [
                            'id'         => 10,
                            'first_name' => 'Somerset',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'Germany',
                                'department' => 'Marketing',
                            ],
                        ],
                    ],
                    'Engineering' => [
                        [
                            'id'         => 7,
                            'first_name' => 'Alfred',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Engineering',
                            ],
                        ],
                    ],
                    'Sales' => [
                        [
                            'id'         => 9,
                            'first_name' => 'Ario',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Sales',
                            ],
                        ],
                    ],
                ],
                'Female' => [
                    'Engineering' => [
                        [
                            'id'         => 3,
                            'first_name' => 'Emera',
                            'gender'     => 'Female',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Engineering',
                            ],
                        ],
                        [
                            'id'         => 6,
                            'first_name' => 'Risa',
                            'gender'     => 'Female',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Engineering',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }

    public static function provideArrayGroupByExcludeEmpty(): iterable
    {
        yield 'simple group-by test' => [
            ['color'],
            [
                [
                    'id'    => 1,
                    'item'  => 'ball',
                    'color' => 'blue',
                ],
                [
                    'id'    => 2,
                    'item'  => 'book',
                    'color' => 'red',
                ],
                [
                    'id'   => 3,
                    'item' => 'bird',
                    'age'  => 5,
                ],
                [
                    'id'    => 4,
                    'item'  => 'jeans',
                    'color' => 'blue',
                ],
            ],
            [
                'blue' => [
                    [
                        'id'    => 1,
                        'item'  => 'ball',
                        'color' => 'blue',
                    ],
                    [
                        'id'    => 4,
                        'item'  => 'jeans',
                        'color' => 'blue',
                    ],
                ],
                'red' => [
                    [
                        'id'    => 2,
                        'item'  => 'book',
                        'color' => 'red',
                    ],
                ],
            ],
        ];

        yield '2 index data' => [
            ['gender', 'country'],
            [
                [
                    'id'         => 1,
                    'first_name' => 'Scarface',
                    'gender'     => 'Male',
                    'country'    => 'Germany',
                ],
                [
                    'id'         => 2,
                    'first_name' => 'Fletch',
                    'gender'     => 'Male',
                    'country'    => 'France',
                ],
                [
                    'id'         => 3,
                    'first_name' => 'Wrennie',
                    'gender'     => 'Female',
                    'country'    => 'France',
                ],
                [
                    'id'         => 4,
                    'first_name' => 'Virgilio',
                    'gender'     => 'Male',
                    'country'    => 'France',
                ],
                [
                    'id'         => 5,
                    'first_name' => 'Cathlene',
                    'gender'     => 'Polygender',
                    'country'    => 'France',
                ],
                [
                    'id'         => 6,
                    'first_name' => 'Far',
                    'gender'     => 'Male',
                    'country'    => 'Canada',
                ],
                [
                    'id'         => 7,
                    'first_name' => 'Dolores',
                    'gender'     => 'Female',
                    'country'    => 'Canada',
                ],
                [
                    'id'         => 8,
                    'first_name' => 'Sissy',
                    'gender'     => 'Female',
                    'country'    => null,
                ],
                [
                    'id'         => 9,
                    'first_name' => 'Chlo',
                    'gender'     => 'Female',
                    'country'    => 'France',
                ],
                [
                    'id'         => 10,
                    'first_name' => 'Gabbie',
                    'gender'     => 'Male',
                    'country'    => 'Canada',
                ],
            ],
            [
                'Male' => [
                    'Germany' => [
                        [
                            'id'         => 1,
                            'first_name' => 'Scarface',
                            'gender'     => 'Male',
                            'country'    => 'Germany',
                        ],
                    ],
                    'France' => [
                        [
                            'id'         => 2,
                            'first_name' => 'Fletch',
                            'gender'     => 'Male',
                            'country'    => 'France',
                        ],
                        [
                            'id'         => 4,
                            'first_name' => 'Virgilio',
                            'gender'     => 'Male',
                            'country'    => 'France',
                        ],
                    ],
                    'Canada' => [
                        [
                            'id'         => 6,
                            'first_name' => 'Far',
                            'gender'     => 'Male',
                            'country'    => 'Canada',
                        ],
                        [
                            'id'         => 10,
                            'first_name' => 'Gabbie',
                            'gender'     => 'Male',
                            'country'    => 'Canada',
                        ],
                    ],
                ],
                'Female' => [
                    'France' => [
                        [
                            'id'         => 3,
                            'first_name' => 'Wrennie',
                            'gender'     => 'Female',
                            'country'    => 'France',
                        ],
                        [
                            'id'         => 9,
                            'first_name' => 'Chlo',
                            'gender'     => 'Female',
                            'country'    => 'France',
                        ],
                    ],
                    'Canada' => [
                        [
                            'id'         => 7,
                            'first_name' => 'Dolores',
                            'gender'     => 'Female',
                            'country'    => 'Canada',
                        ],
                    ],
                ],
                'Polygender' => [
                    'France' => [
                        [
                            'id'         => 5,
                            'first_name' => 'Cathlene',
                            'gender'     => 'Polygender',
                            'country'    => 'France',
                        ],
                    ],
                ],
            ],
        ];

        yield 'nested data with dot syntax' => [
            ['gender', 'hr.department'],
            [
                [
                    'id'         => 1,
                    'first_name' => 'Urbano',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => 'Canada',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 2,
                    'first_name' => 'Case',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Marketing',
                    ],
                ],
                [
                    'id'         => 3,
                    'first_name' => 'Emera',
                    'gender'     => 'Female',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 4,
                    'first_name' => 'Richy',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 5,
                    'first_name' => 'Mandy',
                    'gender'     => null,
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 6,
                    'first_name' => 'Risa',
                    'gender'     => 'Female',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 7,
                    'first_name' => 'Alfred',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Engineering',
                    ],
                ],
                [
                    'id'         => 8,
                    'first_name' => 'Tabby',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'France',
                        'department' => 'Marketing',
                    ],
                ],
                [
                    'id'         => 9,
                    'first_name' => 'Ario',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => null,
                        'department' => 'Sales',
                    ],
                ],
                [
                    'id'         => 10,
                    'first_name' => 'Somerset',
                    'gender'     => 'Male',
                    'hr'         => [
                        'country'    => 'Germany',
                        'department' => 'Marketing',
                    ],
                ],
            ],
            [
                'Male' => [
                    'Marketing' => [
                        [
                            'id'         => 2,
                            'first_name' => 'Case',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Marketing',
                            ],
                        ],
                        [
                            'id'         => 8,
                            'first_name' => 'Tabby',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Marketing',
                            ],
                        ],
                        [
                            'id'         => 10,
                            'first_name' => 'Somerset',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'Germany',
                                'department' => 'Marketing',
                            ],
                        ],
                    ],
                    'Engineering' => [
                        [
                            'id'         => 7,
                            'first_name' => 'Alfred',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Engineering',
                            ],
                        ],
                    ],
                    'Sales' => [
                        [
                            'id'         => 9,
                            'first_name' => 'Ario',
                            'gender'     => 'Male',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Sales',
                            ],
                        ],
                    ],
                ],
                'Female' => [
                    'Engineering' => [
                        [
                            'id'         => 3,
                            'first_name' => 'Emera',
                            'gender'     => 'Female',
                            'hr'         => [
                                'country'    => 'France',
                                'department' => 'Engineering',
                            ],
                        ],
                        [
                            'id'         => 6,
                            'first_name' => 'Risa',
                            'gender'     => 'Female',
                            'hr'         => [
                                'country'    => null,
                                'department' => 'Engineering',
                            ],
                        ],
                    ],
                ],
            ],
        ];
    }
}
