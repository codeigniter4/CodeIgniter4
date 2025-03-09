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

namespace CodeIgniter\HTTP\Parameters;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * @internal
 */
#[Group('Others')]
final class InputParametersTest extends CIUnitTestCase
{
    /**
     * @var array<string, mixed>
     */
    private array $original = [
        'title'        => '',
        'toolbar'      => '1',
        'path'         => 'public/index.php',
        'current_time' => 1741522635.661474,
        'debug'        => true,
        'pages'        => 15,
        'filters'      => [
            0 => 'name',
            1 => 'sum',
        ],
        'sort' => [
            'date' => 'ASC',
            'age'  => 'DESC',
        ],
    ];

    public function testGetNonScalarValues(): void
    {
        $parameters = new InputParameters($this->original);

        $this->assertNull($parameters->get('undefined_or_null', null));

        $this->expectException(InvalidArgumentException::class);

        $parameters->get('undefined_throw', new stdClass());
    }

    public function testAttemptSetNullValues(): void
    {
        $parameters = new InputParameters($this->original);

        $this->expectException(InvalidArgumentException::class);

        $parameters->set('nullable', null);
    }

    public function testAttemptSetNonScalarValues(): void
    {
        $parameters = new InputParameters($this->original);

        $this->expectException(InvalidArgumentException::class);

        $parameters->set('nullable', null);
    }

    public function testUpdateAndSetNewValues(): void
    {
        /**
         * @var array<string, mixed>
         */
        $expected = [
            'title'        => 'CodeIgniter',
            'toolbar'      => '0',
            'path'         => '',
            'current_time' => 1741522888.661434,
            'debug'        => false,
            'pages'        => 10,
            'filters'      => [
                0 => 'sum',
                1 => 'name',
            ],
            'sort' => [
                'age'  => 'ASC',
                'date' => 'DESC',
            ],
            'slug' => 'Ben-i-need-help',
        ];

        $parameters = new InputParameters($this->original);

        foreach (array_keys($expected) as $key) {
            $parameters->set($key, $expected[$key]);
        }

        $this->assertSame($expected, $parameters->all());
    }
}
