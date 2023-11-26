<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 *
 * @group Others
 */
final class DotArrayFilterTest extends CIUnitTestCase
{
    public function testRunReturnEmptyArray()
    {
        $data = [];

        $result = DotArrayFilter::run(['foo.bar'], $data);

        $this->assertSame([], $result);
    }

    public function testRunReturnEmptyArrayMissingValue()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run(['foo.baz'], $data);

        $this->assertSame([], $result);
    }

    public function testRunReturnEmptyArrayEmptyIndex()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run([''], $data);

        $this->assertSame([], $result);
    }

    public function testRunEarlyIndex()
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run(['foo'], $data);

        $this->assertSame($data, $result);
    }

    public function testRunWildcard()
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $result = DotArrayFilter::run(['foo.*.baz'], $data);

        $this->assertSame($data, $result);
    }

    public function testRunWildcardWithMultipleChoices()
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

        $result = DotArrayFilter::run(['foo.*.fizz', 'foo.*.baz'], $data);

        $this->assertSame($data, $result);
    }

    public function testRunNestedNotFound()
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

        $result = DotArrayFilter::run(['foo.*.notthere'], $data);

        $this->assertSame([], $result);
    }

    public function testRunIgnoresLastWildcard()
    {
        $data = [
            'foo' => [
                'bar' => [
                    'baz' => 23,
                ],
            ],
        ];

        $result = DotArrayFilter::run(['foo.bar.*'], $data);

        $this->assertSame($data, $result);
    }

    public function testRunNestedArray()
    {
        $array = [
            'user' => [
                'name'        => 'John',
                'age'         => 30,
                'email'       => 'john@example.com',
                'preferences' => [
                    'theme'         => 'dark',
                    'language'      => 'en',
                    'notifications' => [
                        'email' => true,
                        'push'  => false,
                    ],
                ],
            ],
            'product' => [
                'name'        => 'Acme Product',
                'description' => 'This is a great product!',
                'price'       => 19.99,
            ],
        ];

        $result = DotArrayFilter::run([
            'user.name',
            'user.preferences.language',
            'user.preferences.notifications.email',
            'product.name',
        ], $array);

        $expected = [
            'user' => [
                'name'        => 'John',
                'preferences' => [
                    'language'      => 'en',
                    'notifications' => [
                        'email' => true,
                    ],
                ],
            ],
            'product' => [
                'name' => 'Acme Product',
            ],
        ];
        $this->assertSame($expected, $result);
    }
}
