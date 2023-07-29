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
use stdClass;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 *
 * @group Others
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

    public function provideRequiredCases(): iterable
    {
        yield [['foo' => null], false];

        yield [['foo' => 123], true];

        yield [['foo' => null, 'bar' => 123], false];

        yield [['foo' => [123]], true];

        yield [['foo' => []], false];

        yield [['foo' => new stdClass()], true];
    }

    /**
     * @dataProvider ifExistProvider
     */
    public function testIfExist(array $rules, array $data, bool $expected): void
    {
        $this->validation->setRules($rules);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function ifExistProvider(): iterable
    {
        yield [
            ['foo' => 'required'],
            ['foo' => ''],
            false,
        ];

        yield [
            ['foo' => 'required'],
            ['foo' => null],
            false,
        ];

        yield [
            ['foo' => 'if_exist|required'],
            ['foo' => ''],
            false,
        ];

        // Input data does not exist then the other rules will be ignored
        yield [
            ['foo' => 'if_exist|required'],
            [],
            true,
        ];

        // Testing for multi-dimensional data
        yield [
            ['foo.bar' => 'if_exist|required'],
            ['foo' => ['bar' => '']],
            false,
        ];

        yield [
            ['foo.bar' => 'if_exist|required'],
            ['foo' => []],
            true,
        ];

        // Testing with closure
        yield [
            ['foo' => ['if_exist', static fn ($value) => true]],
            ['foo' => []],
            true,
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

    public function providePermitEmptyCases(): iterable
    {
        yield // If the rule is only `permit_empty`, any value will pass.
        [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => ''],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => '0'],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => '-0'],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_emails'],
            ['foo' => 0],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => -0],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => 0.0],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_emails'],
            ['foo' => '0.0'],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => -0.0],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => '-0.0'],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => null],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => false],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => 'user@domain.tld'],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|valid_email'],
            ['foo' => 'invalid'],
            false,
        ];

        // Required has more priority
        yield [
            ['foo' => 'permit_empty|required|valid_email'],
            ['foo' => ''],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => ''],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => null],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => false],
            false,
        ];

        // This tests will return true because the input data is trimmed
        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => '0'],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => 0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required'],
            ['foo' => 0.0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_with[bar]'],
            ['foo' => ''],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_with[bar]'],
            ['foo' => 0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_with[bar]'],
            ['foo' => 0.0, 'bar' => 1],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_with[bar]'],
            ['foo' => '', 'bar' => 1],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|required_without[bar]'],
            ['foo' => ''],
            false,
        ];

        yield [
            ['foo' => 'permit_empty|required_without[bar]'],
            ['foo' => 0],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_without[bar]'],
            ['foo' => 0.0, 'bar' => 1],
            true,
        ];

        yield [
            ['foo' => 'permit_empty|required_without[bar]'],
            ['foo' => '', 'bar' => 1],
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
     * @dataProvider provideMatchesCases
     */
    public function testMatches(array $data, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'matches[bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideMatchesCases(): iterable
    {
        yield [['foo' => null, 'bar' => null], true];

        yield [['foo' => 'match', 'bar' => 'match'], true];

        yield [['foo' => 'match', 'bar' => 'nope'], false];
    }

    /**
     * @dataProvider provideMatchesNestedCases
     */
    public function testMatchesNested(array $data, bool $expected): void
    {
        $this->validation->setRules(['nested.foo' => 'matches[nested.bar]']);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function provideMatchesNestedCases(): iterable
    {
        yield [['nested' => ['foo' => 'match', 'bar' => 'match']], true];

        yield [['nested' => ['foo' => 'match', 'bar' => 'nope']], false];
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

    public function provideEqualsCases(): iterable
    {
        yield 'null' => [['foo' => null], '', false];

        yield 'empty' => [['foo' => ''], '', true];

        yield 'fail' => [['foo' => 'bar'], 'notbar', false];

        yield 'pass' => [['foo' => 'bar'], 'bar', true];

        yield 'casing' => [['foo' => 'bar'], 'Bar', false];
    }

    /**
     * @dataProvider provideMinLengthCases
     */
    public function testMinLength(?string $data, string $length, bool $expected): void
    {
        $this->validation->setRules(['foo' => "min_length[{$length}]"]);
        $this->assertSame($expected, $this->validation->run(['foo' => $data]));
    }

    public function provideMinLengthCases(): iterable
    {
        yield 'null' => [null, '2', false];

        yield 'less' => ['bar', '2', true];

        yield 'equal' => ['bar', '3', true];

        yield 'greater' => ['bar', '4', false];
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

    public function provideExactLengthCases(): iterable
    {
        yield 'null' => [null, false];

        yield 'exact' => ['bar', true];

        yield 'less' => ['ba', false];

        yield 'greater' => ['bars', false];
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

    public function greaterThanProvider(): iterable
    {
        yield ['-10', '-11', true];

        yield ['10', '9', true];

        yield ['10', '10', false];

        yield ['10.1', '10', true];

        yield ['10.0', '10', false];

        yield ['9.9', '10', false];

        yield ['10', 'a', false];

        yield ['10a', '10', false];

        yield [null, null, false];
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

    public function greaterThanEqualProvider(): iterable
    {
        yield ['0', '0', true];

        yield ['1', '0', true];

        yield ['-1', '0', false];

        yield ['1.0', '1', true];

        yield ['1.1', '1', true];

        yield ['0.9', '1', false];

        yield ['10a', '0', false];

        yield [null, null, false];

        yield ['1', null, true];

        yield [null, '1', false];
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

    public function lessThanProvider(): iterable
    {
        yield ['-10', '-11', false];

        yield ['9', '10', true];

        yield ['10', '9', false];

        yield ['10', '10', false];

        yield ['9.9', '10', true];

        yield ['10.1', '10', false];

        yield ['10.0', '10', false];

        yield ['10', 'a', true];

        yield ['10a', '10', false];

        yield [null, null, false];
    }

    /**
     * @dataProvider lessThanEqualProvider
     */
    public function testLessThanEqual(?string $first, ?string $second, bool $expected): void
    {
        $data = ['foo' => $first];
        $this->validation->setRules(['foo' => "less_than_equal_to[{$second}]"]);
        $this->assertSame($expected, $this->validation->run($data));
    }

    public function lessThanEqualProvider(): iterable
    {
        yield ['0', '0', true];

        yield ['1', '0', false];

        yield ['-1', '0', true];

        yield ['1.0', '1', true];

        yield ['0.9', '1', true];

        yield ['1.1', '1', false];

        yield ['10a', '0', false];

        yield [null, null, false];

        yield ['1', null, false];

        yield [null, '1', false];
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

    public function inListProvider(): iterable
    {
        yield ['red', 'red,Blue,123', true];

        yield ['Blue', 'red, Blue,123', true];

        yield ['Blue', 'red,Blue,123', true];

        yield ['123', 'red,Blue,123', true];

        yield ['Red', 'red,Blue,123', false];

        yield [' red', 'red,Blue,123', false];

        yield ['1234', 'red,Blue,123', false];

        yield [null, 'red,Blue,123', false];

        yield ['red', null, false];
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

    public function requiredWithProvider(): iterable
    {
        yield ['nope', 'bar', false];

        yield ['foo', 'bar', true];

        yield ['nope', 'baz', true];

        yield [null, null, true];

        yield [null, 'foo', false];

        yield ['foo', null, true];

        yield [
            'array.emptyField1',
            'array.emptyField2',
            true,
        ];

        yield [
            'array.nonEmptyField1',
            'array.emptyField2',
            true,
        ];

        yield [
            'array.emptyField1',
            'array.nonEmptyField2',
            false,
        ];

        yield [
            'array.nonEmptyField1',
            'array.nonEmptyField2',
            true,
        ];
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/7557
     *
     * @dataProvider RequiredWithAndOtherRulesProvider
     */
    public function testRequiredWithAndOtherRules(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'mustBeADate' => 'required_with[otherField]|permit_empty|valid_date',
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public function RequiredWithAndOtherRulesProvider(): iterable
    {
        // `otherField` and `mustBeADate` do not exist
        yield [true, []];

        // `mustBeADate` does not exist
        yield [false, ['otherField' => 'exists']];

        // ``otherField` does not exist
        yield [true, ['mustBeADate' => '2023-06-12']];

        yield [true, ['mustBeADate' => '']];

        yield [true, ['mustBeADate' => null]];

        yield [true, ['mustBeADate' => []]];

        // `otherField` and `mustBeADate` exist
        yield [true, ['mustBeADate' => '', 'otherField' => '']];

        yield [true, ['mustBeADate' => '2023-06-12', 'otherField' => 'exists']];

        yield [true, ['mustBeADate' => '2023-06-12', 'otherField' => '']];

        yield [false, ['mustBeADate' => '', 'otherField' => 'exists']];

        yield [false, ['mustBeADate' => [], 'otherField' => 'exists']];

        yield [false, ['mustBeADate' => null, 'otherField' => 'exists']];
    }

    /**
     * @dataProvider RequiredWithAndOtherRuleWithValueZeroProvider
     */
    public function testRequiredWithAndOtherRuleWithValueZero(bool $expected, array $data): void
    {
        $this->validation->setRules([
            'married'      => ['rules' => ['in_list[0,1]']],
            'partner_name' => ['rules' => ['permit_empty', 'required_with[married]', 'alpha_space']],
        ]);

        $result = $this->validation->run($data);

        $this->assertSame($expected, $result);
    }

    public function RequiredWithAndOtherRuleWithValueZeroProvider(): iterable
    {
        yield [true, ['married' => '0', 'partner_name' => '']];

        yield [true, ['married' => '1', 'partner_name' => 'Foo']];

        yield [false, ['married' => '1', 'partner_name' => '']];
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

    public function requiredWithoutProvider(): iterable
    {
        yield ['nope', 'bars', false];

        yield ['foo', 'nope', true];

        yield [null, null, false];

        yield [null, 'foo', true];

        yield ['foo', null, true];

        yield [
            'array.emptyField1',
            'array.emptyField2',
            false,
        ];

        yield [
            'array.nonEmptyField1',
            'array.emptyField2',
            true,
        ];

        yield [
            'array.emptyField1',
            'array.nonEmptyField2',
            true,
        ];

        yield [
            'array.nonEmptyField1',
            'array.nonEmptyField2',
            true,
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

    public function requiredWithoutMultipleProvider(): iterable
    {
        yield 'all empty' => [
            '',
            '',
            '',
            false,
        ];

        yield 'foo is not empty' => [
            'a',
            '',
            '',
            true,
        ];

        yield 'bar is not empty' => [
            '',
            'b',
            '',
            false,
        ];

        yield 'baz is not empty' => [
            '',
            '',
            'c',
            false,
        ];

        yield 'bar,baz are not empty' => [
            '',
            'b',
            'c',
            true,
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

    public function requiredWithoutMultipleWithoutFieldsProvider(): iterable
    {
        yield 'baz is missing' => [
            [
                'foo' => '',
                'bar' => '',
            ],
            false,
        ];

        yield 'bar,baz are missing' => [
            [
                'foo' => '',
            ],
            false,
        ];

        yield 'bar is not empty' => [
            [
                'foo' => '',
                'bar' => 'b',
            ],
            false,
        ];

        yield 'foo is not empty' => [
            [
                'foo' => 'a',
            ],
            true,
        ];
    }
}
