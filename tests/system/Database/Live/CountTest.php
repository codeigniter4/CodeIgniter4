<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class CountTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testCountReturnsZeroWithNoResults()
	{
		$this->assertSame(0, $this->db->table('empty')->countAll());
	}

	//--------------------------------------------------------------------

	public function testCountAllReturnsCorrectInteger()
	{
		$this->assertSame(4, $this->db->table('job')->countAll());
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsReturnsZeroWithNoResults()
	{
		$this->assertSame(0, $this->db->table('job')->where('name', 'Superstar')->countAllResults());
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsReturnsCorrectValue()
	{
		$this->assertSame(1, $this->db->table('job')->where('name', 'Developer')->countAllResults());
	}

	//--------------------------------------------------------------------

	public function testCountAllResultsHonorsReset()
	{
		$builder = $this->db->table('job');

		$this->assertSame(1, $builder->where('name', 'Developer')->countAllResults(false));
		$this->assertSame(1, $builder->countAllResults());
	}

	//--------------------------------------------------------------------
}
