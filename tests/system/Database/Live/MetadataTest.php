<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class MetadataTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testCanListTables()
	{
		$result = $this->db->listTables();

		// user, job, misc, migrations
		$this->assertCount(4, $result);
	}

	//--------------------------------------------------------------------

	public function testCanListTablesConstrainPrefix()
	{
		$result = $this->db->listTables(true);

		// user, job, misc, migrations
		$this->assertCount(4, $result);
	}
  
	//--------------------------------------------------------------------

}
