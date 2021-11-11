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
use Generator;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class ValidationTest extends CIUnitTestCase
{
    /**
     * @var Validation
     */
    private $validation;

    private $config = [
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
        $this->assertSame($rules, $this->validation->getRules());
    }

    public function testRunReturnsFalseWithNothingToDo(): void
    {
        $this->validation->setRules([]);
        $this->assertFalse($this->validation->run([]));
    }

    public function testRunDoesTheBasics(): void
    {
        $data = ['foo' => 'notanumber'];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->assertFalse($this->validation->run($data));
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
        $data = ['foo' => 'notanumber'];

        $messages = [
            'foo' => [
                'is_numeric' => 'Nope. Not a number.',
            ],
        ];

        $this->validation->setRules(['foo' => 'is_numeric'], $messages);
        $this->validation->run($data);
        $this->assertSame('Nope. Not a number.', $this->validation->getError('foo'));
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
        $this->validation->run($data);
        $this->assertSame(['foo' => 'Validation.is_numeric'], $this->validation->getErrors());
    }

    public function testGetErrorsWhenNone(): void
    {
        $data = ['foo' => 123];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->validation->run($data);
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
        $this->validation->run(['foo' => 'bar']);
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
            'foo' => 'required|min_length[5]',
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
     * @dataProvider rulesSetupProvider
     *
     * @param string|string[] $rules
     * @param string          $expected
     */
    public function testRulesSetup($rules, $expected, array $errors = [])
    {
        $data = ['foo' => ''];
        $this->validation->setRules(['foo' => $rules], $errors);
        $this->validation->run($data);
        $this->assertSame($expected, $this->validation->getError('foo'));
    }

    public function rulesSetupProvider(): Generator
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
                [
                    'label' => 'Foo Bar',
                    'rules' => 'min_length[10]',
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
        $rawstring = 'username=admin001&role=administrator&usepass=0';

        $data = [
            'username' => 'admin001',
            'role'     => 'administrator',
            'usepass'  => 0,
        ];

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $rawstring, new UserAgent());
        $this->validation->withRequest($request->withMethod('patch'))->run($data);
        $this->assertSame([], $this->validation->getErrors());
    }

    public function testJsonInput(): void
    {
        $data = [
            'username' => 'admin001',
            'role'     => 'administrator',
            'usepass'  => 0,
        ];
        $json = json_encode($data);

        $_SERVER['CONTENT_TYPE'] = 'application/json';

        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), $json, new UserAgent());

        $rules = [
            'role' => 'required|min_length[5]',
        ];
        $validated = $this->validation
            ->withRequest($request->withMethod('patch'))
            ->setRules($rules)
            ->run();

        $this->assertTrue($validated);
        $this->assertSame([], $this->validation->getErrors());

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
        $data = ['foo' => 'notanumber'];
        $this->validation->setRules(['foo' => 'is_numeric']);
        $this->validation->run($data);
        $this->assertTrue($this->validation->hasError('foo'));
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
        $this->validation->run($data);
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

        $data = (object) ['configuration' => (object) ['first' => 1, 'second' => 2]];
        $this->validation->run((array) $data);
        $this->assertSame([], $this->validation->getErrors());

        $this->validation->reset();
        $this->validation->setRules([
            'configuration' => 'required|check_object_rule',
        ]);

        $data = (object) ['configuration' => (object) ['first1' => 1, 'second' => 2]];
        $this->validation->run((array) $data);

        $this->assertSame([
            'configuration' => 'Validation.check_object_rule',
        ], $this->validation->getErrors());
    }

    /**
     * @dataProvider arrayFieldDataProvider
     */
    public function testRulesForArrayField(array $body, array $rules, array $results)
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), http_build_query($body), new UserAgent());

        $this->validation->setRules($rules);
        $this->validation->withRequest($request->withMethod('post'))->run($body);
        $this->assertSame($results, $this->validation->getErrors());
    }

    public function arrayFieldDataProvider(): Generator
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
                'xyz098',
            ],
        ];

        $request = new IncomingRequest($config, new URI(), 'php://input', new UserAgent());

        $this->validation->setRules([
            'id_user.*'   => 'numeric',
            'name_user.*' => 'alpha',
        ]);

        $this->validation->withRequest($request->withMethod('post'))->run();
        $this->assertSame([
            'id_user.*'   => 'The id_user.* field must contain only numbers.',
            'name_user.*' => 'The name_user.* field may only contain alphabetical characters.',
        ], $this->validation->getErrors());
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

        $this->validation->run($data);
        $errors = $this->validation->getErrors();

        if (! isset($errors['Username'])) {
            $this->fail('Unable to find "Username"');
        }

        $expected = 'Supplied value (Pizza) for Foo Bar Translated must have at least 6 characters.';
        $this->assertSame($expected, $errors['Username']);
    }

    /**
     * @dataProvider dotNotationForIfExistProvider
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4521
     */
    public function testDotNotationOnIfExistRule(bool $expected, array $rules, array $data): void
    {
        $actual = $this->validation->setRules($rules)->run($data);
        $this->assertSame($expected, $actual);
    }

    public function dotNotationForIfExistProvider(): Generator
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
            ['fizz' => [
                'bar' => ['baz' => 'yes'],
            ]],
        ];

        yield 'dot-on-middle-pass' => [
            true,
            ['fizz.*.baz' => 'if_exist|numeric'],
            ['fizz' => [
                'bar' => ['baz' => 30],
            ]],
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
     * @dataProvider validationArrayDataCaseProvider
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4510
     */
    public function testValidationOfArrayData(bool $expected, array $rules, array $data): void
    {
        $actual = $this->validation->setRules($rules)->run($data);
        $this->assertSame($expected, $actual);
    }

    public function validationArrayDataCaseProvider(): iterable
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
    }

    /**
     * @dataProvider provideStringRulesCases
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4929
     */
    public function testSplittingOfComplexStringRules(string $input, array $expected): void
    {
        $splitter = $this->getPrivateMethodInvoker($this->validation, 'splitRules');
        $this->assertSame($expected, $splitter($input));
    }

    public function provideStringRulesCases(): iterable
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
}
