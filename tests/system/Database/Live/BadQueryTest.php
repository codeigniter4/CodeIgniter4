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
		if ($this->db->DBDebug) {
			// expect an exception, class and message varies by DBMS
			$this->expectException(\Exception::class);
			$query = $this->db->query('SELECT * FROM table_does_not_exist');
		} else {
			// this throws an exception in this testing environment, but in production it'll return FALSE
			// perhaps check $this->db->DBDebug for different test?
			$query = $this->db->query('SELECT * FROM table_does_not_exist');
			$this->assertEquals(false, $query);
		}
	}

}
