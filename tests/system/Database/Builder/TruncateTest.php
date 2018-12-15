<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class TruncateTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testTruncate()
	{
		$builder = new BaseBuilder('user', $this->db);

		$expectedSQL = 'TRUNCATE "user"';

		$this->assertEquals($expectedSQL, $builder->truncate(true));
	}

	//--------------------------------------------------------------------

}
