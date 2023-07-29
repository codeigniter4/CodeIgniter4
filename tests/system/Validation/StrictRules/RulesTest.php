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
     * @dataProvider providePermitEmptyCasesStrict
     */
    public function testPermitEmptyStrict(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function providePermitEmptyCasesStrict(): iterable
    {
        yield [
            ['foo' => 'permit_empty'],
            ['foo' => ''],
            true,
        ];

        yield [
            ['foo' => 'permit_empty'],
            ['foo' => '0'],
            true,
        ];

        yield [
            ['foo' => 'permit_empty'],
            ['foo' => 0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty'],
            ['foo' => 0.0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty'],
            ['foo' => null],
            true,
        ];

        yield [
            ['foo' => 'permit_empty'],
            ['foo' => false],
            true,
        ];
        // Testing with closure
        yield [
            ['foo' => ['permit_empty', static fn ($value) => true]],
            ['foo' => ''],
            true,
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

    public function provideGreaterThanEqualStrict(): iterable
    {
        yield [0, '0', true];

        yield [1, '0', true];

        yield [-1, '0', false];

        yield [1.0, '1', true];

        yield [1.1, '1', true];

        yield [0.9, '1', false];

        yield [true, '0', false];
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

    public function provideGreaterThanStrict(): iterable
    {
        yield [-10, '-11', true];

        yield [10, '9', true];

        yield [10, '10', false];

        yield [10.1, '10', true];

        yield [10.0, '10', false];

        yield [9.9, '10', false];

        yield [10, 'a', false];

        yield [true, '0', false];
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

    public function provideLessThanStrict(): iterable
    {
        yield [-10, '-11', false];

        yield [9, '10', true];

        yield [10, '9', false];

        yield [10, '10', false];

        yield [9.9, '10', true];

        yield [10.1, '10', false];

        yield [10.0, '10', false];

        yield [10, 'a', true];

        yield [true, '0', false];
    }

    /**
     * @dataProvider provideLessThanEqualStrict
     *
     * @param int $value
     */
    public function testLessEqualThanStrict($value, ?string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "less_than_equal_to[{$param}]"]);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideLessThanEqualStrict(): iterable
    {
        yield [0, '0', true];

        yield [1, '0', false];

        yield [-1, '0', true];

        yield [1.0, '1', true];

        yield [0.9, '1', true];

        yield [1.1, '1', false];

        yield [true, '0', false];
    }
}
