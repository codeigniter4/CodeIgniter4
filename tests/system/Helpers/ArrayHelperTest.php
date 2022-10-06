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
 */
final class ArrayHelperTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        helper('array');
    }

    public function testArrayDotSimple()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertSame(23, dot_array_search('foo.bar', $data));
    }

    public function testArrayDotTooManyLevels()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('foo.bar.baz', $data));
    }

    public function testArrayDotTooManyLevelsWithWildCard()
    {
        $data = [
            'a' => [],
        ];

        $this->assertNull(dot_array_search('a.*.c', $data));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5369
     */
    public function testArrayDotValueIsListArray()
    {
        $data = [
            'arr' => [1, 2, 3],
        ];

        $this->assertNull(dot_array_search('arr.*.index', $data));
    }

    public function testArrayDotEscape()
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

    public function testArraySearchDotMultiLevels()
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

    public function testArrayDotReturnNullEmptyArray()
    {
        $data = [];

        $this->assertNull(dot_array_search('foo.bar', $data));
    }

    public function testArrayDotReturnNullMissingValue()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('foo.baz', $data));
    }

    public function testArrayDotReturnNullEmptyIndex()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertNull(dot_array_search('', $data));
    }

    public function testArrayDotEarlyIndex()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $this->assertSame(['bar' => 23], dot_array_search('foo', $data));
    }

    public function testArrayDotWildcard()
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

    public function testArrayDotWildcardWithMultipleChoices()
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

    public function testArrayDotNestedNotFound()
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

    public function testArrayDotIgnoresLastWildcard()
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
     * @dataProvider deepSearchProvider
     *
     * @param int|string        $key
     * @param array|string|null $expected
     */
    public function testArrayDeepSearch($key, $expected)
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

    public function testArrayDeepSearchReturnNullEmptyArray()
    {
        $data = [];

        $this->assertNull(array_deep_search('key644', $data));
    }

    /**
     * @dataProvider sortByMultipleKeysProvider
     */
    public function testArraySortByMultipleKeysWithArray(array $data, array $sortColumns, array $expected)
    {
        $success = array_sort_by_multiple_keys($data, $sortColumns);

        $this->assertTrue($success);
        $this->assertSame($expected, array_column($data, 'name'));
    }

    /**
     * @dataProvider sortByMultipleKeysProvider
     */
    public function testArraySortByMultipleKeysWithObjects(array $data, array $sortColumns, array $expected)
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
     * @dataProvider sortByMultipleKeysProvider
     */
    public function testArraySortByMultipleKeysFailsEmptyParameter(array $data, array $sortColumns, array $expected)
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
     * @dataProvider sortByMultipleKeysProvider
     *
     * @param mixed $data
     */
    public function testArraySortByMultipleKeysFailsInconsistentArraySizes($data)
    {
        // PHP 8 changes this error type
        if (version_compare(PHP_VERSION, '8.0', '<')) {
            $this->expectException(ErrorException::class);
        } else {
            $this->expectException(ValueError::class);
        }

        $this->expectExceptionMessage('Array sizes are inconsistent');

        $sortColumns = [
            'team.orders' => SORT_ASC,
            'positions'   => SORT_ASC,
        ];

        array_sort_by_multiple_keys($data, $sortColumns);
    }

    public static function deepSearchProvider()
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

    public static function sortByMultipleKeysProvider()
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
     * @dataProvider arrayFlattenProvider
     */
    public function testArrayFlattening(array $input, array $expected): void
    {
        $this->assertSame($expected, array_flatten_with_dots($input));
    }

    public function arrayFlattenProvider(): iterable
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
}
