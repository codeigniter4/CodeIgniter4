<?php namespace Builder;

use Tests\Support\Database\MockConnection;

class GetTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testGet()
	{
		$builder = $this->db->table('users');

		$expectedSQL = 'SELECT * FROM "users"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2141
	 */
	public function testGetWithReset()
	{
		$builder = $this->db->table('users');
		$builder->where('username', 'bogus');

		$expectedSQL           = 'SELECT * FROM "users" WHERE "username" = \'bogus\'';
		$expectedSQLafterreset = 'SELECT * FROM "users"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, true, false)));
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, true, true)));
		$this->assertEquals($expectedSQLafterreset, str_replace("\n", ' ', $builder->get(0, 50, true, true)));
	}

}
