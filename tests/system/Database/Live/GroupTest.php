<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class GroupTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testGroupBy()
	{
		$result = $this->db->table('user')
						->select('name')
						->groupBy('name')
						->get()
						->getResult();

		$this->assertCount(4, $result);
	}

	//--------------------------------------------------------------------

	public function testHavingBy()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->having('SUM(id) > 2')
						->get()
						->getResultArray();

		$this->assertCount(2, $result);
	}

	//--------------------------------------------------------------------

	public function testOrHavingBy()
	{
		$result = $this->db->table('user')
						->groupBy('id')
						->having('id >', 3)
						->orHaving('SUM(id) > 2')
						->get()
						->getResult();

		$this->assertCount(2, $result);
	}

	//--------------------------------------------------------------------

	public function testHavingIn()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->havingIn('name', ['Developer', 'Politician'])
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Developer', $result[0]->name);
		$this->assertEquals('Politician', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testorHavingIn()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->havingIn('name', ['Developer'])
						->orHavingIn('name', ['Politician'])
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Developer', $result[0]->name);
		$this->assertEquals('Politician', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testHavingNotIn()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->havingNotIn('name', ['Developer', 'Politician'])
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Musician', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testOrHavingNotIn()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->orHavingNotIn('name', ['Developer', 'Politician'])
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Musician', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testHavingLike()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->havingLike('name', 'elo')
						->get()
						->getResult();

		$this->assertCount(1, $result);
		$this->assertEquals('Developer', $result[0]->name);
	}

	//--------------------------------------------------------------------

	public function testNotHavingLike()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->notHavingLike('name', 'ian')
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Developer', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testOrHavingLike()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->havingLike('name', 'elo')
						->orHavingLike('name', 'cc')
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Developer', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testOrNotHavingLike()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->orNotHavingLike('name', 'ian')
						->get()
						->getResult();

		$this->assertCount(3, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Developer', $result[1]->name);
		$this->assertEquals('Musician', $result[2]->name);
	}

	//--------------------------------------------------------------------

	public function testAndHavingGroupStart()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->havingGroupStart()
							->having('SUM(id) <= 4')
							->havingLike('name', 'ant', 'before')
						->havingGroupEnd()
						->get()
						->getResult();

		$this->assertCount(1, $result);
		$this->assertEquals('Accountant', $result[0]->name);
	}

	//--------------------------------------------------------------------

	public function testOrHavingGroupStart()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->orHavingGroupStart()
							->having('SUM(id) <= 4')
							->havingLike('name', 'ant', 'before')
						->havingGroupEnd()
						->get()
						->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Musician', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testNotHavingGroupStart()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->notHavingGroupStart()
							->having('SUM(id) <= 4')
							->havingLike('name', 'ant', 'before')
						->havingGroupEnd()
						->get()
						->getResult();

		$this->assertCount(1, $result);
		$this->assertEquals('Musician', $result[0]->name);
	}

	//--------------------------------------------------------------------

	public function testOrNotHavingGroupStart()
	{
		$result = $this->db->table('job')
						->select('name')
						->groupBy('name')
						->orderBy('name', 'asc')
						->having('SUM(id) > 2')
						->orNotHavingGroupStart()
							->having('SUM(id) < 2')
							->havingLike('name', 'o')
						->havingGroupEnd()
						->get()
						->getResult();

		$this->assertCount(3, $result);
		$this->assertEquals('Accountant', $result[0]->name);
		$this->assertEquals('Musician', $result[1]->name);
		$this->assertEquals('Politician', $result[2]->name);
	}

	//--------------------------------------------------------------------

	public function testAndGroups()
	{
		$result = $this->db->table('user')
				->groupStart()
				->where('id >=', 3)
				->where('name !=', 'Chris Martin')
				->groupEnd()
				->where('country', 'US')
				->get()
				->getResult();

		$this->assertCount(1, $result);
		$this->assertEquals('Richard A Causey', $result[0]->name);
	}

	//--------------------------------------------------------------------

	public function testOrGroups()
	{
		$result = $this->db->table('user')
				->where('country', 'Iran')
				->orGroupStart()
				->where('id >=', 3)
				->where('name !=', 'Richard A Causey')
				->groupEnd()
				->get()
				->getResult();

		$this->assertCount(2, $result);
		$this->assertEquals('Ahmadinejad', $result[0]->name);
		$this->assertEquals('Chris Martin', $result[1]->name);
	}

	//--------------------------------------------------------------------

	public function testNotGroups()
	{
		$result = $this->db->table('user')
				->where('country', 'US')
				->notGroupStart()
				->where('id >=', 3)
				->where('name !=', 'Chris Martin')
				->groupEnd()
				->get()
				->getResult();

		$this->assertCount(1, $result);
		$this->assertEquals('Derek Jones', $result[0]->name);
	}

	//--------------------------------------------------------------------

	public function testOrNotGroups()
	{
		$result = $this->db->table('user')
				->where('country', 'US')
				->orNotGroupStart()
				->where('id >=', 2)
				->where('country', 'Iran')
				->groupEnd()
				->get()
				->getResult();

		$this->assertCount(3, $result);
		$this->assertEquals('Derek Jones', $result[0]->name);
		$this->assertEquals('Richard A Causey', $result[1]->name);
		$this->assertEquals('Chris Martin', $result[2]->name);
	}

	//--------------------------------------------------------------------

	public function testGroupByCount()
	{
		$result = $this->db->table('user')
				->selectCount('id', 'count')
				->groupBy('country')
				->orderBy('country', 'desc')
				->get()
				->getResult();

		$this->assertEquals(2, $result[0]->count);
	}
}
