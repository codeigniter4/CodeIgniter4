<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 */
class AliasTest extends CIUnitTestCase
{
	use DatabaseTestTrait;

	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testAlias()
	{
		$builder = $this->db->table('job j');

		$jobs = $builder
			->where('j.name', 'Developer')
			->get();

		$this->assertEquals(1, count($jobs->getResult()));
	}

	//--------------------------------------------------------------------

}
