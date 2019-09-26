<?php namespace Builder;

use Tests\Support\Database\MockConnection;

class GetTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
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
		$builder->testMode()->where('username', 'bogus');

		$expectedSQL           = 'SELECT * FROM "users" WHERE "username" = \'bogus\'';
		$expectedSQLafterreset = 'SELECT * FROM "users"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, false)));
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->get(0, 50, true)));
		$this->assertEquals($expectedSQLafterreset, str_replace("\n", ' ', $builder->get(0, 50, true)));
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/2143
	 */
	public function testGetWhereWithLimit()
	{
		$builder = $this->db->table('users');
		$builder->testMode();

		$expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'  LIMIT 5';
		$expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'  LIMIT 5';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, null, false)));
		$this->assertEquals($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 0, true)));
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, null, true)));
	}

	//--------------------------------------------------------------------

	public function testGetWhereWithLimitAndOffset()
	{
		$builder = $this->db->table('users');
		$builder->testMode();

		$expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'  LIMIT 10, 5';
		$expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'  LIMIT 10, 5';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, false)));
		$this->assertEquals($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, true)));
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], 5, 10, true)));
	}

	//--------------------------------------------------------------------

	public function testGetWhereWithWhereConditionOnly()
	{
		$builder = $this->db->table('users');
		$builder->testMode();

		$expectedSQL             = 'SELECT * FROM "users" WHERE "username" = \'bogus\'';
		$expectedSQLWithoutReset = 'SELECT * FROM "users" WHERE "username" = \'bogus\' AND "username" = \'bogus\'';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, false)));
		$this->assertEquals($expectedSQLWithoutReset, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, true)));
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(['username' => 'bogus'], null, null, true)));
	}

	//--------------------------------------------------------------------

	public function testGetWhereWithoutArgs()
	{
		$builder = $this->db->table('users');
		$builder->testMode();

		$expectedSQL = 'SELECT * FROM "users"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getWhere(null, null, null, true)));
	}

}
