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
use Config\Services;
use ErrorException;
use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @no-final
 */
#[Group('Others')]
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

    #[DataProvider('provideRequired')]
    public function testRequired(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'required']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideRequired(): iterable
    {
        yield from [
            [[], false],
            [['foo' => null], false],
            [['foo' => 123], true],
            [['foo' => null, 'bar' => 123], false],
            [['foo' => [123]], true],
            [['foo' => []], false],
            [['foo' => new stdClass()], true],
        ];
    }

    #[DataProvider('provideIfExist')]
    public function testIfExist(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideIfExist(): iterable
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
            // Testing with closure
            [
                ['foo' => ['if_exist', static fn ($value) => true]],
                ['foo' => []],
                true,
            ],
        ];
    }

    public function testIfExistArray(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('Array to string conversion');

        $rules = ['foo' => 'if_exist|alpha'];
        // Invalid array input
        $data = ['foo' => ['bar' => '12345']];

        $this->validation->setRules($rules);
        $this->validation->run($data);
    }

    #[DataProvider('providePermitEmpty')]
    public function testPermitEmpty(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function providePermitEmpty(): iterable
    {
        yield from [
            // If the rule is only `permit_empty`, any value will pass.
            [
                ['foo' => 'permit_empty|valid_email'],
                [],
                true,
            ],
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
            [
                // Required has more priority
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
            [
                // This tests will return true because the input data is trimmed
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
            [
                // Testing with closure
                ['foo' => ['permit_empty', static fn ($value) => true]],
                ['foo' => ''],
                true,
            ],
        ];
    }

    #[DataProvider('provideMatches')]
    public function testMatches(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'matches[bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideMatches(): iterable
    {
        yield from [
            'foo bar not exist'        => [[], false],
            'bar not exist'            => [['foo' => null], false],
            'foo not exist'            => [['bar' => null], false],
            'foo bar null'             => [['foo' => null, 'bar' => null], false], // Strict Rule: true
            'foo bar string match'     => [['foo' => 'match', 'bar' => 'match'], true],
            'foo bar string not match' => [['foo' => 'match', 'bar' => 'nope'], false],
            'foo bar float match'      => [['foo' => 1.2, 'bar' => 1.2], true],
            'foo bar float not match'  => [['foo' => 1.2, 'bar' => 2.3], false],
            'foo bar bool match'       => [['foo' => true, 'bar' => true], true],
        ];
    }

    #[DataProvider('provideMatchesNestedCases')]
    public function testMatchesNested(array $data, bool $expected): void
    {
        $this->validation->setRules(['nested.foo' => 'matches[nested.bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideMatchesNestedCases(): iterable
    {
        yield from [
            [['nested' => ['foo' => 'match', 'bar' => 'match']], true],
            [['nested' => ['foo' => 'match', 'bar' => 'nope']], false],
        ];
    }

    #[DataProvider('provideDiffers')]
    public function testDiffers(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'differs[bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideDiffers(): iterable
    {
        yield from [
            'foo bar not exist'        => [[], false],
            'bar not exist'            => [['foo' => null], false],
            'foo not exist'            => [['bar' => null], false],
            'foo bar null'             => [['foo' => null, 'bar' => null], false],
            'foo bar string match'     => [['foo' => 'match', 'bar' => 'match'], false],
            'foo bar string not match' => [['foo' => 'match', 'bar' => 'nope'], true],
            'foo bar float match'      => [['foo' => 1.2, 'bar' => 1.2], false],
            'foo bar float not match'  => [['foo' => 1.2, 'bar' => 2.3], true],
            'foo bar bool match'       => [['foo' => true, 'bar' => true], false],
        ];
    }

    #[DataProvider('provideMatchesNestedCases')]
    public function testDiffersNested(array $data, bool $expected): void
    {
        $this->validation->setRules(['nested.foo' => 'differs[nested.bar]']);
        $this->assertSame(! $expected, $this->validation->run($data));
    }

    #[DataProvider('provideEquals')]
    public function testEquals(array $data, string $param, bool $expected): void
    {
        $this->validation->setRules(['foo' => "equals[{$param}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideEquals(): iterable
    {
        yield from [
            'null'   => [['foo' => null], '', false],
            'empty'  => [['foo' => ''], '', true],
            'fail'   => [['foo' => 'bar'], 'notbar', false],
            'pass'   => [['foo' => 'bar'], 'bar', true],
            'casing' => [['foo' => 'bar'], 'Bar', false],
        ];
    }

    #[DataProvider('provideMinLengthCases')]
    public function testMinLength(?string $data, string $length, bool $expected): void
    {
        $this->validation->setRules(['foo' => "min_length[{$length}]"]);
        $this->assertSame($expected, $this->validation->run(['foo' => $data]));
    }

    public static function provideMinLengthCases(): iterable
    {
        yield from [
            'null'    => [null, '2', false],
            'less'    => ['bar', '2', true],
            'equal'   => ['bar', '3', true],
            'greater' => ['bar', '4', false],
        ];
    }

    #[DataProvider('provideMinLengthCases')]
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
     * @param int|string|null $data
     */
    #[DataProvider('provideExactLength')]
    public function testExactLength($data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'exact_length[3]']);
        $this->assertSame($expected, $this->validation->run(['foo' => $data]));
    }

    public static function provideExactLength(): iterable
    {
        yield from [
            'null'        => [null, false],
            'exact'       => ['bar', true],
            'exact_int'   => [123, true],
            'less'        => ['ba', false],
            'less_int'    => [12, false],
            'greater'     => ['bars', false],
            'greater_int' => [1234, false],
        ];
    }

    public function testExactLengthDetectsBadLength(): void
    {
        $data = ['foo' => 'bar'];
        $this->validation->setRules(['foo' => 'exact_length[abc]']);
        $this->assertFalse($this->validation->run($data));
    }

    #[DataProvider('provideGreaterThan')]
    public function testGreaterThan(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "greater_than[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideGreaterThan(): iterable
    {
        yield from [
            ['-10', '-11', true],
            ['10', '9', true],
            ['10', '10', false],
            ['10.1', '10', true],
            ['10.0', '10', false],
            ['9.9', '10', false],
            ['10', 'a', false],
            ['10a', '10', false],
            [null, null, false],
        ];
    }

    #[DataProvider('provideGreaterThanEqual')]
    public function testGreaterThanEqual(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "greater_than_equal_to[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideGreaterThanEqual(): iterable
    {
        yield from [
            ['0', '0', true],
            ['1', '0', true],
            ['-1', '0', false],
            ['1.0', '1', true],
            ['1.1', '1', true],
            ['0.9', '1', false],
            ['10a', '0', false],
            [null, null, false],
            ['1', null, true],
            [null, '1', false],
        ];
    }

    #[DataProvider('provideLessThan')]
    public function testLessThan(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "less_than[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideLessThan(): iterable
    {
        yield from [
            ['-10', '-11', false],
            ['9', '10', true],
            ['10', '9', false],
            ['10', '10', false],
            ['9.9', '10', true],
            ['10.1', '10', false],
            ['10.0', '10', false],
            ['10', 'a', true],
            ['10a', '10', false],
            [null, null, false],
        ];
    }

    #[DataProvider('provideLessThanEqual')]
    public function testLessThanEqual(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "less_than_equal_to[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideLessThanEqual(): iterable
    {
        yield from [
            ['0', '0', true],
            ['1', '0', false],
            ['-1', '0', true],
            ['1.0', '1', true],
            ['0.9', '1', true],
            ['1.1', '1', false],
            ['10a', '0', false],
            [null, null, false],
            ['1', null, false],
            [null, '1', false],
        ];
    }

    #[DataProvider('provideInList')]
    public function testInList(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "in_list[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    #[DataProvider('provideInList')]
    public function testNotInList(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "not_in_list[{$second}]"]);
        $this->assertSame(! $expected, $this->validation->run($data));
    }

    public static function provideInList(): iterable
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

    #[DataProvider('provideRequiredWith')]
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

    public static function provideRequiredWith(): iterable
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
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7557
     */
    #[DataProvider('provideRequiredWithAndOtherRules')]
    public function testRequiredWithAndOtherRules(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'mustBeADate' => 'required_with[otherField]|permit_empty|valid_date',
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public static function provideRequiredWithAndOtherRules(): iterable
    {
        yield from [
            // `otherField` and `mustBeADate` do not exist
            [true, []],
            // `mustBeADate` does not exist
            [false, ['otherField' => 'exists']],
            // ``otherField` does not exist
            [true, ['mustBeADate' => '2023-06-12']],
            [true, ['mustBeADate' => '']],
            [true, ['mustBeADate' => null]],
            [true, ['mustBeADate' => []]],
            // `otherField` and `mustBeADate` exist
            [true, ['mustBeADate' => '', 'otherField' => '']],
            [true, ['mustBeADate' => '2023-06-12', 'otherField' => 'exists']],
            [true, ['mustBeADate' => '2023-06-12', 'otherField' => '']],
            [false, ['mustBeADate' => '', 'otherField' => 'exists']],
            [false, ['mustBeADate' => [], 'otherField' => 'exists']],
            [false, ['mustBeADate' => null, 'otherField' => 'exists']],
        ];
    }

    #[DataProvider('provideRequiredWithAndOtherRuleWithValueZero')]
    public function testRequiredWithAndOtherRuleWithValueZero(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'married'      => ['rules' => ['in_list[0,1]']],
            'partner_name' => ['rules' => ['permit_empty', 'required_with[married]', 'alpha_space']],
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public static function provideRequiredWithAndOtherRuleWithValueZero(): iterable
    {
        yield from [
            [true, ['married' => '0', 'partner_name' => '']],
            [true, ['married' => '1', 'partner_name' => 'Foo']],
            [false, ['married' => '1', 'partner_name' => '']],
        ];
    }

    #[DataProvider('provideRequiredWithout')]
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

    public static function provideRequiredWithout(): iterable
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

    #[DataProvider('provideRequiredWithoutMultiple')]
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

    public static function provideRequiredWithoutMultiple(): iterable
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

    #[DataProvider('provideRequiredWithoutMultipleWithoutFields')]
    public function testRequiredWithoutMultipleWithoutFields(array $data, bool $result): void
    {
        $this->validation->setRules(['foo' => 'required_without[bar,baz]']);

        $this->assertSame($result, $this->validation->run($data));
    }

    public static function provideRequiredWithoutMultipleWithoutFields(): iterable
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

    #[DataProvider('provideFieldExists')]
    public function testFieldExists(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideFieldExists(): iterable
    {
        // Do not use `foo`, because there is a lang file `Foo`, and
        // the error message may be messed up.
        yield from [
            'empty string' => [
                ['fiz' => 'field_exists'],
                ['fiz' => ''],
                true,
            ],
            'null' => [
                ['fiz' => 'field_exists'],
                ['fiz' => null],
                true,
            ],
            'false' => [
                ['fiz' => 'field_exists'],
                ['fiz' => false],
                true,
            ],
            'empty array' => [
                ['fiz' => 'field_exists'],
                ['fiz' => []],
                true,
            ],
            'empty data' => [
                ['fiz' => 'field_exists'],
                [],
                false,
            ],
            'dot array syntax: true' => [
                ['fiz.bar' => 'field_exists'],
                [
                    'fiz' => ['bar' => null],
                ],
                true,
            ],
            'dot array syntax: false' => [
                ['fiz.bar' => 'field_exists'],
                [],
                false,
            ],
            'dot array syntax asterisk: true' => [
                ['fiz.*.baz' => 'field_exists'],
                [
                    'fiz' => [
                        'bar' => [
                            'baz' => null,
                        ],
                    ],
                ],
                true,
            ],
            'dot array syntax asterisk: false' => [
                ['fiz.*.baz' => 'field_exists'],
                [
                    'fiz' => [
                        'bar' => [
                            'baz' => null,
                        ],
                        'hoge' => [
                            // 'baz' is missing.
                        ],
                    ],
                ],
                false,
            ],
        ];
    }

    public function testFieldExistsErrorMessage(): void
    {
        $this->validation->setRules(['fiz.*.baz' => 'field_exists']);
        $data = [
            'fiz' => [
                'bar' => [
                    'baz' => null,
                ],
                'hoge' => [
                    // 'baz' is missing.
                ],
            ],
        ];

        $this->assertFalse($this->validation->run($data));
        $this->assertSame(
            ['fiz.*.baz' => 'The fiz.*.baz field must exist.'],
            $this->validation->getErrors()
        );
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('provideRequiredIf')]
    public function testRequiredIf(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'is_internal'     => 'in_list[0,1,2,3]|permit_empty',
            'identity_number' => 'required_if[is_internal,1,2]',
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public static function provideRequiredIf(): Generator
    {
        yield from [
            // `is_internal` and `identity_number` do not exist
            [true, []],
            // `identity_number` is not required because field `is_internal`
            // value does not match with any value in the rule params
            [true, ['is_internal' => '', 'identity_number' => '']],
            [true, ['is_internal' => '0', 'identity_number' => '']],
            [true, ['is_internal' => '3', 'identity_number' => '']],
            // `identity_number` is required and exist
            [false, ['is_internal' => '1', 'identity_number' => '']],
            [false, ['is_internal' => '2', 'identity_number' => '']],
            [true, ['is_internal' => '1', 'identity_number' => '123']],
            [true, ['is_internal' => '2', 'identity_number' => '123']],
            // `identity_number` is required but do not exist
            [false, ['is_internal' => '1']],
            [false, ['is_internal' => '2']],
            // `identity_number` with integer value
            [true, ['is_internal' => '1', 'identity_number' => '3207783']],
            [true, ['is_internal' => '2', 'identity_number' => '3207783']],
            [true, ['identity_number' => '3207783']],
            [true, ['identity_number' => '3207783']],
            // `identity_number` with string value
            [true, ['is_internal' => '1', 'identity_number' => 'a']],
            [true, ['is_internal' => '2', 'identity_number' => 'b']],
            [true, ['identity_number' => 'a']],
            [true, ['identity_number' => 'b']],
        ];
    }

    /**
     * @param array<string, mixed> $data
     */
    #[DataProvider('provideRequiredIfWorkWithOtherRule')]
    public function testRequiredIfWorkWithOtherRule(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'is_internal'     => 'in_list[0,1,2,3]|permit_empty',
            'identity_number' => 'required_if[is_internal,1,2]|permit_empty|integer',
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public static function provideRequiredIfWorkWithOtherRule(): Generator
    {
        yield from [
            // `identity_number` with integer value
            [true, ['is_internal' => '1', 'identity_number' => '3207783']],
            [true, ['is_internal' => '2', 'identity_number' => '3207783']],
            // `identity_number` is not empty, but it is not an integer
            // value, which triggers the Validation.integer error message.
            [false, ['is_internal' => '1', 'identity_number' => 'a']],
            [false, ['is_internal' => '2', 'identity_number' => 'b']],
        ];
    }
}
