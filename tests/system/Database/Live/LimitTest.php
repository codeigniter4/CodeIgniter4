<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class LimitTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testLimit()
	{
		$jobs = $this->db->table('job')
						->limit(2)
						->get()
						->getResult();

		$this->assertCount(2, $jobs);
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

		$this->assertCount(2, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

}
