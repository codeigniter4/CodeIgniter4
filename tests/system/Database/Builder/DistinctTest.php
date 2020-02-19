<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\Mock\MockConnection;

class DistinctTest extends \CodeIgniter\Test\CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testDelete()
	{
		$builder = new BaseBuilder('user', $this->db);

		$builder->select('country')->distinct();

		$expectedSQL = 'SELECT DISTINCT "country" FROM "user"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}

	//--------------------------------------------------------------------
}
