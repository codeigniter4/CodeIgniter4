<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class WhereTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testWhereSimpleKeyValue()
	{
	    $row = $this->db->table('job')->where('id', 1)->get()->getRow();

		$this->assertEquals(1, $row->id);
		$this->assertEquals('Developer', $row->name);
	}

	//--------------------------------------------------------------------

	public function testWhereCustomKeyValue()
	{
	    $jobs = $this->db->table('job')->where('id !=', 1)->get()->getResult();

		$this->assertEquals(3, count($jobs));
	}

	//--------------------------------------------------------------------

	public function testWhereArray()
	{
	    $jobs = $this->db->table('job')->where([
		    'id >' => 2,
	        'name !=' => 'Accountant'
	    ])->get()->getResult();

		$this->assertEquals(1, count($jobs));

		$job = current($jobs);
		$this->assertEquals('Musician', $job->name);
	}

	//--------------------------------------------------------------------

	public function testWhereCustomString()
	{
	    $jobs = $this->db->table('job')->where("id > 2 AND name != 'Accountant'")
		                    ->get()
		                    ->getResult();

		$this->assertEquals(1, count($jobs));

		$job = current($jobs);
		$this->assertEquals('Musician', $job->name);
	}

	//--------------------------------------------------------------------

	public function testOrWhere()
	{
	    $jobs = $this->db->table('job')
		                ->where('name !=', 'Accountant')
		                ->orWhere('id >', 3)
		                ->get()
		                ->getResult();

		$this->assertEquals(3, count($jobs));
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Politician', $jobs[1]->name);
		$this->assertEquals('Musician', $jobs[2]->name);
	}

	//--------------------------------------------------------------------

	public function testWhereIn()
	{
	    $jobs = $this->db->table('job')
						->whereIn('name', ['Politician', 'Accountant'])
		                ->get()
		                ->getResult();

		$this->assertEquals(2, count($jobs));
		$this->assertEquals('Politician', $jobs[0]->name);
		$this->assertEquals('Accountant', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testWhereNotIn()
	{
		$jobs = $this->db->table('job')
		                 ->whereNotIn('name', ['Politician', 'Accountant'])
		                 ->get()
		                 ->getResult();

		$this->assertEquals(2, count($jobs));
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

}