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

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Config\Services;
use PHPUnit\Framework\ExpectationFailedException;
use Tests\Support\Validation\TestRules;
use TypeError;

/**
 * @internal
 *
 * @group Others
 *
 * @no-final
 */
class ValidationTest extends CIUnitTestCase
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
        'login' => [
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'custom username required error msg.',
                ],
            ],
            'password' => [
                'label'  => 'Password',
                'rules'  => 'required',
                'errors' => [
                    'required' => 'custom password required error msg.',
                ],
            ],
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.',
            ],
        ],
        'groupX'    => 'Not an array, so not a real group',
        'templates' => [
            'list'   => 'CodeIgniter\Validation\Views\list',
            'single' => 'CodeIgniter\Validation\Views\single',
        ],
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();
    }

    public function testSetRulesStoresRules(): void
    {
        $rules = [
            'foo' => 'bar|baz',
            'bar' => 'baz|belch',
        ];
        $this->validation->setRules($rules);

        $expected = [
            'foo' => ['rules' => ['bar', 'baz']],
            'bar' => ['rules' => ['baz', 'belch']],
        ];
        $this->assertSame($expected, $this->validation->getRules());
    }

    public function testSetRuleStoresRule(): void
    {
        $this->validation->setRules([]);
        $this->validation->setRule('foo', null, 'bar|baz');

        $this->assertSame([
            'foo' => [
                'label' => null,
                'rules' => ['bar', 'baz'],
            ],
        ], $this->validation->getRules());
    }

    public function testSetRuleMultipleWithIndividual(): void
    {
        $this->validation->setRule('username', 'Username', 'required|min_length[3]');
        $this->validation->setRule('password', 'Password', ['required', 'min_length[8]', 'alpha_numeric_punct']);

        $this->assertSame([
            'username' => [
                'label' => 'Username',
                'rules' => ['required', 'min_length[3]'],
            ],
            'password' => [
                'label' => 'Password',
                'rules' => [
                    'required',
                    'min_length[8]',
                    'alpha_numeric_punct',
                ],
            ],
        ], $this->validation->getRules());
    }

    public function testSetRuleAddsRule(): void
    {
        $this->validation->setRules([
            'bar' => [
                'label' => null,
                'rules' => 'bar|baz',
            ],
        ]);
        $this->validation->setRule('foo', null, 'foo|foz');

        $this->assertSame([
            'bar' => [
                'label' => null,
                'rules' => ['bar', 'baz'],
            ],
            'foo' => [
                'label' => null,
                'rules' => ['foo', 'foz'],
            ],
        ], $this->validation->getRules());
    }

    public function testSetRuleOverwritesRule(): void
    {
        $this->validation->setRules([
            'foo' => [
                'label' => null,
                'rules' => 'bar|baz',
            ],
        ]);
        $this->validation->setRule('foo', null, 'foo|foz');

        $this->assertSame([
            'foo' => [
                'label' => null,
                'rules' => ['foo', 'foz'],
            ],
        ], $this->validation->getRules());
    }

    public function testSetRuleOverwritesRuleReverse(): void
    {
        $this->validation->setRule('foo', null, 'foo|foz');
        $this->validation->setRules([
            'foo' => [
                'label' => null,
                'rules' => 'bar|baz',
            ],
        ]);

        $this->assertSame([
            'foo' => [
                'label' => null,
                'rules' => ['bar', 'baz'],
            ],
        ], $this->validation->getRules());
    }

    /**
     * @dataProvider provideSetRuleRulesFormat
     *
     * @param mixed $rules
     */
    public function testSetRuleRulesFormat(bool $expected, $rules): void
    {
        if (! $expected) {
            $this->expectException(TypeError::class);
            $this->expectExceptionMessage('$rules must be of type string|array');
        }

        $this->validation->setRule('foo', null, $rules);
        $this->addToAssertionCount(1);
    }

    public function provideSetRuleRulesFormat(): iterable
    {
        yield 'fail-simple-object' => [
            false,
            (object) ['required'],
        ];

        yield 'pass-single-string' => [
            true,
            'required',
        ];

        yield 'pass-single-array' => [
            true,
            ['required'],
        ];

        yield 'fail-deep-object' => [
            false,
            new Validation((object) $this->config, Services::renderer()),
        ];

        yield 'pass-multiple-string' => [
            true,
            'required|alpha',
        ];

        yield 'pass-multiple-array' => [
            true,
            ['required', 'alpha'],
        ];
    }

    public function testRunReturnsFalseWithNothingToDo(): void
    {
        $this->validation->setRules([]);
        $this->assertFalse($this->validation->run([]));
        $this->assertSame([], $this->validation->getValidated());
    }

    public function testRuleClassesInstantiatedOnce(): void
    {
        $this->validation->setRules([]);
        $this->validation->run([]);
        $count1 = count(
            $this->getPrivateProperty($this->validation, 'ruleSetInstances')
        );

        $this->validation->run([]);
        $count2 = count(
            $this->getPrivateProperty($this->validation, 'ruleSetInstances')
        );

        $this->assertSame($count1, $count2);
    }

    public function testRunDoesTheBasics(): void
    {
        $data = ['foo' => 'notanumber'];
        $this->validation->setRules(['foo' => 'is_numeric']);

        $this->assertFalse($this->validation->run($data));
        $this->assertSame([], $this->validation->getValidated());
    }

    public function testClosureRule(): void
    {
        $this->validation->setRules(
            [
                'foo' => ['required', static fn ($value) => $value === 'abc'],
            ],
            [
                // Errors
                'foo' => [
                    // Specify the array key for the closure rule.
                    1 => 'The value is not "abc"',
                ],
            ],
        );

        $data   = ['foo' => 'xyz'];
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame(
            ['foo' => 'The value is not "abc"'],
            $this->validation->getErrors()
        );
        $this->assertSame([], $this->validation->getValidated());
    }

    public function testClosureRuleWithParamError(): void
    {
        $this->validation->setRules([
            'foo' => [
                'required',
                static function ($value, $data, &$error, $field) {
                    if ($value !== 'abc') {
                        $error = 'The ' . $field . ' value is not "abc"';

                        return false;
                    }

                    return true;
                },
            ],
        ]);

        $data   = ['foo' => 'xyz'];
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame(
            ['foo' => 'The foo value is not "abc"'],
            $this->validation->getErrors()
        );
        $this->assertSame([], $this->validation->getValidated());
    }

    public function testClosureRuleWithLabel(): void
    {
        $this->validation->setRules([
            'secret' => [
                'label'  => 'シークレット',
                'rules'  => ['required', static fn ($value) => $value === 'abc'],
                'errors' => [
                    // Specify the array key for the closure rule.
                    1 => 'The {field} is invalid',
                ],
            ],
        ]);

        $data   = ['secret' => 'xyz'];
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame(
            ['secret' => 'The シークレット is invalid'],
            $this->validation->getErrors()
        );
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5368
     *
     * @dataProvider provideCanValidatetArrayData
     *
     * @param mixed $value
     */
    public function testCanValidatetArrayData($value, bool $expected): void
    {
        $data = [];
        $this->validation->setRules(['arr' => 'is_array']);

        $data['arr'] = $value;
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideCanValidatetArrayData(): iterable
    {
        yield 'list array' => [
            [1, 2, 3, 4, 5],
            true,
        ];

        yield 'associative array' => [
            [
                'username' => 'admin001',
                'role'     => 'administrator',
                'usepass'  => 0,
            ],
            true,
        ];

        yield 'int' => [
            0,
            false,
        ];

        yield 'string int' => [
            '0',
            false,
        ];

        yield 'bool' => [
            false,
            false,
        ];

        yield 'null' => [
            null,
            false,
        ];
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5374
     *
     * @dataProvider provideIsIntWithInvalidTypeData
     *
     * @param mixed $value
     */
    public function testIsIntWithInvalidTypeData($value, bool $expected): void
    {
        $this->validation->setRules(['foo' => 'is_int']);

        $data = ['foo' => $value];
        $this->assertSame($expected, $this->validation->run($data));
    }

    public static function provideIsIntWithInvalidTypeData(): iterable
    {
        yield 'array with int' => [
            [555],
            false,
        ];

        yield 'empty array' => [
            [],
            false,
        ];

        yield 'bool true' => [
            true,
            false,
        ];

        yield 'bool false' => [
            false,
            false,
        ];

        yield 'null' => [
            null,
            false,
        ];
    }

    public function testRunReturnsLocalizedErrors(): void
    {
        $data = ['foo' => 'notanumber'];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->assertFalse($this->validation->run($data));
        $this->assertSame('Validation.is_numeric', $this->validation->getError('foo'));
    }

    public function testRunWithCustomErrors(): void
    {
        $data = [
            'foo' => 'notanumber',
            'bar' => 'notanumber',
        ];
        $messages = [
            'foo' => [
                'is_numeric' => 'Nope. Not a number.',
            ],
            'bar' => [
                'is_numeric' => 'No. Not a number.',
            ],
        ];
        $this->validation->setRules(['foo' => 'is_numeric', 'bar' => 'is_numeric'], $messages);
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame('Nope. Not a number.', $this->validation->getError('foo'));
        $this->assertSame('No. Not a number.', $this->validation->getError('bar'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6239
     */
    public function testSetRuleWithCustomErrors(): void
    {
        $data = [
            'foo' => 'notanumber',
            'bar' => 'notanumber',
        ];
        $this->validation->setRule(
            'foo',
            'Foo',
            ['foo'        => 'is_numeric'],
            ['is_numeric' => 'Nope. Not a number.']
        );
        $this->validation->setRule(
            'bar',
            'Bar',
            ['bar'        => 'is_numeric'],
            ['is_numeric' => 'Nope. Not a number.']
        );
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame('Nope. Not a number.', $this->validation->getError('foo'));
        $this->assertSame('Nope. Not a number.', $this->validation->getError('bar'));
    }

    public function testCheck(): void
    {
        $this->assertFalse($this->validation->check('notanumber', 'is_numeric'));
    }

    public function testCheckLocalizedError(): void
    {
        $this->assertFalse($this->validation->check('notanumber', 'is_numeric'));
        $this->assertSame('Validation.is_numeric', $this->validation->getError());
    }

    public function testCheckCustomError(): void
    {
        $this->validation->check('notanumber', 'is_numeric', [
            'is_numeric' => 'Nope. Not a number.',
        ]);
        $this->assertSame('Nope. Not a number.', $this->validation->getError());
    }

    public function testGetErrors(): void
    {
        $data = ['foo' => 'notanumber'];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame(['foo' => 'Validation.is_numeric'], $this->validation->getErrors());
    }

    public function testGetErrorsWhenNone(): void
    {
        $data = ['foo' => 123];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $result = $this->validation->run($data);

        $this->assertTrue($result);
        $this->assertSame([], $this->validation->getErrors());
    }

    public function testSetErrors(): void
    {
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->validation->setError('foo', 'Nadda');
        $this->assertSame(['foo' => 'Nadda'], $this->validation->getErrors());
    }

    public function testRulesReturnErrors(): void
    {
        $this->validation->setRules(['foo' => 'customError']);
        $this->assertFalse($this->validation->run(['foo' => 'bar']));
        $this->assertSame(['foo' => 'My lovely error'], $this->validation->getErrors());
    }

    public function testGroupsReadFromConfig(): void
    {
        $data = ['foo' => 'bar'];
        $this->assertFalse($this->validation->run($data, 'groupA'));
        $this->assertSame('Shame, shame. Too short.', $this->validation->getError('foo'));
    }

    public function testGroupsReadFromConfigValid(): void
    {
        $data = ['foo' => 'barsteps'];
        $this->assertTrue($this->validation->run($data, 'groupA'));
    }

    public function testGetRuleGroup(): void
    {
        $this->assertSame([
            'foo' => 'required|min_length[5]',
        ], $this->validation->getRuleGroup('groupA'));
    }

    public function testGetRuleGroupException(): void
    {
        $this->expectException(ValidationException::class);
        $this->validation->getRuleGroup('groupZ');
    }

    public function testSetRuleGroup(): void
    {
        $this->validation->setRuleGroup('groupA');
        $this->assertSame([
            'foo' => ['rules' => ['required', 'min_length[5]']],
        ], $this->validation->getRules());
    }

    public function testSetRuleGroupException(): void
    {
        $this->expectException(ValidationException::class);
        $this->validation->setRuleGroup('groupZ');
    }

    public function testSetRuleGroupWithCustomErrorMessage(): void
    {
        $this->validation->reset();
        $this->validation->setRuleGroup('login');
        $this->validation->run(['username' => 'codeigniter']);

        $this->assertSame([
            'password' => 'custom password required error msg.',
        ], $this->validation->getErrors());
    }

    public function testRunGroupWithCustomErrorMessage(): void
    {
        $this->validation->reset();
        $this->validation->run([
            'username' => 'codeigniter',
        ], 'login');

        $this->assertSame([
            'password' => 'custom password required error msg.',
        ], $this->validation->getErrors());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6245
     */
    public function testRunWithCustomErrorsAndAsteriskField(): void
    {
        $data = [
            'foo' => [
                ['bar' => null],
                ['bar' => null],
            ],
        ];
        $this->validation->setRules(
            ['foo.*.bar' => ['label' => 'foo bar', 'rules' => 'required']],
            ['foo.*.bar' => ['required' => 'Required']]
        );
        $this->validation->run($data);
        $this->assertSame([
            'foo.0.bar' => 'Required',
            'foo.1.bar' => 'Required',
        ], $this->validation->getErrors());
    }

    /**
     * @dataProvider provideRulesSetup
     *
     * @param string|string[] $rules
     * @param string          $expected
     */
    public function testRulesSetup($rules, $expected, array $errors = []): void
    {
        $data = ['foo' => ''];
        $this->validation->setRules(['foo' => $rules], $errors);
        $this->validation->run($data);

        $this->assertSame($expected, $this->validation->getError('foo'));
    }

    public static function provideRulesSetup(): iterable
    {
        yield from [
            [
                'min_length[10]',
                'The foo field must be at least 10 characters in length.',
            ],
            [
                'min_length[10]',
                'The foo field is very short.',
                ['foo' => ['min_length' => 'The {field} field is very short.']],
            ],
            [
                ['min_length[10]'],
                'The foo field must be at least 10 characters in length.',
            ],
            [
                ['min_length[10]'],
                'The foo field is very short.',
                ['foo' => ['min_length' => 'The {field} field is very short.']],
            ],
            [
                ['rules' => 'min_length[10]'],
                'The foo field must be at least 10 characters in length.',
            ],
            [
                ['rules' => ['min_length[10]']],
                'The foo field must be at least 10 characters in length.',
            ],
            [
                [
                    'label' => 'Foo Bar',
                    'rules' => 'min_length[10]',
                ],
                'The Foo Bar field must be at least 10 characters in length.',
            ],
            [
                [
                    'label' => 'Foo Bar',
                    'rules' => ['min_length[10]'],
                ],
                'The Foo Bar field must be at least 10 characters in length.',
            ],
            [
                [
                    'label' => 'Foo Bar',
                    'rules' => 'min_length[10]',
                ],
                'The Foo Bar field is very short.',
                ['foo' => ['min_length' => 'The {field} field is very short.']],
            ],
            [
                [
                    'label'  => 'Foo Bar',
                    'rules'  => 'min_length[10]',
                    'errors' => ['min_length' => 'The {field} field is very short.'],
                ],
                'The Foo Bar field is very short.',
            ],
        ];
    }

    public function testSetRulesRemovesErrorsArray(): void
    {
        $rules = [
            'foo' => [
                'label'  => 'Foo Bar',
                'rules'  => 'min_length[10]',
                'errors' => [
                    'min_length' => 'The {field} field is very short.',
                ],
            ],
        ];

        $this->validation->setRules($rules, []);
        $this->validation->run(['foo' => 'abc']);
        $this->assertSame('The Foo Bar field is very short.', $this->validation->getError('foo'));
    }

    public function testInvalidRule(): void
    {
        $this->expectException(ValidationException::class);

        $rules = [
            'foo' => 'bar|baz',
            'bar' => 'baz|belch',
        ];
        $this->validation->setRules($rules);

        $data = ['foo' => ''];
        $this->validation->run($data);
    }

    public function testRawInput(): void
    {
        $rawstring       = 'username=admin001&role=administrator&usepass=0';
        $config          = new App();
        $config->baseURL = 'http://example.com/';
        $request         = new IncomingRequest($config, new URI(), $rawstring, new UserAgent());

        $rules = [
            'role' => 'required|min_length[5]',
        ];
        $result = $this->validation->withRequest($request->withMethod('patch'))->setRules($rules)->run();

        $this->assertTrue($result);
        $this->assertSame([], $this->validation->getErrors());
        $this->assertSame(['role' => 'administrator'], $this->validation->getValidated());
    }

    public function testJsonInput(): void
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $data = [
            'username' => 'admin001',
            'role'     => 'administrator',
            'usepass'  => 0,
        ];
        $json            = json_encode($data);
        $config          = new App();
        $config->baseURL = 'http://example.com/';
        $request         = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $rules = [
            'role' => 'required|min_length[5]',
        ];
        $result = $this->validation
            ->withRequest($request->withMethod('patch'))
            ->setRules($rules)
            ->run();

        $this->assertTrue($result);
        $this->assertSame([], $this->validation->getErrors());
        $this->assertSame(['role' => 'administrator'], $this->validation->getValidated());

        unset($_SERVER['CONTENT_TYPE']);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6466
     */
    public function testJsonInputObjectArray(): void
    {
        $json = <<<'EOL'
            {
                "p": [
                    {
                        "id": "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                    }
                ]
            }
            EOL;

        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $rules = [
            'p' => 'required|array_count[2]',
        ];
        $result = $this->validation
            ->withRequest($request->withMethod('patch'))
            ->setRules($rules)
            ->run();

        $this->assertFalse($result);
        $this->assertSame(['p' => 'Validation.array_count'], $this->validation->getErrors());

        unset($_SERVER['CONTENT_TYPE']);
    }

    public function testHasRule(): void
    {
        $this->validation->setRuleGroup('groupA');
        $this->assertTrue($this->validation->hasRule('foo'));
    }

    public function testNotARealGroup(): void
    {
        $this->expectException(ValidationException::class);
        $this->validation->setRuleGroup('groupX');
        $this->validation->getRuleGroup('groupX');
    }

    public function testBadTemplate(): void
    {
        $this->expectException(ValidationException::class);
        $this->validation->listErrors('obviouslyBadTemplate');
    }

    public function testShowNonError(): void
    {
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->validation->setError('foo', 'Nadda');
        $this->assertSame('', $this->validation->showError('bogus'));
    }

    public function testShowBadTemplate(): void
    {
        $this->expectException(ValidationException::class);
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->validation->setError('foo', 'Nadda');
        $this->assertSame('We should never get here', $this->validation->showError('foo', 'bogus_template'));
    }

    public function testNoRuleSetsSetup(): void
    {
        $this->expectException(ValidationException::class);

        $this->config['ruleSets'] = null;
        $this->validation         = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();
        $this->validation->run(['foo' => '']);
    }

    public function testNotCustomRuleGroup(): void
    {
        $this->expectException(ValidationException::class);
        $data = ['foo' => ''];
        $this->validation->run($data, 'GeorgeRules');
    }

    public function testNotRealCustomRule(): void
    {
        $this->expectException(ValidationException::class);
        $data = ['foo' => ''];
        $this->validation->run($data, 'groupX');
    }

    public function testHasError(): void
    {
        $data = [
            'foo' => 'notanumber',
            'bar' => [
                ['baz' => 'string'],
                ['baz' => ''],
            ],
        ];

        $this->validation->setRules([
            'foo'       => 'is_numeric',
            'bar.*.baz' => 'required',
        ]);

        $this->validation->run($data);
        $this->assertTrue($this->validation->hasError('foo'));
        $this->assertTrue($this->validation->hasError('bar.*.baz'));
        $this->assertFalse($this->validation->hasError('bar.0.baz'));
        $this->assertTrue($this->validation->hasError('bar.1.baz'));
    }

    public function testSplitRulesTrue(): void
    {
        $this->validation->setRules([
            'phone' => 'required|regex_match[/^(01[2689]|09)[0-9]{8}$/]|numeric',
        ]);
        $this->assertTrue($this->validation->run(['phone' => '0987654321']));
    }

    public function testSplitRulesFalse(): void
    {
        $this->validation->setRules([
            'phone' => 'required|regex_match[/^(01[2689]|09)[0-9]{8}$/]|numeric',
        ]);
        $this->assertFalse($this->validation->run(['phone' => '09876543214']));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1201
     */
    public function testSplitNotRegex(): void
    {
        $method = $this->getPrivateMethodInvoker($this->validation, 'splitRules');
        $result = $method('uploaded[avatar]|max_size[avatar,1024]');
        $this->assertSame('uploaded[avatar]', $result[0]);
    }

    public function testSplitRegex(): void
    {
        $method = $this->getPrivateMethodInvoker($this->validation, 'splitRules');
        $result = $method('required|regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]|max_length[10]');
        $this->assertSame('regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]', $result[1]);
    }

    public function testTagReplacement(): void
    {
        $data = ['Username' => 'Pizza'];
        $this->validation->setRules(
            ['Username' => 'min_length[6]'],
            ['Username' => [
                'min_length' => 'Supplied value ({value}) for {field} must have at least {param} characters.',
            ]]
        );
        $result = $this->validation->run($data);

        $this->assertFalse($result);

        $errors = $this->validation->getErrors();

        if (! isset($errors['Username'])) {
            $this->fail('Unable to find "Username"');
        }

        $expected = 'Supplied value (Pizza) for Username must have at least 6 characters.';
        $this->assertSame($expected, $errors['Username']);
    }

    public function testRulesForObjectField(): void
    {
        $this->validation->setRules([
            'configuration' => 'required|check_object_rule',
        ]);

        $data   = (object) ['configuration' => (object) ['first' => 1, 'second' => 2]];
        $result = $this->validation->run((array) $data);

        $this->assertTrue($result);
        $this->assertSame([], $this->validation->getErrors());

        $this->validation->reset();
        $this->validation->setRules([
            'configuration' => 'required|check_object_rule',
        ]);

        $data   = (object) ['configuration' => (object) ['first1' => 1, 'second' => 2]];
        $result = $this->validation->run((array) $data);

        $this->assertFalse($result);
        $this->assertSame([
            'configuration' => 'Validation.check_object_rule',
        ], $this->validation->getErrors());
    }

    /**
     * @dataProvider provideRulesForArrayField
     */
    public function testRulesForArrayField(array $body, array $rules, array $results): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), http_build_query($body), new UserAgent());

        $this->validation->setRules($rules);
        $this->validation->withRequest($request->withMethod('post'))->run($body);
        $this->assertSame($results, $this->validation->getErrors());
    }

    public static function provideRulesForArrayField(): iterable
    {
        yield from [
            'all_rules_should_pass' => [
                'body' => [
                    'foo' => [
                        'a',
                        'b',
                        'c',
                    ],
                ],
                'rules' => [
                    'foo.0' => 'required|alpha|max_length[2]',
                    'foo.1' => 'required|alpha|max_length[2]',
                    'foo.2' => 'required|alpha|max_length[2]',
                ],
                'results' => [],
            ],
            'first_field_will_return_required_error' => [
                'body' => [
                    'foo' => [
                        '',
                        'b',
                        'c',
                    ],
                ],
                'rules' => [
                    'foo.0' => 'required|alpha|max_length[2]',
                    'foo.1' => 'required|alpha|max_length[2]',
                    'foo.2' => 'required|alpha|max_length[2]',
                ],
                'results' => [
                    'foo.0' => 'The foo.0 field is required.',
                ],
            ],
            'first_and second_field_will_return_required_and_min_length_error' => [
                'body' => [
                    'foo' => [
                        '',
                        'b',
                        'c',
                    ],
                ],
                'rules' => [
                    'foo.0' => 'required|alpha|max_length[2]',
                    'foo.1' => 'required|alpha|min_length[2]|max_length[4]',
                    'foo.2' => 'required|alpha|max_length[2]',
                ],
                'results' => [
                    'foo.0' => 'The foo.0 field is required.',
                    'foo.1' => 'The foo.1 field must be at least 2 characters in length.',
                ],
            ],
        ];
    }

    public function testRulesForSingleRuleWithAsteriskWillReturnNoError(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_REQUEST = [
            'id_user' => [
                1,
                3,
            ],
            'name_user' => [
                'abc123',
                'xyz098',
            ],
        ];

        $request = new IncomingRequest($config, new URI(), 'php://input', new UserAgent());

        $this->validation->setRules([
            'id_user.*'   => 'numeric',
            'name_user.*' => 'alpha_numeric',
        ]);

        $this->validation->withRequest($request->withMethod('post'))->run();
        $this->assertSame([], $this->validation->getErrors());
    }

    public function testRulesForSingleRuleWithAsteriskWillReturnError(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_REQUEST = [
            'id_user' => [
                '1dfd',
                3,
            ],
            'name_user' => [
                123,
                'alpha',
                'xyz098',
            ],
            'contacts' => [
                'friends' => [
                    ['name' => ''],
                    ['name' => 'John'],
                ],
            ],
        ];

        $request = new IncomingRequest($config, new URI(), 'php://input', new UserAgent());

        $this->validation->setRules([
            'id_user.*'       => 'numeric',
            'name_user.*'     => 'alpha',
            'contacts.*.name' => 'required',
        ]);

        $this->validation->withRequest($request->withMethod('post'))->run();
        $this->assertSame([
            'id_user.0'               => 'The id_user.* field must contain only numbers.',
            'name_user.0'             => 'The name_user.* field may only contain alphabetical characters.',
            'name_user.2'             => 'The name_user.* field may only contain alphabetical characters.',
            'contacts.friends.0.name' => 'The contacts.*.name field is required.',
        ], $this->validation->getErrors());

        $this->assertSame(
            "The name_user.* field may only contain alphabetical characters.\n"
            . 'The name_user.* field may only contain alphabetical characters.',
            $this->validation->getError('name_user.*')
        );
        $this->assertSame(
            'The contacts.*.name field is required.',
            $this->validation->getError('contacts.*.name')
        );
    }

    public function testRulesForSingleRuleWithSingleValue(): void
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $_REQUEST = [
            'id_user' => 'gh',
        ];

        $request = new IncomingRequest($config, new URI(), 'php://input', new UserAgent());

        $this->validation->setRules([
            'id_user' => 'numeric',
        ]);

        $this->validation->withRequest($request->withMethod('post'))->run();
        $this->assertSame([
            'id_user' => 'The id_user field must contain only numbers.',
        ], $this->validation->getErrors());
    }

    public function testTranslatedLabel(): void
    {
        $rules = [
            'foo' => [
                'label' => 'Foo.bar',
                'rules' => 'min_length[10]',
            ],
        ];

        $this->validation->setRules($rules, []);
        $this->validation->run(['foo' => 'abc']);
        $this->assertSame('The Foo Bar Translated field must be at least 10 characters in length.', $this->validation->getError('foo'));
    }

    public function testTranslatedLabelIsMissing(): void
    {
        $rules = [
            'foo' => [
                'label' => 'Foo.bar.is.missing',
                'rules' => 'min_length[10]',
            ],
        ];

        $this->validation->setRules($rules, []);
        $this->validation->run(['foo' => 'abc']);
        $this->assertSame('The Foo.bar.is.missing field must be at least 10 characters in length.', $this->validation->getError('foo'));
    }

    public function testTranslatedLabelWithCustomErrorMessage(): void
    {
        $rules = [
            'foo' => [
                'label'  => 'Foo.bar',
                'rules'  => 'min_length[10]',
                'errors' => [
                    'min_length' => 'Foo.bar.min_length1',
                ],
            ],
        ];

        $this->validation->setRules($rules, []);
        $this->validation->run(['foo' => 'abc']);
        $this->assertSame('The Foo Bar Translated field is very short.', $this->validation->getError('foo'));
    }

    public function testTranslatedLabelTagReplacement(): void
    {
        $data = ['Username' => 'Pizza'];
        $this->validation->setRules(
            ['Username' => [
                'label' => 'Foo.bar',
                'rules' => 'min_length[6]',
            ]],
            ['Username' => [
                'min_length' => 'Foo.bar.min_length2',
            ]]
        );
        $result = $this->validation->run($data);

        $this->assertFalse($result);

        $errors = $this->validation->getErrors();

        if (! isset($errors['Username'])) {
            $this->fail('Unable to find "Username"');
        }

        $expected = 'Supplied value (Pizza) for Foo Bar Translated must have at least 6 characters.';
        $this->assertSame($expected, $errors['Username']);
    }

    /**
     * @dataProvider provideDotNotationOnIfExistRule
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4521
     */
    public function testDotNotationOnIfExistRule(bool $expected, array $rules, array $data): void
    {
        $actual = $this->validation->setRules($rules)->run($data);
        $this->assertSame($expected, $actual);
    }

    public static function provideDotNotationOnIfExistRule(): iterable
    {
        yield 'dot-on-end-fail' => [
            false,
            ['status.*' => 'if_exist|in_list[status_1,status_2]'],
            ['status'   => ['bad-status']],
        ];

        yield 'dot-on-end-pass' => [
            true,
            ['status.*' => 'if_exist|in_list[status_1,status_2]'],
            ['status'   => ['status_1']],
        ];

        yield 'dot-on-middle-fail' => [
            false,
            ['fizz.*.baz' => 'if_exist|numeric'],
            [
                'fizz' => [
                    'bar' => ['baz' => 'yes'],
                ],
            ],
        ];

        yield 'dot-on-middle-pass' => [
            true,
            ['fizz.*.baz' => 'if_exist|numeric'],
            [
                'fizz' => [
                    'bar' => ['baz' => 30],
                ],
            ],
        ];

        yield 'dot-multiple-fail' => [
            false,
            ['fizz.*.bar.*' => 'if_exist|numeric'],
            ['fizz' => [
                'bos' => [
                    'bar' => [
                        'bub' => 'noo',
                    ],
                ],
            ]],
        ];

        yield 'dot-multiple-pass' => [
            true,
            ['fizz.*.bar.*' => 'if_exist|numeric'],
            ['fizz' => [
                'bos' => [
                    'bar' => [
                        'bub' => 5,
                    ],
                ],
            ]],
        ];
    }

    /**
     * @dataProvider provideValidationOfArrayData
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4510
     */
    public function testValidationOfArrayData(bool $expected, array $rules, array $data): void
    {
        $actual = $this->validation->setRules($rules)->run($data);
        $this->assertSame($expected, $actual);
    }

    public static function provideValidationOfArrayData(): iterable
    {
        yield 'fail-empty-string' => [
            false,
            ['bar.*.foo' => 'required'],
            ['bar' => [
                ['foo' => 'baz'],
                ['foo' => ''],
            ]],
        ];

        yield 'pass-nonempty-string' => [
            true,
            ['bar.*.foo' => 'required'],
            ['bar' => [
                ['foo' => 'baz'],
                ['foo' => 'boz'],
            ]],
        ];

        yield 'fail-empty-array' => [
            false,
            ['bar.*.foo' => 'required'],
            ['bar' => [
                ['foo' => 'baz'],
                ['foo' => []],
            ]],
        ];

        yield 'pass-nonempty-array' => [
            true,
            ['bar.*.foo' => 'required'],
            ['bar' => [
                ['foo' => 'baz'],
                ['foo' => ['boz']],
            ]],
        ];

        yield 'leading-asterisk' => [
            true,
            ['*.foo' => 'required'],
            [['foo' => 'bar']],
        ];
    }

    /**
     * @dataProvider provideSplittingOfComplexStringRules
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4929
     */
    public function testSplittingOfComplexStringRules(string $input, array $expected): void
    {
        $splitter = $this->getPrivateMethodInvoker($this->validation, 'splitRules');
        $this->assertSame($expected, $splitter($input));
    }

    public static function provideSplittingOfComplexStringRules(): iterable
    {
        yield [
            'required',
            ['required'],
        ];

        yield [
            'required|numeric',
            ['required', 'numeric'],
        ];

        yield [
            'required|max_length[500]|hex',
            ['required', 'max_length[500]', 'hex'],
        ];

        yield [
            'required|numeric|regex_match[/[a-zA-Z]+/]',
            ['required', 'numeric', 'regex_match[/[a-zA-Z]+/]'],
        ];

        yield [
            'required|max_length[500]|regex_match[/^;"\'{}\[\]^<>=/]',
            ['required', 'max_length[500]', 'regex_match[/^;"\'{}\[\]^<>=/]'],
        ];

        yield [
            'regex_match[/^;"\'{}\[\]^<>=/]|regex_match[/[^a-z0-9.\|_]+/]',
            ['regex_match[/^;"\'{}\[\]^<>=/]', 'regex_match[/[^a-z0-9.\|_]+/]'],
        ];

        yield [
            'required|regex_match[/^(01[2689]|09)[0-9]{8}$/]|numeric',
            ['required', 'regex_match[/^(01[2689]|09)[0-9]{8}$/]', 'numeric'],
        ];

        yield [
            'required|regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]|max_length[10]',
            ['required', 'regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]', 'max_length[10]'],
        ];

        yield [
            'required|regex_match[/^(01|2689|09)[0-9]{8}$/]|numeric',
            ['required', 'regex_match[/^(01|2689|09)[0-9]{8}$/]', 'numeric'],
        ];
    }

    /**
     * internal method to simplify placeholder replacement test
     * REQUIRES THE RULES TO BE SET FOR THE FIELD "foo"
     *
     * @param array|null $data optional POST data, needs to contain the key $placeholderField to pass
     *
     * @source https://github.com/codeigniter4/CodeIgniter4/pull/3910#issuecomment-784922913
     */
    protected function placeholderReplacementResultDetermination(string $placeholder = 'id', ?array $data = null): void
    {
        if ($data === null) {
            $data = [$placeholder => '12'];
        }

        $validationRules = $this->getPrivateMethodInvoker($this->validation, 'fillPlaceholders')($this->validation->getRules(), $data);
        $fieldRules      = $validationRules['foo']['rules'] ?? $validationRules['foo'];
        if (is_string($fieldRules)) {
            $fieldRules = $this->getPrivateMethodInvoker($this->validation, 'splitRules')($fieldRules);
        }

        // loop all rules for this field
        foreach ($fieldRules as $rule) {
            // only string type rules are supported
            if (is_string($rule)) {
                $this->assertStringNotContainsString('{' . $placeholder . '}', $rule);
            }
        }
    }

    /**
     * @see ValidationTest::placeholderReplacementResultDetermination()
     */
    public function testPlaceholderReplacementTestFails(): void
    {
        // to test if placeholderReplacementResultDetermination() works we provoke and expect an exception
        $this->expectException(ExpectationFailedException::class);
        $this->expectExceptionMessage('Failed asserting that \'filter[{id}]\' does not contain "{id}".');

        $this->validation->setRule('foo', 'foo-label', 'required|filter[{id}]');

        // calling with empty $data should produce an exception since {id} can't be replaced
        $this->placeholderReplacementResultDetermination('id', []);
    }

    public function testPlaceholderReplacementSetSingleRuleString(): void
    {
        $this->validation->setRule('id', null, 'required|is_natural_no_zero');
        $this->validation->setRule('foo', null, 'required|filter[{id}]');

        $this->placeholderReplacementResultDetermination();
    }

    public function testPlaceholderReplacementSetSingleRuleArray(): void
    {
        $this->validation->setRule('id', null, ['required', 'is_natural_no_zero']);
        $this->validation->setRule('foo', null, ['required', 'filter[{id}]']);

        $this->placeholderReplacementResultDetermination();
    }

    public function testPlaceholderReplacementSetMultipleRulesSimpleString(): void
    {
        $this->validation->setRules([
            'id'  => 'required|is_natural_no_zero',
            'foo' => 'required|filter[{id}]',
        ]);

        $this->placeholderReplacementResultDetermination();
    }

    public function testPlaceholderReplacementSetMultipleRulesSimpleArray(): void
    {
        $this->validation->setRules([
            'id'  => ['required', 'is_natural_no_zero'],
            'foo' => ['required', 'filter[{id}]'],
        ]);

        $this->placeholderReplacementResultDetermination();
    }

    public function testPlaceholderReplacementSetMultipleRulesComplexString(): void
    {
        $this->validation->setRules([
            'id' => [
                'rules' => 'required|is_natural_no_zero',
            ],
            'foo' => [
                'rules' => 'required|filter[{id}]',
            ],
        ]);

        $this->placeholderReplacementResultDetermination();
    }

    public function testPlaceholderReplacementSetMultipleRulesComplexArray(): void
    {
        $this->validation->setRules([
            'id' => [
                'rules' => ['required', 'is_natural_no_zero'],
            ],
            'foo' => [
                'rules' => ['required', 'filter[{id}]'],
            ],
        ]);

        $this->placeholderReplacementResultDetermination();
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5922
     */
    public function testNestedArrayThrowsException(): void
    {
        $rule = [
            'customer_account_number' => [
                'label' => 'ACCOUNT NUMBER',
                'rules' => 'required|exact_length[5]',
            ],
            'debit_amount' => [
                'label' => 'DEBIT AMOUNT',
                'rules' => 'required|decimal|is_natural_no_zero',
            ],
            'beneficiaries_accounts.*.account_number' => [
                'label' => 'BENEFICIARY ACCOUNT NUMBER',
                'rules' => 'exact_length[5]',
            ],
            'beneficiaries_accounts.*.credit_amount' => [
                'label' => 'CREDIT AMOUNT',
                'rules' => 'required|decimal|is_natural_no_zero',
            ],
            'beneficiaries_accounts.*.purpose' => [
                'label' => 'PURPOSE',
                'rules' => 'required_without[beneficiaries_accounts.*.account_number]|min_length[3]|max_length[255]',
            ],
        ];

        $this->validation->setRules($rule);
        $data = [
            'customer_account_number' => 'A_490',
            'debit_amount'            => '1500',
            'beneficiaries_accounts'  => [],
        ];
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame([
            'beneficiaries_accounts.*.account_number' => 'The BENEFICIARY ACCOUNT NUMBER field must be exactly 5 characters in length.',
            'beneficiaries_accounts.*.credit_amount'  => 'The CREDIT AMOUNT field is required.',
            'beneficiaries_accounts.*.purpose'        => 'The PURPOSE field is required when BENEFICIARY ACCOUNT NUMBER is not present.',
        ], $this->validation->getErrors());

        $this->validation->reset();
        $this->validation->setRules($rule);
        $data = [
            'customer_account_number' => 'A_490',
            'debit_amount'            => '1500',
            'beneficiaries_accounts'  => [
                'account_1' => [
                    'account_number' => 'A_103',
                    'credit_amount'  => 1000,
                    'purpose'        => 'Personal',
                ],
                'account_2' => [
                    'account_number' => 'A_258',
                    'credit_amount'  => null,
                    'purpose'        => 'A',
                ],
                'account_3' => [
                    'account_number' => '',
                    'credit_amount'  => 2000,
                    'purpose'        => '',
                ],
            ],
        ];
        $result = $this->validation->run($data);

        $this->assertFalse($result);
        $this->assertSame([
            'beneficiaries_accounts.account_3.account_number' => 'The BENEFICIARY ACCOUNT NUMBER field must be exactly 5 characters in length.',
            'beneficiaries_accounts.account_2.credit_amount'  => 'The CREDIT AMOUNT field is required.',
            'beneficiaries_accounts.account_2.purpose'        => 'The PURPOSE field must be at least 3 characters in length.',
            'beneficiaries_accounts.account_3.purpose'        => 'The PURPOSE field is required when BENEFICIARY ACCOUNT NUMBER is not present.',
        ], $this->validation->getErrors());
    }

    public function testRuleWithLeadingAsterisk(): void
    {
        $data = [
            ['foo' => 1],
            ['foo' => null],
        ];

        $this->validation->setRules(['*.foo' => 'required'], ['1.foo' => ['required' => 'Required {field}']]);

        $this->assertFalse($this->validation->run($data));
        $this->assertSame('Required *.foo', $this->validation->getError('*.foo'));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5942
     */
    public function testRequireWithoutWithWildCard(): void
    {
        $data = [
            'a' => [
                ['b' => 1, 'c' => 2],
                ['c' => ''],
            ],
        ];

        $this->validation->setRules([
            'a.*.c' => 'required_without[a.*.b]',
        ])->run($data);

        $this->assertSame(
            'The a.*.c field is required when a.*.b is not present.',
            $this->validation->getError('a.1.c')
        );
    }
}
