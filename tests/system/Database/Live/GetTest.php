<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class GetTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testGet()
	{
		$jobs = $this->db->table('job')
						->get()
						->getResult();

		$this->assertCount(4, $jobs);
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Politician', $jobs[1]->name);
		$this->assertEquals('Accountant', $jobs[2]->name);
		$this->assertEquals('Musician', $jobs[3]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWitLimit()
	{
		$jobs = $this->db->table('job')
						 ->get(2, 2)
						 ->getResult();

		$this->assertCount(2, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWhereArray()
	{
		$jobs = $this->db->table('job')
						 ->getWhere(['id' => 1])
						 ->getResult();

		$this->assertCount(1, $jobs);
		$this->assertEquals('Developer', $jobs[0]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWhereWithLimits()
	{
		$jobs = $this->db->table('job')
						 ->getWhere('id > 1', 1, 1)
						 ->getResult();

		$this->assertCount(1, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
	}

	//--------------------------------------------------------------------
}
