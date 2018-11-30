<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class FromTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testFromCanAddTables()
	{
		$result = $this->db->table('job')->from('misc')->get()->getResult();

		$this->assertCount(12, $result);
	}

	//--------------------------------------------------------------------

	public function testFromCanOverride()
	{
		$result = $this->db->table('job')->from('misc', true)->get()->getResult();

		$this->assertCount(3, $result);
	}

	//--------------------------------------------------------------------

	public function testFromWithWhere()
	{
		$result = $this->db->table('job')->from('user')->where('user.id', 1)->get()->getResult();

		$this->assertCount(4, $result);
	}

	//--------------------------------------------------------------------

}
