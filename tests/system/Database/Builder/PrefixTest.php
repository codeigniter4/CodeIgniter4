<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class PrefixTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection(['DBPrefix' => 'ci_']);
	}

	//--------------------------------------------------------------------

	public function testPrefixesSetOnTableNames()
	{
		$expected = 'ci_users';

		$this->assertEquals($expected, $this->db->prefixTable('users'));
	}

	//--------------------------------------------------------------------

}
