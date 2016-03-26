<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class GroupTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testGroupBy()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name');

		$expectedSQL   = "SELECT \"name\" FROM \"user\" GROUP BY \"name\"";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------

	public function testHavingBy()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('name')
				->groupBy('name')
		        ->having('SUM(id) > 2');

		$expectedSQL   = "SELECT \"name\" FROM \"user\" GROUP BY \"name\" HAVING SUM(id) > 2";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
