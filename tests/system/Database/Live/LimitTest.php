<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class LimitTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testLimit()
	{
	    $jobs = $this->db->table('job')
		                ->limit(2)
		                ->get()
		                ->getResult();

		$this->assertEquals(2, count($jobs));
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Politician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testLimitAndOffset()
	{
	    $jobs = $this->db->table('job')
		                ->limit(2, 2)
		                ->get()
		                ->getResult();

		$this->assertEquals(2, count($jobs));
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

}