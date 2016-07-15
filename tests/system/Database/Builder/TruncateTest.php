<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class TruncateTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testTruncate()
	{
		$builder = new BaseBuilder('user', $this->db);

		$expectedSQL   = "TRUNCATE \"user\"";

		$this->assertEquals($expectedSQL, $builder->truncate(true));
	}

	//--------------------------------------------------------------------

}
