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

namespace CodeIgniter\Validation;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DotArrayFilterTest extends CIUnitTestCase
{
    public function testRunReturnEmptyArray(): void
    {
        $data = [];

        $result = DotArrayFilter::run(['foo.bar'], $data);

        $this->assertSame([], $result);
    }

    public function testRunReturnEmptyArrayMissingValue(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run(['foo.baz'], $data);

        $this->assertSame([], $result);
    }

    public function testRunReturnEmptyArrayEmptyIndex(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run([''], $data);

        $this->assertSame([], $result);
    }

    public function testRunEarlyIndex(): void
    {
        $data = [
            'foo' => [
                'bar' => 23,
            ],
        ];

        $result = DotArrayFilter::run(['foo'], $data);

        $this->assertSame($data, $result);
    }

    public function testRunWildcard(): void
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

    public function testRunWildcardWithMultipleChoices(): void
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

    public function testRunNestedNotFound(): void
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

    public function testRunIgnoresLastWildcard(): void
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

    public function testRunNestedArray(): void
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

    public function testRunReturnOrderedIndices(): void
    {
        $data = [
            'foo' => [
                2 => 'bar',
                0 => 'baz',
                1 => 'biz',
            ],
        ];

        $result = DotArrayFilter::run(['foo.2', 'foo.0', 'foo.1'], $data);

        $this->assertSame($data, $result);
    }
}
