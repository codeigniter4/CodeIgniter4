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

	/**
	 * @group single
	 */
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

}