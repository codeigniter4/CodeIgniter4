<?php

namespace CodeIgniter\Validation;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Config\Services;
use Tests\Support\Validation\TestRules;

/**
 * @internal
 */
final class ValidationTest extends CIUnitTestCase
{
    /**
     * @var Validation
     */
    protected $validation;
    protected $config = [
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

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        Services::reset(true);

        $this->validation = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();

        $_FILES = [];
    }

    //--------------------------------------------------------------------

    public function testSetRulesStoresRules()
    {
        $rules = [
            'foo' => 'bar|baz',
            'bar' => 'baz|belch',
        ];

        $this->validation->setRules($rules);

        $this->assertSame($rules, $this->validation->getRules());
    }

    //--------------------------------------------------------------------

    public function testRunReturnsFalseWithNothingToDo()
    {
        $this->validation->setRules([]);

        $this->assertFalse($this->validation->run([]));
    }

    //--------------------------------------------------------------------

    public function testRunDoesTheBasics()
    {
        $data = [
            'foo' => 'notanumber',
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->assertFalse($this->validation->run($data));
    }

    //--------------------------------------------------------------------

    public function testRunReturnsLocalizedErrors()
    {
        $data = [
            'foo' => 'notanumber',
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->assertFalse($this->validation->run($data));
        $this->assertSame('Validation.is_numeric', $this->validation->getError('foo'));
    }

    //--------------------------------------------------------------------

    public function testRunWithCustomErrors()
    {
        $data = [
            'foo' => 'notanumber',
        ];

        $messages = [
            'foo' => [
                'is_numeric' => 'Nope. Not a number.',
            ],
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ], $messages);

        $this->validation->run($data);
        $this->assertSame('Nope. Not a number.', $this->validation->getError('foo'));
    }

    //--------------------------------------------------------------------

    public function testCheck()
    {
        $this->assertFalse($this->validation->check('notanumber', 'is_numeric'));
    }

    //--------------------------------------------------------------------

    public function testCheckLocalizedError()
    {
        $this->assertFalse($this->validation->check('notanumber', 'is_numeric'));
        $this->assertSame('Validation.is_numeric', $this->validation->getError());
    }

    //--------------------------------------------------------------------

    public function testCheckCustomError()
    {
        $this->validation->check('notanumber', 'is_numeric', [
            'is_numeric' => 'Nope. Not a number.',
        ]);
        $this->assertSame('Nope. Not a number.', $this->validation->getError());
    }

    //--------------------------------------------------------------------

    public function testGetErrors()
    {
        $data = [
            'foo' => 'notanumber',
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->validation->run($data);

        $this->assertSame(['foo' => 'Validation.is_numeric'], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function testGetErrorsWhenNone()
    {
        $_SESSION = [];

        $data = [
            'foo' => 123,
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->validation->run($data);

        $this->assertSame([], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function testSetErrors()
    {
        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->validation->setError('foo', 'Nadda');

        $this->assertSame(['foo' => 'Nadda'], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function testRulesReturnErrors()
    {
        $this->validation->setRules([
            'foo' => 'customError',
        ]);

        $this->validation->run(['foo' => 'bar']);

        $this->assertSame(['foo' => 'My lovely error'], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function testGroupsReadFromConfig()
    {
        $data = [
            'foo' => 'bar',
        ];

        $this->assertFalse($this->validation->run($data, 'groupA'));
        $this->assertSame('Shame, shame. Too short.', $this->validation->getError('foo'));
    }

    //--------------------------------------------------------------------

    public function testGroupsReadFromConfigValid()
    {
        $data = [
            'foo' => 'barsteps',
        ];

        $this->assertTrue($this->validation->run($data, 'groupA'));
    }

    //--------------------------------------------------------------------

    public function testGetRuleGroup()
    {
        $this->assertSame([
            'foo' => 'required|min_length[5]',
        ], $this->validation->getRuleGroup('groupA'));
    }

    //--------------------------------------------------------------------

    public function testGetRuleGroupException()
    {
        $this->expectException(ValidationException::class);
        $this->validation->getRuleGroup('groupZ');
    }

    //--------------------------------------------------------------------

    public function testSetRuleGroup()
    {
        $this->validation->setRuleGroup('groupA');

        $this->assertSame([
            'foo' => 'required|min_length[5]',
        ], $this->validation->getRules());
    }

    //--------------------------------------------------------------------

    public function testSetRuleGroupException()
    {
        $this->expectException(ValidationException::class);

        $this->validation->setRuleGroup('groupZ');
    }

    //--------------------------------------------------------------------

    public function testSetRuleGroupWithCustomErrorMessage()
    {
        $this->validation->reset();
        $this->validation->setRuleGroup('login');
        $this->validation->run([
            'username' => 'codeigniter',
        ]);

        $this->assertSame([
            'password' => 'custom password required error msg.',
        ], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function testRunGroupWithCustomErrorMessage()
    {
        $this->validation->reset();
        $this->validation->run([
            'username' => 'codeigniter',
        ], 'login');

        $this->assertSame([
            'password' => 'custom password required error msg.',
        ], $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider rulesSetupProvider
     */
    public function testRulesSetup($rules, $expected, $errors = [])
    {
        $data = [
            'foo' => '',
        ];

        $this->validation->setRules([
            'foo' => $rules,
        ], $errors);

        $this->validation->run($data);

        $this->assertSame($expected, $this->validation->getError('foo'));
    }

    //--------------------------------------------------------------------

    public function rulesSetupProvider()
    {
        return [
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

    //--------------------------------------------------------------------

    public function testSetRulesRemovesErrorsArray()
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

    public function testInvalidRule()
    {
        $this->expectException(ValidationException::class);

        $rules = [
            'foo' => 'bar|baz',
            'bar' => 'baz|belch',
        ];
        $this->validation->setRules($rules);

        $data = [
            'foo' => '',
        ];
        $this->validation->run($data);
    }

    //--------------------------------------------------------------------

    public function testRawInput()
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

    //--------------------------------------------------------------------

    public function testJsonInput()
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

    //--------------------------------------------------------------------

    public function testHasRule()
    {
        $this->validation->setRuleGroup('groupA');

        $this->assertTrue($this->validation->hasRule('foo'));
    }

    //--------------------------------------------------------------------

    public function testNotARealGroup()
    {
        $this->expectException(ValidationException::class);
        $this->validation->setRuleGroup('groupX');
        $this->validation->getRuleGroup('groupX');
    }

    //--------------------------------------------------------------------

    public function testBadTemplate()
    {
        $this->expectException(ValidationException::class);
        $this->validation->listErrors('obviouslyBadTemplate');
    }

    //--------------------------------------------------------------------

    public function testShowNonError()
    {
        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->validation->setError('foo', 'Nadda');

        $this->assertSame('', $this->validation->showError('bogus'));
    }

    //--------------------------------------------------------------------

    public function testShowBadTemplate()
    {
        $this->expectException(ValidationException::class);

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);
        $this->validation->setError('foo', 'Nadda');

        $this->assertSame('We should never get here', $this->validation->showError('foo', 'bogus_template'));
    }

    //--------------------------------------------------------------------

    public function testNoRuleSetsSetup()
    {
        $this->expectException(ValidationException::class);

        $this->config['ruleSets'] = null;
        $this->validation         = new Validation((object) $this->config, Services::renderer());
        $this->validation->reset();

        $data = [
            'foo' => '',
        ];

        $this->validation->run($data);
    }

    //--------------------------------------------------------------------

    public function testNotCustomRuleGroup()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'foo' => '',
        ];

        $this->validation->run($data, 'GeorgeRules');
    }

    //--------------------------------------------------------------------

    public function testNotRealCustomRule()
    {
        $this->expectException(ValidationException::class);

        $data = [
            'foo' => '',
        ];

        $this->validation->run($data, 'groupX');
    }

    //--------------------------------------------------------------------

    public function testHasError()
    {
        $data = [
            'foo' => 'notanumber',
        ];

        $this->validation->setRules([
            'foo' => 'is_numeric',
        ]);

        $this->validation->run($data);

        $this->assertTrue($this->validation->hasError('foo'));
    }

    //--------------------------------------------------------------------

    public function testSplitRulesTrue()
    {
        $data = [
            'phone' => '0987654321',
        ];

        $this->validation->setRules([
            'phone' => 'required|regex_match[/^(01[2689]|09)[0-9]{8}$/]|numeric',
        ]);

        $result = $this->validation->run($data);

        $this->assertTrue($result);
    }

    public function testSplitRulesFalse()
    {
        $data = [
            'phone' => '09876543214',
        ];

        $this->validation->setRules([
            'phone' => 'required|regex_match[/^(01[2689]|09)[0-9]{8}$/]|numeric',
        ]);

        $result = $this->validation->run($data);

        $this->assertFalse($result);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1201
     */
    public function testSplitNotRegex()
    {
        $method = $this->getPrivateMethodInvoker($this->validation, 'splitRules');

        $result = $method('uploaded[avatar]|max_size[avatar,1024]');

        $this->assertSame('uploaded[avatar]', $result[0]);
    }

    public function testSplitRegex()
    {
        $method = $this->getPrivateMethodInvoker($this->validation, 'splitRules');

        $result = $method('required|regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]|max_length[10]');

        $this->assertSame('regex_match[/^[0-9]{4}[\-\.\[\/][0-9]{2}[\-\.\[\/][0-9]{2}/]', $result[1]);
    }

    //--------------------------------------------------------------------

    public function testTagReplacement()
    {
        // data
        $data = [
            'Username' => 'Pizza',
        ];

        // rules
        $this->validation->setRules([
            'Username' => 'min_length[6]',
        ], [
            'Username' => [
                'min_length' => 'Supplied value ({value}) for {field} must have at least {param} characters.',
            ],
        ]);

        // run validation
        $this->validation->run($data);

        // $errors should contain an associative array
        $errors = $this->validation->getErrors();

        // if "Username" doesn't exist in errors
        if (! isset($errors['Username'])) {
            $this->fail('Unable to find "Username"');
        }

        // expected error message
        $expected = 'Supplied value (Pizza) for Username must have at least 6 characters.';

        // check if they are the same!
        $this->assertSame($expected, $errors['Username']);
    }

    //--------------------------------------------------------------------

    /**
     * @dataProvider arrayFieldDataProvider
     */
    public function testRulesForArrayField($body, $rules, $results)
    {
        $config          = new App();
        $config->baseURL = 'http://example.com/';

        $request = new IncomingRequest($config, new URI(), http_build_query($body), new UserAgent());

        $this->validation->setRules($rules);
        $this->validation->withRequest($request->withMethod('post'))->run($body);
        $this->assertSame($results, $this->validation->getErrors());
    }

    //--------------------------------------------------------------------

    public function arrayFieldDataProvider()
    {
        return [
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

    //--------------------------------------------------------------------

    public function testRulesForSingleRuleWithAsteriskWillReturnNoError()
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

    //--------------------------------------------------------------------

    public function testRulesForSingleRuleWithAsteriskWillReturnError()
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

    //--------------------------------------------------------------------

    public function testRulesForSingleRuleWithSingleValue()
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

    //--------------------------------------------------------------------

    public function testTranslatedLabel()
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

    //--------------------------------------------------------------------

    public function testTranslatedLabelIsMissing()
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

    //--------------------------------------------------------------------

    public function testTranslatedLabelWithCustomErrorMessage()
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

    //--------------------------------------------------------------------

    public function testTranslatedLabelTagReplacement()
    {
        // data
        $data = [
            'Username' => 'Pizza',
        ];

        // rules
        $this->validation->setRules([
            'Username' => [
                'label' => 'Foo.bar',
                'rules' => 'min_length[6]',
            ],
        ], [
            'Username' => [
                'min_length' => 'Foo.bar.min_length2',
            ],
        ]);

        // run validation
        $this->validation->run($data);

        // $errors should contain an associative array
        $errors = $this->validation->getErrors();

        // if "Username" doesn't exist in errors
        if (! isset($errors['Username'])) {
            $this->fail('Unable to find "Username"');
        }

        // expected error message
        $expected = 'Supplied value (Pizza) for Foo Bar Translated must have at least 6 characters.';

        // check if they are the same!
        $this->assertSame($expected, $errors['Username']);
    }

    /**
     * @dataProvider dotNotationForIfExistProvider
     *
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4521
     *
     * @param bool  $expected
     * @param array $rules
     * @param array $data
     *
     * @return void
     */
    public function testDotNotationOnIfExistRule(bool $expected, array $rules, array $data): void
    {
        $actual = $this->validation->setRules($rules)->run($data);
        $this->assertSame($expected, $actual);
    }

    public function dotNotationForIfExistProvider()
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
     *
     * @param bool  $expected
     * @param array $rules
     * @param array $data
     *
     * @return void
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
}
