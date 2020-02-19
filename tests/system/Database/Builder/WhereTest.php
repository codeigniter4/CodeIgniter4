<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\Mock\MockConnection;

class WhereTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleWhere()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE "id" = 3';
		$expectedBinds = [
			'id' => [
				3,
				true,
			],
		];

		$builder->where('id', 3);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertEquals($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereNoEscape()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE id = 3';
		$expectedBinds = [
			'id' => [
				3,
				false,
			],
		];

		$builder->where('id', 3, false);
		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereCustomKeyOperator()
	{
		$builder = $this->db->table('users');

		$expectedSQL   = 'SELECT * FROM "users" WHERE "id" != 3';
		$expectedBinds = [
			'id' => [
				3,
				true,
			],
		];

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

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 AND "name" != \'Accountant\'';
		$expectedBinds = [
			'id'   => [
				2,
				true,
			],
			'name' => [
				'Accountant',
				true,
			],
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

	public function testWhereValueClosure()
	{
		$builder = $this->db->table('neworder');

		$builder->where('advance_amount <', function (BaseBuilder $builder) {
			return $builder->select('MAX(advance_amount)', false)->from('orders')->where('id >', 2);
		});
		$expectedSQL = 'SELECT * FROM "neworder" WHERE "advance_amount" < (SELECT MAX(advance_amount) FROM "orders" WHERE "id" > 2)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrWhere()
	{
		$builder = $this->db->table('jobs');

		$builder->where('name !=', 'Accountant')
				->orWhere('id >', 3);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" != \'Accountant\' OR "id" > 3';
		$expectedBinds = [
			'name' => [
				'Accountant',
				true,
			],
			'id'   => [
				3,
				true,
			],
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

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" = \'Accountant\' OR "name" = \'foobar\'';
		$expectedBinds = [
			'name'  => [
				'Accountant',
				true,
			],
			'name0' => [
				'foobar',
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereIn()
	{
		$builder = $this->db->table('jobs');

		$builder->whereIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" IN (\'Politician\',\'Accountant\')';
		$expectedBinds = [
			'name' => [
				[
					'Politician',
					'Accountant',
				],
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereInClosure()
	{
		$builder = $this->db->table('jobs');

		$builder->whereIn('id', function (BaseBuilder $builder) {
			return $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
		});

		$expectedSQL = 'SELECT * FROM "jobs" WHERE "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testWhereNotIn()
	{
		$builder = $this->db->table('jobs');

		$builder->whereNotIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "name" NOT IN (\'Politician\',\'Accountant\')';
		$expectedBinds = [
			'name' => [
				[
					'Politician',
					'Accountant',
				],
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testWhereNotInClosure()
	{
		$builder = $this->db->table('jobs');

		$builder->whereNotIn('id', function (BaseBuilder $builder) {
			return $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
		});

		$expectedSQL = 'SELECT * FROM "jobs" WHERE "id" NOT IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrWhereIn()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 2)->orWhereIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 OR "name" IN (\'Politician\',\'Accountant\')';
		$expectedBinds = [
			'id'   => [
				2,
				true,
			],
			'name' => [
				[
					'Politician',
					'Accountant',
				],
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhereInClosure()
	{
		$builder = $this->db->table('jobs');

		$builder->where('deleted_at', null)->orWhereIn('id', function (BaseBuilder $builder) {
			return $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
		});

		$expectedSQL = 'SELECT * FROM "jobs" WHERE "deleted_at" IS NULL OR "id" IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testOrWhereNotIn()
	{
		$builder = $this->db->table('jobs');

		$builder->where('id', 2)->orWhereNotIn('name', ['Politician', 'Accountant']);

		$expectedSQL   = 'SELECT * FROM "jobs" WHERE "id" = 2 OR "name" NOT IN (\'Politician\',\'Accountant\')';
		$expectedBinds = [
			'id'   => [
				2,
				true,
			],
			'name' => [
				[
					'Politician',
					'Accountant',
				],
				true,
			],
		];

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
		$this->assertSame($expectedBinds, $builder->getBinds());
	}

	//--------------------------------------------------------------------

	public function testOrWhereNotInClosure()
	{
		$builder = $this->db->table('jobs');

		$builder->where('deleted_at', null)->orWhereNotIn('id', function (BaseBuilder $builder) {
			return $builder->select('job_id')->from('users_jobs')->where('user_id', 3);
		});

		$expectedSQL = 'SELECT * FROM "jobs" WHERE "deleted_at" IS NULL OR "id" NOT IN (SELECT "job_id" FROM "users_jobs" WHERE "user_id" = 3)';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
