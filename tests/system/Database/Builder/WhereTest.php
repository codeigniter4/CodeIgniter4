<?php namespace Builder;

use Tests\Support\Database\MockConnection;

class WhereTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleWhere()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE "id" = :id:';
		$expectedBinds = ['id' => 3];

		$builder->where('id', 3);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereNoEscape()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE id = :id:';
		$expectedBinds = ['id' => 3];

		$builder->where('id', 3, false);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereCustomKeyOperator()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE "id" != :id:';
		$expectedBinds = ['id' => 3];

		$builder->where('id !=', 3);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereAssociateArray()
	{
		$builder = $this->db->table('jobs');

		$where = [
			'id'      => 2,
			'name !=' => 'Accountant',
		];

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = :id: AND "name" != :name:';
		$expectedBinds = [
			'id'   => 2,
			'name' => 'Accountant',
		];

		$builder->where($where);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereCustomString()
	{
		$builder = $this->db->table('jobs');

		$where = "id > 2 AND name != 'Accountant'";

		$expectedSQL   = "SELECT * FROM \"jobs\" WHERE \"id\" > 2 AND \"name\" != 'Accountant'";
		$expectedBinds = [];

		$builder->where($where);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhere()
	{
		$builder = $this->db->table('jobs');

		$builder->where('name !=', 'Accountant')
				->orWhere('id >', 3);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" != :name: OR "id" > :id:';
		$expectedBinds = [
			'name' => 'Accountant',
			'id'   => 3,
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhereSameColumn()
	{
		$builder = $this->db->table('jobs');

		$builder->where('name', 'Accountant')
				->orWhere('name', 'foobar');

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" = :name: OR "name" = :name0:';
		$expectedBinds = [
			'name'  => 'Accountant',
			'name0' => 'foobar',
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereIn()
	{
		$builder = $this->db->table('jobs');

		$builder->whereIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" IN :name:';
		$expectedBinds = [
			'name' => [
				'Politician',
				'Accountant',
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereNotIn()
	{
		$builder = $this->db->table('jobs');

		$builder->whereNotIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" NOT IN :name:';
		$expectedBinds = [
			'name' => [
				'Politician',
				'Accountant',
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhereIn()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 2)->orWhereIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = :id: OR "name" IN :name:';
		$expectedBinds = [
			'id'   => 2,
			'name' => [
				'Politician',
				'Accountant',
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhereNotIn()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 2)->orWhereNotIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = :id: OR "name" NOT IN :name:';
		$expectedBinds = [
			'id'   => 2,
			'name' => [
				'Politician',
				'Accountant',
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------
}
