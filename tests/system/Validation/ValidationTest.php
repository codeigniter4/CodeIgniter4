<?php namespace CodeIgniter\Validation;

use Config\Database;

class ValidationTest extends \CIUnitTestCase
{
	/**
	 * @var Validation
	 */
	protected $validation;

	protected $config = [
		'ruleSets' => [
			\CodeIgniter\Validation\Rules::class,
			\CodeIgniter\Validation\CreditCardRules::class,
		],
        'groupA' => [
            'foo' => 'required|min_length[5]'
        ],
        'groupA_errors' => [
            'foo' => [
                'min_length' => 'Shame, shame. Too short.'
            ]
        ]
	];

	//--------------------------------------------------------------------

	public function setUp()
	{
	    parent::setUp();
		$this->validation = new Validation((object)$this->config);
		$this->validation->reset();
	}

	//--------------------------------------------------------------------

	public function testSetRulesStoresRules()
	{
		$rules = [
			'foo' => 'bar|baz',
			'bar' => 'baz|belch'
		];

		$this->validation->setRules($rules);

		$this->assertEquals($rules, $this->validation->getRules());
	}

	//--------------------------------------------------------------------

	public function testRunReturnsTrueWithNOthingToDo()
	{
	    $this->validation->setRules([]);

		$this->assertTrue($this->validation->run([]));
	}

	//--------------------------------------------------------------------

	public function testRunDoesTheBasics()
	{
		$data = [
			'foo' => 'notanumber'
		];

	    $this->validation->setRules([
			'foo' => 'is_numeric'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRunReturnsLocalizedErrors()
	{
		$data = [
			'foo' => 'notanumber'
		];

		$this->validation->setRules([
			'foo' => 'is_numeric'
		]);

		$this->assertFalse($this->validation->run($data));
		$this->assertEquals('is_numeric', $this->validation->getError('foo'));
	}

	//--------------------------------------------------------------------

	public function testRunWithCustomErrors()
	{
	    $data = [
	    	'foo' => 'notanumber'
		];

		$messages = [
			'foo' => [
				'is_numeric' => 'Nope. Not a number.'
			]
		];

		$this->validation->setRules([
			'foo' => 'is_numeric'
		], $messages);

		$this->validation->run($data);
		$this->assertEquals('Nope. Not a number.', $this->validation->getError('foo'));
	}

	//--------------------------------------------------------------------

	public function testGetErrors()
	{
		$data = [
			'foo' => 'notanumber'
		];

		$this->validation->setRules([
			'foo' => 'is_numeric'
		]);

		$this->validation->run($data);

		$this->assertEquals(['foo' => 'is_numeric'], $this->validation->getErrors());
	}

	//--------------------------------------------------------------------

	public function testGetErrorsWhenNone()
	{
		$data = [
			'foo' => 123
		];

		$this->validation->setRules([
			'foo' => 'is_numeric'
		]);

		$this->validation->run($data);

		$this->assertEquals([], $this->validation->getErrors());
	}

	//--------------------------------------------------------------------

	public function testSetErrors()
	{
		$this->validation->setRules([
			'foo' => 'is_numeric'
		]);

		$this->validation->setError('foo', 'Nadda');

		$this->assertEquals(['foo' => 'Nadda'], $this->validation->getErrors());
	}

	//--------------------------------------------------------------------

    public function testGroupsReadFromConfig()
    {
        $data = [
            'foo' => 'bar'
        ];

        $this->assertFalse($this->validation->run($data, 'groupA'));
        $this->assertEquals('Shame, shame. Too short.', $this->validation->getError('foo'));
    }

    //--------------------------------------------------------------------

    /**
     * @group single
     */
    public function testGroupsReadFromConfigValid()
    {
        $data = [
            'foo' => 'barsteps'
        ];

        $this->assertTrue($this->validation->run($data, 'groupA'));
    }

    //--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Rules Tests
	//--------------------------------------------------------------------

	public function testRequiredTrueString()
	{
		$data = [
			'foo' => 123
		];

		$this->validation->setRules([
			'foo' => 'required'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRequiredFalseString()
	{
		$data = [
			'bar' => 123
		];

		$this->validation->setRules([
			'foo' => 'required'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRequiredTrueArray()
	{
		$data = [
			'foo' => [123]
		];

		$this->validation->setRules([
			'foo' => 'required'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRequiredFalseArray()
	{
		$data = [
			'foo' => []
		];

		$this->validation->setRules([
			'foo' => 'required'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRegexMatch()
	{
	    $data = [
	    	'foo' => 'abcde'
		];

		$this->validation->setRules([
			'foo' => 'regex_match[/[a-z]/]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testRegexMatchFalse()
	{
		$data = [
			'foo' => 'abcde'
		];

		$this->validation->setRules([
			'foo' => 'regex_match[\d]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMatchesTrue()
	{
		$data = [
			'foo' => 'match',
			'bar' => 'match'
		];

		$this->validation->setRules([
			'foo' => 'matches[bar]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMatchesFalse()
	{
		$data = [
			'foo' => 'match',
			'bar' => 'nope'
		];

		$this->validation->setRules([
			'foo' => 'matches[bar]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testDiffersTrue()
	{
		$data = [
			'foo' => 'match',
			'bar' => 'nope'
		];

		$this->validation->setRules([
			'foo' => 'differs[bar]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testDiffersFalse()
	{
		$data = [
			'foo' => 'match',
			'bar' => 'match'
		];

		$this->validation->setRules([
			'foo' => 'differs[bar]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testIsUniqueFalse()
	{
		$data = [
			'email' => 'derek@world.com',
		];

		$this->validation->setRules([
			'email' => 'is_unique[user.email]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testIsUniqueTrue()
	{
		$data = [
			'email' => 'derek@world.co.uk',
		];

		$this->validation->setRules([
			'email' => 'is_unique[user.email]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testIsUniqueIgnoresParams()
	{
		$db = Database::connect();
		$row = $db->table('user')->limit(1)->get()->getRow();

		$data = [
			'email' => 'derek@world.co.uk',
		];

		$this->validation->setRules([
			'email' => "is_unique[user.email,id,{$row->id}]"
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMinLengthReturnsFalseWithNonNumericVal()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'min_length[bar]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMinLengthReturnsTrueWithSuccess()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'min_length[2]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMinLengthReturnsTrueWithExactLength()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'min_length[3]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMinLengthReturnsFalseWhenWrong()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'min_length[4]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMaxLengthReturnsFalseWithNonNumericVal()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'max_length[bar]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMaxLengthReturnsTrueWithSuccess()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'max_length[4]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMaxLengthReturnsTrueWithExactLength()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'max_length[3]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testMaxLengthReturnsFalseWhenWrong()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'max_length[2]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testExactLengthReturnsTrueOnSuccess()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'exact_length[3]'
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testExactLengthReturnsFalseWhenShort()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'exact_length[2]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function testExactLengthReturnsFalseWhenLong()
	{
		$data = [
			'foo' => 'bar',
		];

		$this->validation->setRules([
			'foo' => 'exact_length[4]'
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider urlProvider
	 */
	public function testValidURL(string $url, bool $expected)
	{
		$data = [
			'foo' => $url,
		];

		$this->validation->setRules([
			'foo' => 'valid_url'
		]);

	    $this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function urlProvider()
	{
	    return [
	    	['www.codeigniter.com', true],
			['http://codeigniter.com', true],
			//https://bugs.php.net/bug.php?id=51192
			['http://accept-dashes.tld', true],
			['http://reject_underscores', false],
			// https://github.com/bcit-ci/CodeIgniter/issues/4415
			['http://[::1]/ipv6', true],
			['htt://www.codeigniter.com', false],
			['', false],
			['code igniter', false]
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider emailProviderSingle
	 *
	 * @param $email
	 * @param $expected
	 */
	public function testValidEmail($email, $expected)
	{
		$data = [
			'foo' => $email,
		];

		$this->validation->setRules([
			'foo' => 'valid_email'
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider emailProviderSingle
	 *
	 * @param $email
	 * @param $expected
	 */
	public function testValidEmails($email, $expected)
	{
		$data = [
			'foo' => $email,
		];

		$this->validation->setRules([
			'foo' => 'valid_emails'
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function emailProviderSingle()
	{
	    return [
	    	['email@sample.com', true],
			['valid_email', false]
		];
	}

	//--------------------------------------------------------------------

	public function emailsProvider()
	{
	    return [
			['1@sample.com,2@sample.com', true],
			['1@sample.com, 2@sample.com', true],
			['email@sample.com', true],
			['@sample.com,2@sample.com,validemail@email.ca', false]
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider ipProvider
	 *
	 * @param $ip
	 * @param $which
	 * @param $expected
	 */
	public function testValidIP($ip, $which, $expected)
	{
		$data = [
			'foo' => $ip,
		];

		$this->validation->setRules([
			'foo' => "valid_ip[{$which}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function ipProvider()
	{
	    return [
	    	['127.0.0.1', null, true],
			['127.0.0.1', 'ipv4', true],
			['2001:0db8:85a3:0000:0000:8a2e:0370:7334', null, true],
			['2001:0db8:85a3:0000:0000:8a2e:0370:7334', 'ipv6', true],
			['2001:0db8:85a3:0000:0000:8a2e:0370:7334', 'ipv4', false],
			['127.0.0.1', 'ipv6', false],
			['H001:0db8:85a3:0000:0000:8a2e:0370:7334', null, false],
			['127.0.0.259', null, false]
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider alphaProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testAlpha($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "alpha"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function alphaProvider()
	{
	    return [
	    	['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ', true],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ ', false],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ1', false],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ*', false]
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider alphaNumericProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testAlphaNumeric($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "alpha_numeric"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function alphaNumericProvider()
	{
		return [
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789', true],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789\ ', false],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789_', false],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider alphaNumericProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testAlphaNumericSpace($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "alpha_numeric_spaces"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function alphaNumericSpaceProvider()
	{
		return [
			[' abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789', true],
			[' abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789-', false],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider alphaDashProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testAlphaDash($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "alpha_dash"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function alphaDashProvider()
	{
		return [
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789-', true],
			['abcdefghijklmnopqrstuvwxyzABCDEFGHLIJKLMNOPQRSTUVWXYZ0123456789-\ ', false],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider numericProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testNumeric($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "numeric"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function numericProvider()
	{
		return [
			['0', true],
			['12314', true],
			['-42', true],
			['+42', true],
			['123a', false],
			['--1', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider integerProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testInteger($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "integer"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function integerProvider()
	{
		return [
			['0', true],
			['42', true],
			['-1', true],
			['123a', false],
			['1.9', false],
			['--1', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider decimalProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testDecimal($str, $expected)
	{
		$data = [
			'foo' => $str,
		];

		$this->validation->setRules([
			'foo' => "decimal"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function decimalProvider()
	{
		return [
			['1.0', true],
			['-0.98', true],
			['0', false],
			['1.0a', false],
			['-i', false],
			['--1', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider greaterThanProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testGreaterThan($first, $second, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "greater_than[{$second}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function greaterThanProvider()
	{
		return [
			['-10', '-11', true],
			['10', '9', true],
			['10', '10', false],
			['10', 'a', false],
			['10a', '10', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider greaterThanEqualProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testGreaterThanEqual($first, $second, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "greater_than_equal_to[{$second}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function greaterThanEqualProvider()
	{
		return [
			['0', '0', true],
			['1', '0', true],
			['-1', '0', false],
			['10a', '0', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider lessThanProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testLessThan($first, $second, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "less_than[{$second}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function lessThanProvider()
	{
		return [
			['4', '5', true],
			['-1', '0', true],
			['4', '4', false],
			['10a', '5', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider lessThanEqualProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testLessEqualThan($first, $second, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "less_than_equal_to[{$second}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function lessThanEqualProvider()
	{
		return [
			['-1', '0', true],
			['-1', '-1', true],
			['4', '4', true],
			['0', '-1', false],
			['10a', '0', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider inListProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testInList($first, $second, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "in_list[{$second}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function inListProvider()
	{
		return [
			['red', 'red,Blue,123', true],
			['Blue', 'red,Blue,123', true],
			['123', 'red,Blue,123', true],
			['Red', 'red,Blue,123', false],
			[' red', 'red,Blue,123', false],
			['1234', 'red,Blue,123', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider naturalProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testNatural($first, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "is_natural"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function naturalProvider()
	{
		return [
			['0', true],
			['12', true],
			['42a', false],
			['-1', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider naturalZeroProvider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testNaturalNoZero($first, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "is_natural_no_zero"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function naturalZeroProvider()
	{
		return [
			['0', false],
			['12', true],
			['42a', false],
			['-1', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider base64Provider
	 *
	 * @param $str
	 * @param $expected
	 */
	public function testBase64($first, $expected)
	{
		$data = [
			'foo' => $first,
		];

		$this->validation->setRules([
			'foo' => "valid_base64"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function base64Provider()
	{
		return [
			[base64_encode('string'), true],
			['FA08GG', false],
		];
	}

	//-------------------------------------------------------------------

	/**
	 * @dataProvider timezoneProvider
	 *
	 * @param $value
	 * @param $expected
	 */
	public function testTimeZone($value, $expected)
	{
		$data = [
			'foo' => $value,
		];

		$this->validation->setRules([
			'foo' => "timezone"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function timezoneProvider()
	{
	    return [
	    	['America/Chicago', true],
	    	['america/chicago', false],
			['foo/bar', false],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider requiredWithProvider
	 *
	 * @param $check
	 * @param $expected
	 */
	public function testRequiredWith($field, $check, $expected=false)
	{
		$data = [
			'foo' => 'bar',
			'bar' => 'something',
			'baz' => null,
		];

		$this->validation->setRules([
			$field => "required_with[{$check}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function requiredWithProvider()
	{
	    return [
			['nope', 'bar', false],
			['foo', 'bar', true],
			['nope', 'baz', true],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider requiredWithoutProvider
	 *
	 * @param $check
	 * @param $expected
	 */
	public function testRequiredWithout($field, $check, $expected=false)
	{
		$data = [
			'foo' => 'bar',
			'bar' => 'something',
			'baz' => null,
		];

		$this->validation->setRules([
			$field => "required_without[{$check}]"
		]);

		$this->assertEquals($expected, $this->validation->run($data));
	}

	//--------------------------------------------------------------------

	public function requiredWithoutProvider()
	{
		return [
			['nope', 'bars', false],
			['foo', 'nope', true]
		];
	}

	//--------------------------------------------------------------------

    //--------------------------------------------------------------------
    // Credit Card Rules
    //--------------------------------------------------------------------

    /**
     * @dataProvider creditCardProvider
     * @group single
     *
     * @param      $type
     * @param      $number
     * @param bool $expected
     */
    public function testValidCCNumber($type, $number, $expected=false)
    {
        $data = [
            'cc' => $number,
        ];

        $this->validation->setRules([
            'cc' => "valid_cc_number[{$type}]"
        ]);

        $this->assertEquals($expected, $this->validation->run($data));
    }

    //--------------------------------------------------------------------

    /**
     * Cards shown are test cards found around the web.
     *
     * @see https://www.paypalobjects.com/en_US/vhelp/paypalmanager_help/credit_card_numbers.htm
     *
     * @return array
     */
    public function creditCardProvider()
    {
        return [
            'invalid_type'      => ['shorty', '1111 1111 1111 1111', false],
            'invalid_length'    => ['amex', '', false],
            'not_numeric'       => ['amex', 'abcd efgh ijkl mnop', false],
            'bad_length'        => ['amex', '3782 8224 6310 0051', false],
            'bad_prefix'        => ['amex', '3582 8224 6310 0051', false],
            'amex1'             => ['amex', '3782 8224 6310 005', true],
            'amex2'             => ['amex', '3714 4963 5398 431', true],
            'dinersclub1'       => ['dinersclub', '3056 9309 0259 04', true],
            'dinersculb2'       => ['dinersclub', '3852 0000 0232 37', true],
            'discover1'         => ['discover', '6011 1111 1111 1117', true],
            'discover2'         => ['discover', '6011 0009 9013 9424', true],
            'jcb1'              => ['jcb', '3530 1113 3330 0000', true],
            'jcb2'              => ['jcb', '3566 0020 2036 0505', true],
            'mastercard1'       => ['mastercard', '5555 5555 5555 4444', true],
            'mastercard2'       => ['mastercard', '5105 1051 0510 5100', true],
            'visa1'             => ['visa', '4111 1111 1111 1111', true],
            'visa2'             => ['visa', '4012 8888 8888 1881', true],
            'visa3'             => ['visa', '4222 2222 2222 2', true],
            'dankort1'          => ['dankort', '5019 7170 1010 3742', true],
        ];
    }

    //--------------------------------------------------------------------

}