<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class FromTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function testFromCanAddTables()
	{
		$result = $this->db->table('job')->from('misc')->get()->getResult();

		$this->assertEquals(12, count($result));
	}

	//--------------------------------------------------------------------


	public function testFromCanOverride()
	{
	    $result = $this->db->table('job')->from('misc', true)->get()->getResult();

		$this->assertEquals(3, count($result));
	}

	//--------------------------------------------------------------------

	public function testFromWithWhere()
	{
		$result = $this->db->table('job')->from('user')->where('user.id', 1)->get()->getResult();

		$this->assertEquals(4, count($result));
	}

	//--------------------------------------------------------------------


}