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
use Config\Services;
use Generator;
use stdClass;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @no-final
 */
class RulesTest extends CIUnitTestCase
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();
    }

    /**
     * @dataProvider provideRequiredCases
     */
    public function testRequired(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'required']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideRequiredCases(): Generator
    {
        yield from [
            [['foo' => null], false],
            [['foo' => 123], true],
            [['foo' => null, 'bar' => 123], false],
            [['foo' => [123]], true],
            [['foo' => []], false],
            [['foo' => new stdClass()], true],
        ];
    }

    /**
     * @dataProvider ifExistProvider
     */
    public function testIfExist(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function ifExistProvider(): Generator
    {
        yield from [
            [
                ['foo' => 'required'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'required'],
                ['foo' => null],
                false,
            ],
            [
                ['foo' => 'if_exist|required'],
                ['foo' => ''],
                false,
            ],
            // Input data does not exist then the other rules will be ignored
            [
                ['foo' => 'if_exist|required'],
                [],
                true,
            ],
            // Testing for multi-dimensional data
            [
                ['foo.bar' => 'if_exist|required'],
                ['foo' => ['bar' => '']],
                false,
            ],
            [
                ['foo.bar' => 'if_exist|required'],
                ['foo' => []],
                true,
            ],
        ];
    }

    /**
     * @dataProvider providePermitEmptyCases
     */
    public function testPermitEmpty(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function providePermitEmptyCases(): Generator
    {
        yield from [
            // If the rule is only `permit_empty`, any value will pass.
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => '0'],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => '-0'],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_emails'],
                ['foo' => 0],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => -0],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 0.0],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_emails'],
                ['foo' => '0.0'],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => -0.0],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => '-0.0'],
                false,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => null],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => false],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'user@domain.tld'],
                true,
            ],
            [
                ['foo' => 'permit_empty|valid_email'],
                ['foo' => 'invalid'],
                false,
            ],
            // Required has more priority
            [
                ['foo' => 'permit_empty|required|valid_email'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => null],
                false,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => false],
                false,
            ],
            // This tests will return true because the input data is trimmed
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => '0'],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required'],
                ['foo' => 0.0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => ''],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => 0.0, 'bar' => 1],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_with[bar]'],
                ['foo' => '', 'bar' => 1],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => ''],
                false,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => 0],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => 0.0, 'bar' => 1],
                true,
            ],
            [
                ['foo' => 'permit_empty|required_without[bar]'],
                ['foo' => '', 'bar' => 1],
                true,
            ],
        ];
    }

    /**
     * @dataProvider provideMatchesCases
     */
    public function testMatches(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'matches[bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideMatchesCases(): Generator
    {
        yield from [
            [['foo' => null, 'bar' => null], true],
            [['foo' => 'match', 'bar' => 'match'], true],
            [['foo' => 'match', 'bar' => 'nope'], false],
        ];
    }

    /**
     * @dataProvider provideMatchesNestedCases
     */
    public function testMatchesNested(array $data, bool $expected): void
    {
        $this->validation->setRules(['nested.foo' => 'matches[nested.bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideMatchesNestedCases(): Generator
    {
        yield from [
            [['nested' => ['foo' => 'match', 'bar' => 'match']], true],
            [['nested' => ['foo' => 'match', 'bar' => 'nope']], false],
        ];
    }

    /**
     * @dataProvider provideMatchesCases
     */
    public function testDiffers(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'differs[bar]']);
        $this->assertSame(! $expected, $this->validation->run($data));
    }

    /**
     * @dataProvider provideMatchesNestedCases
     */
    public function testDiffersNested(array $data, bool $expected): void
    {
        $this->validation->setRules(['nested.foo' => 'differs[nested.bar]']);
        $this->assertSame(! $expected, $this->validation->run($data));
    }

    /**
     * @dataProvider provideEqualsCases
     */
    public function testEquals(array $data, string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "equals[{$param}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideEqualsCases(): Generator
    {
        yield from [
            'null'   => [['foo' => null], '', false],
            'empty'  => [['foo' => ''], '', true],
            'fail'   => [['foo' => 'bar'], 'notbar', false],
            'pass'   => [['foo' => 'bar'], 'bar', true],
            'casing' => [['foo' => 'bar'], 'Bar', false],
        ];
    }

    /**
     * @dataProvider provideMinLengthCases
     */
    public function testMinLength(?string $data, string $length, bool $expected): void
    {
        $this->validation->setRules(['foo' => "min_length[{$length}]"]);
        $this->assertSame($expected, $this->validation->run(['foo' => $data]));
    }

    public function provideMinLengthCases(): Generator
    {
        yield from [
            'null'    => [null, '2', false],
            'less'    => ['bar', '2', true],
            'equal'   => ['bar', '3', true],
            'greater' => ['bar', '4', false],
        ];
    }

    /**
     * @dataProvider provideMinLengthCases
     */
    public function testMaxLength(?string $data, string $length, bool $expected): void
    {
        $this->validation->setRules(['foo' => "max_length[{$length}]"]);
        $this->assertSame(! $expected || $length === '3', $this->validation->run(['foo' => $data]));
    }

    public function testMaxLengthReturnsFalseWithNonNumericVal(): void
    {
        $this->validation->setRules(['foo' => 'max_length[bar]']);
        $this->assertFalse($this->validation->run(['foo' => 'bar']));
    }

    /**
     * @dataProvider provideExactLengthCases
     */
    public function testExactLength(?string $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'exact_length[3]']);
        $this->assertSame($expected, $this->validation->run(['foo' => $data]));
    }

    public function provideExactLengthCases(): Generator
    {
        yield from [
            'null'    => [null, false],
            'exact'   => ['bar', true],
            'less'    => ['ba', false],
            'greater' => ['bars', false],
        ];
    }

    public function testExactLengthDetectsBadLength(): void
    {
        $data = ['foo' => 'bar'];
        $this->validation->setRules(['foo' => 'exact_length[abc]']);
        $this->assertFalse($this->validation->run($data));
    }

    /**
     * @dataProvider greaterThanProvider
     */
    public function testGreaterThan(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "greater_than[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function greaterThanProvider(): Generator
    {
        yield from [
            ['-10', '-11', true],
            ['10', '9', true],
            ['10', '10', false],
            ['10', 'a', false],
            ['10a', '10', false],
            [null, null, false],
        ];
    }

    /**
     * @dataProvider greaterThanEqualProvider
     */
    public function testGreaterThanEqual(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "greater_than_equal_to[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function greaterThanEqualProvider(): Generator
    {
        yield from [
            ['0', '0', true],
            ['1', '0', true],
            ['-1', '0', false],
            ['10a', '0', false],
            [null, null, false],
            ['1', null, true],
            [null, '1', false],
        ];
    }

    /**
     * @dataProvider lessThanProvider
     */
    public function testLessThan(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "less_than[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function lessThanProvider(): Generator
    {
        yield from [
            ['-10', '-11', false],
            ['9', '10', true],
            ['10', '9', false],
            ['10', '10', false],
            ['10', 'a', true],
            ['10a', '10', false],
            [null, null, false],
        ];
    }

    /**
     * @dataProvider lessThanEqualProvider
     */
    public function testLessEqualThan(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "less_than_equal_to[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function lessThanEqualProvider(): Generator
    {
        yield from [
            ['0', '0', true],
            ['1', '0', false],
            ['-1', '0', true],
            ['10a', '0', false],
            [null, null, false],
            ['1', null, false],
            [null, '1', false],
        ];
    }

    /**
     * @dataProvider inListProvider
     */
    public function testInList(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "in_list[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    /**
     * @dataProvider inListProvider
     */
    public function testNotInList(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "not_in_list[{$second}]"]);
        $this->assertSame(! $expected, $this->validation->run($data));
    }

    public function inListProvider(): Generator
    {
        yield from [
            ['red', 'red,Blue,123', true],
            ['Blue', 'red, Blue,123', true],
            ['Blue', 'red,Blue,123', true],
            ['123', 'red,Blue,123', true],
            ['Red', 'red,Blue,123', false],
            [' red', 'red,Blue,123', false],
            ['1234', 'red,Blue,123', false],
            [null, 'red,Blue,123', false],
            ['red', null, false],
        ];
    }

    /**
     * @dataProvider requiredWithProvider
     */
    public function testRequiredWith(?string $field, ?string $check, bool $expected): void
    {
        $data = [
            'foo'   => 'bar',
            'bar'   => 'something',
            'baz'   => null,
            'array' => [
                'nonEmptyField1' => 'value1',
                'nonEmptyField2' => 'value2',
                'emptyField1'    => null,
                'emptyField2'    => null,
            ],
        ];

        $this->validation->setRules([$field => "required_with[{$check}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function requiredWithProvider(): Generator
    {
        yield from [
            ['nope', 'bar', false],
            ['foo', 'bar', true],
            ['nope', 'baz', true],
            [null, null, true],
            [null, 'foo', false],
            ['foo', null, true],
            [
                'array.emptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.nonEmptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.emptyField1',
                'array.nonEmptyField2',
                false,
            ],
            [
                'array.nonEmptyField1',
                'array.nonEmptyField2',
                true,
            ],
        ];
    }

    /**
     * @dataProvider requiredWithoutProvider
     */
    public function testRequiredWithout(?string $field, ?string $check, bool $expected): void
    {
        $data = [
            'foo'   => 'bar',
            'bar'   => 'something',
            'baz'   => null,
            'array' => [
                'nonEmptyField1' => 'value1',
                'nonEmptyField2' => 'value2',
                'emptyField1'    => null,
                'emptyField2'    => null,
            ],
        ];

        $this->validation->setRules([$field => "required_without[{$check}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function requiredWithoutProvider(): Generator
    {
        yield from [
            ['nope', 'bars', false],
            ['foo', 'nope', true],
            [null, null, false],
            [null, 'foo', true],
            ['foo', null, true],
            [
                'array.emptyField1',
                'array.emptyField2',
                false,
            ],
            [
                'array.nonEmptyField1',
                'array.emptyField2',
                true,
            ],
            [
                'array.emptyField1',
                'array.nonEmptyField2',
                true,
            ],
            [
                'array.nonEmptyField1',
                'array.nonEmptyField2',
                true,
            ],
        ];
    }

    /**
     * @dataProvider requiredWithoutMultipleProvider
     */
    public function testRequiredWithoutMultiple(string $foo, string $bar, string $baz, bool $result): void
    {
        $this->validation->setRules(['foo' => 'required_without[bar,baz]']);

        $data = [
            'foo' => $foo,
            'bar' => $bar,
            'baz' => $baz,
        ];
        $this->assertSame($result, $this->validation->run($data));
    }

    public function requiredWithoutMultipleProvider(): Generator
    {
        yield from [
            'all empty' => [
                '',
                '',
                '',
                false,
            ],
            'foo is not empty' => [
                'a',
                '',
                '',
                true,
            ],
            'bar is not empty' => [
                '',
                'b',
                '',
                false,
            ],
            'baz is not empty' => [
                '',
                '',
                'c',
                false,
            ],
            'bar,baz are not empty' => [
                '',
                'b',
                'c',
                true,
            ],
        ];
    }

    /**
     * @dataProvider requiredWithoutMultipleWithoutFieldsProvider
     */
    public function testRequiredWithoutMultipleWithoutFields(array $data, bool $result): void
    {
        $this->validation->setRules(['foo' => 'required_without[bar,baz]']);

        $this->assertSame($result, $this->validation->run($data));
    }

    public function requiredWithoutMultipleWithoutFieldsProvider(): Generator
    {
        yield from [
            'baz is missing' => [
                [
                    'foo' => '',
                    'bar' => '',
                ],
                false,
            ],
            'bar,baz are missing' => [
                [
                    'foo' => '',
                ],
                false,
            ],
            'bar is not empty' => [
                [
                    'foo' => '',
                    'bar' => 'b',
                ],
                false,
            ],
            'foo is not empty' => [
                [
                    'foo' => 'a',
                ],
                true,
            ],
        ];
    }
}
