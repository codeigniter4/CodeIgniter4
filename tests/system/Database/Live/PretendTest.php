<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Query;

/**
 * @group DatabaseLive
 */
class PretendTest extends \CIDatabaseTestCase
{
	public function testPretendReturnsQueryObject()
	{
		$result = $this->db->pretend(false)
						   ->table('user')
						   ->get();

		$this->assertFalse($result instanceof Query);

		$result = $this->db->pretend(true)
					->table('user')
				 	->get();

		$this->assertTrue($result instanceof Query);
	}

	//--------------------------------------------------------------------

}