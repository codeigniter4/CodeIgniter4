<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class JoinTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testSimpleJoin()
	{
		$row = $this->db->table('job')
						->select('job.id as job_id, job.name as job_name, user.id as user_id, user.name as user_name')
						->join('user', 'user.id = job.id')
						->get()
						->getRow();

		$this->assertEquals(1, $row->job_id);
		$this->assertEquals(1, $row->user_id);
		$this->assertEquals('Derek Jones', $row->user_name);
		$this->assertEquals('Developer', $row->job_name);
	}

	//--------------------------------------------------------------------

}
