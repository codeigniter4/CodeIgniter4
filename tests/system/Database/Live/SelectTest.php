<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BaseResult;

/**
 * @group DatabaseLive
 */
class SelectTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function __construct()
	{
	    parent::__construct();

		$this->db = \Config\Database::connect($this->DBGroup);
		$this->db->initialize();
	}

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

		$this->assertFalse(array_key_exists('id', $row));
		$this->assertArrayHasKey('name', $row);
		$this->assertFalse(array_key_exists('description', $row));
	}

	//--------------------------------------------------------------------

	public function testSelectMultipleColumns()
	{
		$row = $this->db->table('job')->select('name, description')->get()->getRowArray();

		$this->assertFalse(array_key_exists('id', $row));
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

		$this->assertEquals(3, count($users));
	}

	//--------------------------------------------------------------------

	public function testSelectDistinctCanBeTurnedOff()
	{
		$users = $this->db->table('user')->select('country')->distinct(false)->get()->getResult();

		$this->assertEquals(4, count($users));
	}

	//--------------------------------------------------------------------
}