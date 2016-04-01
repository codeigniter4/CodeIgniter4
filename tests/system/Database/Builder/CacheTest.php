<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class CacheTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testQBCachingWorks()
	{
		$builder = $this->db->table('jobs');

		$response = $builder->startCache()
							->select('field1')
							->stopCache()
							->get(0, 0, true);

		$response = $builder->select('field2')
						    ->get(0, 0, true);

		$expectedSQL   = 'SELECT "field1", "field2" FROM "jobs"';

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $response));
	}

	//--------------------------------------------------------------------

}
