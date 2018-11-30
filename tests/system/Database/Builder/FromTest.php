<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class FromTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testSimpleFrom()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->from('jobs');

		$expectedSQL = 'SELECT * FROM "user", "jobs"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testFromThatOverwrites()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->from('jobs', true);

		$expectedSQL = 'SELECT * FROM "jobs"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testFromWithMultipleTables()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->from(['jobs', 'roles']);

		$expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testFromWithMultipleTablesAsString()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->from(['jobs, roles']);

		$expectedSQL = 'SELECT * FROM "user", "jobs", "roles"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
