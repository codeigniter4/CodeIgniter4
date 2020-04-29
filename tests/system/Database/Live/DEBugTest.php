<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class DEBugTest extends CIDatabaseTestCase
{

	protected $refresh = true;

	public function testDBDebugTrue()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', true);
		$this->expectException('Exception');
		$result = $this->db->simpleQuery('SELECT * FROM db_error');
	}

	public function testDBDebugFalse()
	{
		$this->setPrivateProperty($this->db, 'DBDebug', false);
		$result = $this->db->simpleQuery('SELECT * FROM db_error');
		$this->assertEquals(false, $result);
	}

	public function tearDown(): void
	{
		$this->setPrivateProperty($this->db, 'DBDebug', true);
		parent::tearDown();
	}
}
