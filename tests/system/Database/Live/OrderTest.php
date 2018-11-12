<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class OrderTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testOrderAscending()
	{
		$jobs = $this->db->table('job')
						->orderBy('name', 'asc')
						->get()
						->getResult();

		$this->assertCount(4, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Developer', $jobs[1]->name);
		$this->assertEquals('Musician', $jobs[2]->name);
		$this->assertEquals('Politician', $jobs[3]->name);
	}

	//--------------------------------------------------------------------

	public function testOrderDescending()
	{
		$jobs = $this->db->table('job')
						 ->orderBy('name', 'desc')
						 ->get()
						 ->getResult();

		$this->assertCount(4, $jobs);
		$this->assertEquals('Accountant', $jobs[3]->name);
		$this->assertEquals('Developer', $jobs[2]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
		$this->assertEquals('Politician', $jobs[0]->name);
	}

	//--------------------------------------------------------------------

	public function testMultipleOrderValues()
	{
		$users = $this->db->table('user')
						->orderBy('country', 'asc')
						->orderBy('name', 'desc')
						->get()
						->getResult();

		$this->assertCount(4, $users);
		$this->assertEquals('Ahmadinejad', $users[0]->name);
		$this->assertEquals('Chris Martin', $users[1]->name);
		$this->assertEquals('Richard A Causey', $users[2]->name);
		$this->assertEquals('Derek Jones', $users[3]->name);
	}

	//--------------------------------------------------------------------

}
