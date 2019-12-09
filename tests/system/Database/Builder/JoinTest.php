<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class JoinTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testJoinSimple()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->join('job', 'user.id = job.id');

		$expectedSQL = 'SELECT * FROM "user" JOIN "job" ON "user"."id" = "job"."id"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testJoinIsNull()
	{
		$builder = new BaseBuilder('table1', $this->db);

		$builder->join('table2', 'field IS NULL');

		$expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NULL';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testJoinIsNotNull()
	{
		$builder = new BaseBuilder('table1', $this->db);

		$builder->join('table2', 'field IS NOT NULL');

		$expectedSQL = 'SELECT * FROM "table1" JOIN "table2" ON "field" IS NOT NULL';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testJoinMultipleConditions()
	{
		$builder = new BaseBuilder('table1', $this->db);

		$builder->join('table2', "table1.field1 = table2.field2 AND table1.field1 = 'foo' AND table2.field2 = 0", 'LEFT');

		$expectedSQL = "SELECT * FROM \"table1\" LEFT JOIN \"table2\" ON \"table1\".\"field1\" = \"table2\".\"field2\" AND \"table1\".\"field1\" = 'foo' AND \"table2\".\"field2\" = 0";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
