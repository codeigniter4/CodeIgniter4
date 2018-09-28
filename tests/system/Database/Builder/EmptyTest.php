<?php namespace Builder;

use CodeIgniter\Database\BaseBuilder;
use Tests\Support\Database\MockConnection;

class EmptyTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	public function setUp()
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testEmptyWithNoTable()
	{
		$builder = new BaseBuilder('jobs', $this->db);
		$builder->returnDeleteSQL = true;

		$answer = $builder->emptyTable(true);

		$expectedSQL   = "DELETE FROM \"jobs\"";

		$this->assertEquals($expectedSQL, str_replace("\n", ' ', $answer));
	}

	//--------------------------------------------------------------------

}
