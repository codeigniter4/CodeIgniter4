<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class CountTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testCountAll()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs"';

		$this->assertEquals($expectedSQL, $builder->countAll(true, true));
	}

	//--------------------------------------------------------------------

	public function testCountAllResults()
	{
		$builder = new BaseBuilder('jobs', $this->db);

		$answer = $builder->where('id >', 3)->countAllResults(false, true);

		$expectedSQL = 'SELECT COUNT(*) AS "numrows" FROM "jobs" WHERE "id" > :id:';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------
}
