<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class BadQueryTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testBadQuery()
	{
		// this throws an exception in this testing environment, but in production it'll return FALSE
		// perhaps check $this->db->DBDebug for different test?
		$query = $this->db->query('SELECT * FROM table_does_not_exist');
		$this->assertEquals(false, $query);
	}

}
