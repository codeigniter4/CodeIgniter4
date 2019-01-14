<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class SelectTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	//--------------------------------------------------------------------

	public function testSelectAllByDefault()
	{
		$row = $this->db->table('job')->get()->getRowArray();

		$this->assertArrayHasKey('id', $row);
		$this->assertArrayHasKey('name', $row);
		$this->assertArrayHasKey('description', $row);
	}

	//--------------------------------------------------------------------

	public function testSelectSingleColumn()
	{
		$row = $this->db->table('job')->select('name')->get()->getRowArray();

		$this->assertArrayNotHasKey('id', $row);
		$this->assertArrayHasKey('name', $row);
		$this->assertArrayNotHasKey('description', $row);
	}

	//--------------------------------------------------------------------

	public function testSelectMultipleColumns()
	{
		$row = $this->db->table('job')->select('name, description')->get()->getRowArray();

		$this->assertArrayNotHasKey('id', $row);
		$this->assertArrayHasKey('name', $row);
		$this->assertArrayHasKey('description', $row);
	}

	//--------------------------------------------------------------------

	public function testSelectMax()
	{
		$result = $this->db->table('job')->selectMax('id')->get()->getRow();

		$this->assertEquals(4, $result->id);
	}

	//--------------------------------------------------------------------

	public function testSelectMaxWithAlias()
	{
		$result = $this->db->table('job')->selectMax('id', 'xam')->get()->getRow();

		$this->assertEquals(4, $result->xam);
	}

	//--------------------------------------------------------------------

	public function testSelectMin()
	{
		$result = $this->db->table('job')->selectMin('id')->get()->getRow();

		$this->assertEquals(1, $result->id);
	}

	//--------------------------------------------------------------------

	public function testSelectMinWithAlias()
	{
		$result = $this->db->table('job')->selectMin('id', 'xam')->get()->getRow();

		$this->assertEquals(1, $result->xam);
	}

	//--------------------------------------------------------------------

	public function testSelectAvg()
	{
		$result = $this->db->table('job')->selectAvg('id')->get()->getRow();

		$this->assertEquals(2.5, $result->id);
	}

	//--------------------------------------------------------------------

	public function testSelectAvgWitAlias()
	{
		$result = $this->db->table('job')->selectAvg('id', 'xam')->get()->getRow();

		$this->assertEquals(2.5, $result->xam);
	}

	//--------------------------------------------------------------------

	public function testSelectSum()
	{
		$result = $this->db->table('job')->selectSum('id')->get()->getRow();

		$this->assertEquals(10, $result->id);
	}

	//--------------------------------------------------------------------

	public function testSelectSumWitAlias()
	{
		$result = $this->db->table('job')->selectSum('id', 'xam')->get()->getRow();

		$this->assertEquals(10, $result->xam);
	}

	//--------------------------------------------------------------------

	public function testSelectDistinctWorkTogether()
	{
		$users = $this->db->table('user')->select('country')->distinct()->get()->getResult();

		$this->assertCount(3, $users);
	}

	//--------------------------------------------------------------------

	public function testSelectDistinctCanBeTurnedOff()
	{
		$users = $this->db->table('user')->select('country')->distinct(false)->get()->getResult();

		$this->assertCount(4, $users);
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1226
	 */
	public function testSelectWithMultipleWheresOnSameColumn()
	{
		$users = $this->db->table('user')
			->where('id', 1)
			->orWhereIn('id', [2, 3])
			->get()
			->getResultArray();

		$this->assertCount(3, $users);

		foreach ($users as $user)
		{
			$this->assertTrue(in_array($user['id'], [1, 2, 3]));
		}
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1226
	 */
	public function testSelectWithMultipleWheresOnSameColumnAgain()
	{
		$users = $this->db->table('user')
						  ->whereIn('id', [1, 2])
						  ->orWhere('id', 3)
						  ->get()
						  ->getResultArray();

		$this->assertCount(3, $users);

		foreach ($users as $user)
		{
			$this->assertTrue(in_array($user['id'], [1, 2, 3]));
		}
	}
}
