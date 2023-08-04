<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Validation\StrictRules;

use CodeIgniter\Validation\RulesTest as TraditionalRulesTest;
use CodeIgniter\Validation\Validation;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
 */
final class RulesTest extends TraditionalRulesTest
{
    protected Validation $validation;
    protected array $config = [
        'ruleSets' => [
            Rules::class,
            FormatRules::class,
            FileRules::class,
            CreditCardRules::class,
            TestRules::class,
        ],
        'groupA' => [
            'foo' => 'required|min_length[5]',
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
    ];

    /**
     * @dataProvider providePermitEmptyStrict
     */
    public function testPermitEmptyStrict(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function providePermitEmptyStrict(): iterable
    {
        yield from [
            [
                ['foo' => 'permit_empty'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => null],
                true,
            ],
            [
                ['foo' => 'permit_empty'],
                ['foo' => false],
                true,
            ],
            // Testing with closure
            [
                ['foo' => ['permit_empty', static fn ($value) => true]],
                ['foo' => ''],
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideGreaterThanEqualStrict
     *
     * @param int $value
     */
    public function testGreaterThanEqualStrict($value, string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "greater_than_equal_to[{$param}]"]);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideGreaterThanEqualStrict(): iterable
    {
        yield from [
            [0, '0', true],
            [1, '0', true],
            [-1, '0', false],
            [1.0, '1', true],
            [1.1, '1', true],
            [0.9, '1', false],
            [true, '0', false],
        ];
    }

    /**
     * @dataProvider provideGreaterThanStrict
     *
     * @param int $value
     */
    public function testGreaterThanStrict($value, string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "greater_than[{$param}]"]);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideGreaterThanStrict(): iterable
    {
        yield from [
            [-10, '-11', true],
            [10, '9', true],
            [10, '10', false],
            [10.1, '10', true],
            [10.0, '10', false],
            [9.9, '10', false],
            [10, 'a', false],
            [true, '0', false],
        ];
    }

    /**
     * @dataProvider provideLessThanStrict
     *
     * @param int $value
     */
    public function testLessThanStrict($value, string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "less_than[{$param}]"]);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideLessThanStrict(): iterable
    {
        yield from [
            [-10, '-11', false],
            [9, '10', true],
            [10, '9', false],
            [10, '10', false],
            [9.9, '10', true],
            [10.1, '10', false],
            [10.0, '10', false],
            [10, 'a', true],
            [true, '0', false],
        ];
    }

    /**
     * @dataProvider provideLessEqualThanStrict
     *
     * @param int $value
     */
    public function testLessEqualThanStrict($value, ?string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "less_than_equal_to[{$param}]"]);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideLessEqualThanStrict(): iterable
    {
        yield from [
            [0, '0', true],
            [1, '0', false],
            [-1, '0', true],
            [1.0, '1', true],
            [0.9, '1', true],
            [1.1, '1', false],
            [true, '0', false],
        ];
    }
}
