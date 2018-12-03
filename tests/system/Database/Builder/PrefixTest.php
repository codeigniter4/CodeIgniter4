<?php namespace CodeIgniter\Database\Builder;

use Tests\Support\Database\MockConnection;

class PrefixTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

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
