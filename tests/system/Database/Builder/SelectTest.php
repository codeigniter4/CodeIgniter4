<?php namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\MockConnection;

class SelectTest extends \CIUnitTestCase
{
	protected $db;
	
	//--------------------------------------------------------------------
	
	public function setUp() 
	{
	    $this->db = new MockConnection([]);
	}
	
	//--------------------------------------------------------------------
	
	public function testSimpleSelect()
	{
	    $builder = new BaseBuilder('users', $this->db);

		$expected = "SELECT * FROM users";

		$this->assertEquals($expected, str_replace("\n", ' ', $builder->getCompiledSelect()));
	}
	
	//--------------------------------------------------------------------
	
	
}