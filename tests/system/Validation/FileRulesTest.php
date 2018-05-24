<?php namespace CodeIgniter\Validation;

use Config\Database;

class FileRulesTest extends \CIUnitTestCase
{
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
			\CodeIgniter\Validation\TestRules::class,
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

	public function setUp()
	{
		parent::setUp();
		$this->validation = new Validation((object)$this->config, \Config\Services::renderer());
		$this->validation->reset();

		$_FILES = [];
	}

	//--------------------------------------------------------------------

	public function testUploadedTrue()
	{
		$_FILES = [
			'avatar' => [
				'tmp_name' => 'phpUxcOty',
				'name' => 'my-avatar.png',
				'size' => 90996,
				'type' => 'image/png',
				'error' => 0,
			]
		];

		$this->validation->setRules([
			'avatar' => "uploaded[avatar]",
		]);

		$this->assertTrue($this->validation->run([]));

	}

	//--------------------------------------------------------------------

	public function testUploadedFalse()
	{
		$_FILES = [
			'avatar' => [
				'tmp_name' => 'phpUxcOty',
				'name' => 'my-avatar.png',
				'size' => 90996,
				'type' => 'image/png',
				'error' => 0,
			]
		];

		$this->validation->setRules([
			'avatar' => "uploaded[userfile]",
		]);

		$this->assertFalse($this->validation->run([]));

	}

	//--------------------------------------------------------------------

}
