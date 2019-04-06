<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class GetTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	public function testGet()
	{
		$jobs = $this->db->table('job')
		                 ->get()
		                 ->getResult();

		$this->assertCount(4, $jobs);
		$this->assertEquals('Developer', $jobs[0]->name);
		$this->assertEquals('Politician', $jobs[1]->name);
		$this->assertEquals('Accountant', $jobs[2]->name);
		$this->assertEquals('Musician', $jobs[3]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWitLimit()
	{
		$jobs = $this->db->table('job')
		                 ->get(2, 2)
		                 ->getResult();

		$this->assertCount(2, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
		$this->assertEquals('Musician', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWhereArray()
	{
		$jobs = $this->db->table('job')
		                 ->getWhere(['id' => 1])
		                 ->getResult();

		$this->assertCount(1, $jobs);
		$this->assertEquals('Developer', $jobs[0]->name);
	}

	//--------------------------------------------------------------------

	public function testGetWhereWithLimits()
	{
		$jobs = $this->db->table('job')
		                 ->getWhere('id > 1', 1, 1)
		                 ->getResult();

		$this->assertCount(1, $jobs);
		$this->assertEquals('Accountant', $jobs[0]->name);
	}

	//--------------------------------------------------------------------

	public function testGetFieldCount()
	{
		$jobs = $this->db->table('job')
		                 ->get()
		                 ->getFieldCount();

		$this->assertEquals(4, $jobs);
	}

	//--------------------------------------------------------------------

	public function testGetFieldNames()
	{
		$jobs = $this->db->table('job')
		                 ->get()
		                 ->getFieldNames();

		$this->assertTrue(in_array('name', $jobs));
		$this->assertTrue(in_array('description', $jobs));
	}

	//--------------------------------------------------------------------

	public function testGetFieldData()
	{
		$jobs = $this->db->table('job')
		                 ->get()
		                 ->getFieldData();

		$this->assertEquals('id', $jobs[0]->name);
		$this->assertEquals('name', $jobs[1]->name);
	}

	//--------------------------------------------------------------------

	public function testGetDataSeek()
	{
		$data = $this->db->table('job')
		                 ->get();

		if ($this->db->DBDriver === 'SQLite3')
		{
			$this->expectException(DatabaseException::class);
			$this->expectExceptionMessage('SQLite3 doesn\'t support seeking to other offset.');
		}

		$data->dataSeek(3);

		$details = $data->getResult();
		$this->assertEquals('Musician', $details[0]->name);
	}

	//--------------------------------------------------------------------

	public function testFreeResult()
	{
		$data = $this->db->table('job')
		                 ->where('id', 4)
		                 ->get();

		$details = $data->getResult();

		$this->assertEquals('Musician', $details[0]->name);

		$data->freeResult();

		$this->assertFalse($data->resultID);
	}

	//--------------------------------------------------------------------

	public function testGetRowWithColumnName()
	{
		$name = $this->db->table('user')
		                 ->get()
		                 ->getRow('name', 'array');

		$this->assertEquals('Derek Jones', $name);
	}

	//--------------------------------------------------------------------

	public function testGetRowWithReturnType()
	{
		$user = $this->db->table('user')
		                 ->get()
		                 ->getRow(0, 'array');

		$this->assertEquals('Derek Jones', $user['name']);
	}

	//--------------------------------------------------------------------

	public function testGetRowWithCustomReturnType()
	{

		$user = $this->db->table('user')
		                 ->get()
		                 ->getRow(0, 'Tests\Support\Database\MockTestClass');


		$this->assertEquals('Derek Jones', $user->name);
	}

	//--------------------------------------------------------------------

}