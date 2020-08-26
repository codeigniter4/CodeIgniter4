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

		$this->assertEquals(6, $jobs);
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

		$type_test = $this->db->table('type_test')
							  ->get()
							  ->getFieldData();

		if ($this->db->DBDriver === 'SQLite3')
		{
			$this->assertEquals('integer', $type_test[0]->type_name); //INTEGER AUTO INC
			$this->assertEquals('text', $type_test[1]->type_name);  //VARCHAR
			$this->assertEquals('text', $type_test[2]->type_name);  //CHAR
			$this->assertEquals('text', $type_test[3]->type_name);  //TEXT
			$this->assertEquals('integer', $type_test[4]->type_name);  //SMALLINT
			$this->assertEquals('integer', $type_test[5]->type_name);  //INTEGER
			$this->assertEquals('float', $type_test[6]->type_name);  //FLOAT
			$this->assertEquals('float', $type_test[7]->type_name);  //NUMERIC
			$this->assertEquals('text', $type_test[8]->type_name);  //DATE
			$this->assertEquals('text', $type_test[9]->type_name);  //TIME
			$this->assertEquals('text', $type_test[10]->type_name);  //DATETIME
			$this->assertEquals('text', $type_test[11]->type_name);  //TIMESTAMP
			$this->assertEquals('integer', $type_test[12]->type_name);  //BIGINT
			$this->assertEquals('text', $type_test[13]->type_name);  //ENUM
			$this->assertEquals('text', $type_test[14]->type_name);  //SET
			$this->assertEquals('text', $type_test[15]->type_name);  //MEDIUMTEXT
			$this->assertEquals('float', $type_test[16]->type_name);  //REAL
			$this->assertEquals('float', $type_test[17]->type_name);  //DOUBLE
			$this->assertEquals('float', $type_test[18]->type_name);  //DECIMAL
			$this->assertEquals('text', $type_test[19]->type_name);  //BLOB
		}
		if ($this->db->DBDriver === 'MySQLi')
		{
			$this->assertEquals('long', $type_test[0]->type_name); //INTEGER AUTOINC
			$this->assertEquals('var_string', $type_test[1]->type_name);  //VARCHAR
			$this->assertEquals('string', $type_test[2]->type_name);  //CHAR
			$this->assertEquals('blob', $type_test[3]->type_name);  //TEXT
			$this->assertEquals('short', $type_test[4]->type_name);  //SMALLINT
			$this->assertEquals('long', $type_test[5]->type_name);  //INTEGER
			$this->assertEquals('float', $type_test[6]->type_name);  //FLOAT
			$this->assertEquals('newdecimal', $type_test[7]->type_name);  //NUMERIC
			$this->assertEquals('date', $type_test[8]->type_name);  //DATE
			$this->assertEquals('time', $type_test[9]->type_name);  //TIME
			$this->assertEquals('datetime', $type_test[10]->type_name);  //DATETIME
			$this->assertEquals('timestamp', $type_test[11]->type_name);  //TIMESTAMP
			$this->assertEquals('longlong', $type_test[12]->type_name); //BIGINT
			$this->assertEquals('string', $type_test[13]->type_name);  //ENUM
			$this->assertEquals('string', $type_test[14]->type_name);  //SET
			$this->assertEquals('blob', $type_test[15]->type_name);  //MEDIUMTEXT
			$this->assertEquals('double', $type_test[16]->type_name);  //REAL
			$this->assertEquals('double', $type_test[17]->type_name);  //DOUBLE
			$this->assertEquals('newdecimal', $type_test[18]->type_name);  //DECIMAL
			$this->assertEquals('blob', $type_test[19]->type_name);  //BLOB
		}
		if ($this->db->DBDriver === 'Postgre')
		{
			$this->assertEquals('int4', $type_test[0]->type_name); //INTEGER AUTOINC
			$this->assertEquals('varchar', $type_test[1]->type_name);  //VARCHAR
			$this->assertEquals('bpchar', $type_test[2]->type_name);  //CHAR
			$this->assertEquals('text', $type_test[3]->type_name);  //TEXT
			$this->assertEquals('int2', $type_test[4]->type_name);  //SMALLINT
			$this->assertEquals('int4', $type_test[5]->type_name);  //INTEGER
			$this->assertEquals('float8', $type_test[6]->type_name);  //FLOAT
			$this->assertEquals('numeric', $type_test[7]->type_name);  //NUMERIC
			$this->assertEquals('date', $type_test[8]->type_name);  //DATE
			$this->assertEquals('time', $type_test[9]->type_name);  //TIME
			$this->assertEquals('timestamp', $type_test[10]->type_name);  //DATETIME
			$this->assertEquals('timestamp', $type_test[11]->type_name);  //TIMESTAMP
			$this->assertEquals('int8', $type_test[12]->type_name); //BIGINT
		}
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
	public function testGetAnotherDataSeek()
	{
		$data = $this->db->table('job')
						 ->get();

		$data->dataSeek(0);

		$details = $data->getResult();

		$this->assertEquals('Developer', $details[0]->name);
		$this->assertEquals('Politician', $details[1]->name);
		$this->assertEquals('Accountant', $details[2]->name);
		$this->assertEquals('Musician', $details[3]->name);
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
		$testClass = new class { };

		$user = $this->db->table('user')
						 ->get()
						 ->getRow(0, get_class($testClass));

		$this->assertEquals('Derek Jones', $user->name);
	}

	//--------------------------------------------------------------------

	public function testGetFirstRow()
	{
		$user = $this->db->table('user')
						 ->get()
						 ->getFirstRow();

		$this->assertEquals('Derek Jones', $user->name);
	}

	//--------------------------------------------------------------------

	public function testGetLastRow()
	{
		$user = $this->db->table('user')
						 ->get()
						 ->getLastRow();

		$this->assertEquals('Chris Martin', $user->name);
	}

	//--------------------------------------------------------------------

	public function testGetNextRow()
	{
		$user = $this->db->table('user')
						 ->get()
						 ->getNextRow();

		$this->assertEquals('Ahmadinejad', $user->name);
	}

	//--------------------------------------------------------------------

	public function testGetPreviousRow()
	{
		$user = $this->db->table('user')
						 ->get();

		$user->currentRow = 3;
		$user             = $user->getPreviousRow();

		$this->assertEquals('Richard A Causey', $user->name);
	}

	//--------------------------------------------------------------------
}
