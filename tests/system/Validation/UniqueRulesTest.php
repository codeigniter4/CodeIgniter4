<?php namespace CodeIgniter\Validation;

use Config\Database;
use CodeIgniter\Test\CIDatabaseTestCase;

class UniqueRulesTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	/**
	 * @var Validation
	 */
	protected $validation;

	protected $config = [
		'ruleSets'      => [
			\CodeIgniter\Validation\Rules::class,
			\CodeIgniter\Validation\FormatRules::class,
			\CodeIgniter\Validation\FileRules::class,
			\CodeIgniter\Validation\CreditCardRules::class,
			\Tests\Support\Validation\TestRules::class,
		],
		'groupA'        => [
			'foo' => 'required|min_length[5]',
		],
		'groupA_errors' => [
			'foo' => [
				'min_length' => 'Shame, shame. Too short.',
			],
		],
	];

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();
		$this->validation = new Validation((object)$this->config, \Config\Services::renderer());
		$this->validation->reset();

		$_FILES = [];
	}

	/**
	 * @group DatabaseLive
	 */
	public function testIsUniqueFalse()
	{
		$this->hasInDatabase('user', [
			'name'    => 'Derek',
			'email'   => 'derek@world.com',
			'country' => 'USA',
		]);

		$data = [
			'email' => 'derek@world.com',
		];

		$this->validation->setRules([
			'email' => 'is_unique[user.email]',
		]);

		$this->assertFalse($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @group DatabaseLive
	 */
	public function testIsUniqueTrue()
	{
		$data = [
			'email' => 'derek@world.co.uk',
		];

		$this->validation->setRules([
			'email' => 'is_unique[user.email]',
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------

	/**
	 * @group DatabaseLive
	 */
	public function testIsUniqueIgnoresParams()
	{
		$this->hasInDatabase('user', [
			'name'    => 'Derek',
			'email'   => 'derek@world.co.uk',
			'country' => 'GB',
		]);

		$db  = Database::connect();
		$row = $db->table('user')
				  ->limit(1)
				  ->get()
				  ->getRow();

		$data = [
			'email' => 'derek@world.co.uk',
		];

		$this->validation->setRules([
			'email' => "is_unique[user.email,id,{$row->id}]",
		]);

		$this->assertTrue($this->validation->run($data));
	}

	//--------------------------------------------------------------------
}
