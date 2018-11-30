<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class LikeTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testLikeDefault()
	{
		$job = $this->db->table('job')->like('name', 'veloper')->get();
		$job = $job->getRow();

		$this->assertEquals(1, $job->id);
		$this->assertEquals('Developer', $job->name);
	}

	//--------------------------------------------------------------------

	public function testLikeBefore()
	{
		$job = $this->db->table('job')->like('name', 'veloper', 'before')->get();
		$job = $job->getRow();

		$this->assertEquals(1, $job->id);
		$this->assertEquals('Developer', $job->name);
	}

	//--------------------------------------------------------------------

	public function testLikeAfter()
	{
		$job = $this->db->table('job')->like('name', 'Develop')->get();
		$job = $job->getRow();

		$this->assertEquals(1, $job->id);
		$this->assertEquals('Developer', $job->name);
	}

	//--------------------------------------------------------------------

	public function testLikeBoth()
	{
		$job = $this->db->table('job')->like('name', 'veloper', 'both')->get();
		$job = $job->getRow();

		$this->assertEquals(1, $job->id);
		$this->assertEquals('Developer', $job->name);
	}

	//--------------------------------------------------------------------

	public function testLikeCaseInsensitive()
	{
		$job = $this->db->table('job')->like('name', 'VELOPER', 'both', null, true)->get();
		$job = $job->getRow();

		$this->assertEquals(1, $job->id);
		$this->assertEquals('Developer', $job->name);
	}

	//--------------------------------------------------------------------

	public function testOrLike()
	{
		$jobs = $this->db->table('job')->like('name', 'ian')
						->orLike('name', 'veloper')
						->get()
						->getResult();

		$this->assertCount(3, $jobs);
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Politician', $jobs[1]->name);
		$this->assertEquals('Musician', $jobs[2]->name);
	}

	//--------------------------------------------------------------------

	public function testNotLike()
	{
		$jobs = $this->db->table('job')
						 ->notLike('name', 'veloper')
						 ->get()
						 ->getResult();

		$this->assertCount(3, $jobs);
		$this->assertEquals('Politician', $jobs[0]->name);
		$this->assertEquals('Accountant', $jobs[1]->name);
		$this->assertEquals('Musician', $jobs[2]->name);
	}

	//--------------------------------------------------------------------

	public function testOrNotLike()
	{
		$jobs = $this->db->table('job')
						 ->like('name', 'ian')
						 ->orNotLike('name', 'veloper')
						 ->get()
						 ->getResult();

		$this->assertCount(3, $jobs);
		$this->assertEquals('Politician', $jobs[0]->name);
		$this->assertEquals('Accountant', $jobs[1]->name);
		$this->assertEquals('Musician', $jobs[2]->name);
	}

	//--------------------------------------------------------------------

	public function testLikeSpacesOrTabs()
	{
		$builder = $this->db->table('misc');
		$spaces  = $builder->like('value', '   ')->get()->getResult();
		$tabs    = $builder->like('value', "\t")->get()->getResult();

		$this->assertCount(1, $spaces);
		$this->assertCount(1, $tabs);
	}

	//--------------------------------------------------------------------

}
