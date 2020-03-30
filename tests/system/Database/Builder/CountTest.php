<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\Mock\MockConnection;

class CountTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testCountAll()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->testMode();

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs"';

		$this->assertEquals($expectedSQL, $builder->countAll(true));
	}

	//--------------------------------------------------------------------

	public function testCountAllResults()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->testMode();

		$answer = $builder->where('id >', 3)->countAllResults(false);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs" WHERE "id" > :id:';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsWithGroupBy()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->groupBy('id');
		$builder->testMode();

		$answer = $builder->where('id >', 3)->countAllResults(false);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM ( SELECT * FROM "jobs" WHERE "id" > :id: GROUP BY "id" ) CI_count_all_results';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsWithGroupByAndHaving()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->groupBy('id');
		$builder->having('1=1');
		$builder->testMode();

		$answer = $builder->where('id >', 3)->countAllResults(false);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM ( SELECT * FROM "jobs" WHERE "id" > :id: GROUP BY "id" HAVING 1 = 1 ) CI_count_all_results';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsWithHavingOnly()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->having('1=1');
		$builder->testMode();

		$answer = $builder->where('id >', 3)->countAllResults(false);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs" WHERE "id" > :id: HAVING 1 = 1';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------
}
