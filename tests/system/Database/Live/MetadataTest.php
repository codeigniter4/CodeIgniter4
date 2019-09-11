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
		$prefix = $this->db->getPRefix();
		$expected = [
			$prefix . 'migrations',
			$prefix . 'user',
			$prefix . 'job',
			$prefix . 'misc',
			$prefix . 'empty',
			$prefix . 'secondary'
		];
		
		$result = $this->db->listTables();
		
		$this->assertEquals($expected, $result);
	}

	//--------------------------------------------------------------------

	public function testCanListTablesConstrainPrefix()
	{
		$prefix = $this->db->getPRefix();
		$expected = [
			$prefix . 'migrations',
			$prefix . 'user',
			$prefix . 'job',
			$prefix . 'misc',
			$prefix . 'empty',
			$prefix . 'secondary'
		];
		
		$result = $this->db->listTables(true);
		
		$this->assertEquals($expected, $result);
	}
  
	//--------------------------------------------------------------------

}
