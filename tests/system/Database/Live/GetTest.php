<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testGet()
    {
        $jobs = $this->db->table('job')->get()->getResult();

        $this->assertCount(4, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Politician', $jobs[1]->name);
        $this->assertSame('Accountant', $jobs[2]->name);
        $this->assertSame('Musician', $jobs[3]->name);
    }

    //--------------------------------------------------------------------

    public function testGetWitLimit()
    {
        $jobs = $this->db->table('job')->get(2, 2)->getResult();

        $this->assertCount(2, $jobs);
        $this->assertSame('Accountant', $jobs[0]->name);
        $this->assertSame('Musician', $jobs[1]->name);
    }

    //--------------------------------------------------------------------

    public function testGetWhereArray()
    {
        $jobs = $this->db->table('job')
            ->getWhere(['id' => 1])
            ->getResult();

        $this->assertCount(1, $jobs);
        $this->assertSame('Developer', $jobs[0]->name);
    }

    //--------------------------------------------------------------------

    public function testGetWhereWithLimits()
    {
        $jobs = $this->db->table('job')
            ->getWhere('id > 1', 1, 1)
            ->getResult();

        $this->assertCount(1, $jobs);
        $this->assertSame('Accountant', $jobs[0]->name);
    }

    //--------------------------------------------------------------------

    public function testGetFieldCount()
    {
        $jobs = $this->db->table('job')->get()->getFieldCount();

        $this->assertSame(6, $jobs);
    }

    //--------------------------------------------------------------------

    public function testGetFieldNames()
    {
        $jobs = $this->db->table('job')->get()->getFieldNames();

        $this->assertTrue(in_array('name', $jobs, true));
        $this->assertTrue(in_array('description', $jobs, true));
    }

    //--------------------------------------------------------------------

    public function testGetFieldData()
    {
        $jobs = $this->db->table('job')->get()->getFieldData();

        $this->assertSame('id', $jobs[0]->name);
        $this->assertSame('name', $jobs[1]->name);

        $typeTest = $this->db->table('type_test')->get()->getFieldData();

        if ($this->db->DBDriver === 'SQLite3') {
            $this->assertSame('integer', $typeTest[0]->type_name); //INTEGER AUTO INC
            $this->assertSame('text', $typeTest[1]->type_name);  //VARCHAR
            $this->assertSame('text', $typeTest[2]->type_name);  //CHAR
            $this->assertSame('text', $typeTest[3]->type_name);  //TEXT
            $this->assertSame('integer', $typeTest[4]->type_name);  //SMALLINT
            $this->assertSame('integer', $typeTest[5]->type_name);  //INTEGER
            $this->assertSame('float', $typeTest[6]->type_name);  //FLOAT
            $this->assertSame('float', $typeTest[7]->type_name);  //NUMERIC
            $this->assertSame('text', $typeTest[8]->type_name);  //DATE
            $this->assertSame('text', $typeTest[9]->type_name);  //TIME
            $this->assertSame('text', $typeTest[10]->type_name);  //DATETIME
            $this->assertSame('text', $typeTest[11]->type_name);  //TIMESTAMP
            $this->assertSame('integer', $typeTest[12]->type_name);  //BIGINT
            $this->assertSame('float', $typeTest[13]->type_name);  //REAL
            $this->assertSame('text', $typeTest[14]->type_name);  //ENUM
            $this->assertSame('text', $typeTest[15]->type_name);  //SET
            $this->assertSame('text', $typeTest[16]->type_name);  //MEDIUMTEXT
            $this->assertSame('float', $typeTest[17]->type_name);  //DOUBLE
            $this->assertSame('float', $typeTest[18]->type_name);  //DECIMAL
            $this->assertSame('text', $typeTest[19]->type_name);  //BLOB
        }
        if ($this->db->DBDriver === 'MySQLi') {
            $this->assertSame('long', $typeTest[0]->type_name); //INTEGER AUTOINC
            $this->assertSame('var_string', $typeTest[1]->type_name);  //VARCHAR
            $this->assertSame('string', $typeTest[2]->type_name);  //CHAR
            $this->assertSame('blob', $typeTest[3]->type_name);  //TEXT
            $this->assertSame('short', $typeTest[4]->type_name);  //SMALLINT
            $this->assertSame('long', $typeTest[5]->type_name);  //INTEGER
            $this->assertSame('float', $typeTest[6]->type_name);  //FLOAT
            $this->assertSame('newdecimal', $typeTest[7]->type_name);  //NUMERIC
            $this->assertSame('date', $typeTest[8]->type_name);  //DATE
            $this->assertSame('time', $typeTest[9]->type_name);  //TIME
            $this->assertSame('datetime', $typeTest[10]->type_name);  //DATETIME
            $this->assertSame('timestamp', $typeTest[11]->type_name);  //TIMESTAMP
            $this->assertSame('longlong', $typeTest[12]->type_name); //BIGINT
            $this->assertSame('double', $typeTest[13]->type_name);  //REAL
            $this->assertSame('string', $typeTest[14]->type_name);  //ENUM
            $this->assertSame('string', $typeTest[15]->type_name);  //SET
            $this->assertSame('blob', $typeTest[16]->type_name);  //MEDIUMTEXT
            $this->assertSame('double', $typeTest[17]->type_name);  //DOUBLE
            $this->assertSame('newdecimal', $typeTest[18]->type_name);  //DECIMAL
            $this->assertSame('blob', $typeTest[19]->type_name);  //BLOB
        }
        if ($this->db->DBDriver === 'Postgre') {
            $this->assertSame('int4', $typeTest[0]->type_name); //INTEGER AUTOINC
            $this->assertSame('varchar', $typeTest[1]->type_name);  //VARCHAR
            $this->assertSame('bpchar', $typeTest[2]->type_name);  //CHAR
            $this->assertSame('text', $typeTest[3]->type_name);  //TEXT
            $this->assertSame('int2', $typeTest[4]->type_name);  //SMALLINT
            $this->assertSame('int4', $typeTest[5]->type_name);  //INTEGER
            $this->assertSame('float8', $typeTest[6]->type_name);  //FLOAT
            $this->assertSame('numeric', $typeTest[7]->type_name);  //NUMERIC
            $this->assertSame('date', $typeTest[8]->type_name);  //DATE
            $this->assertSame('time', $typeTest[9]->type_name);  //TIME
            $this->assertSame('timestamp', $typeTest[10]->type_name);  //DATETIME
            $this->assertSame('timestamp', $typeTest[11]->type_name);  //TIMESTAMP
            $this->assertSame('int8', $typeTest[12]->type_name); //BIGINT
        }
        if ($this->db->DBDriver === 'SQLSRV') {
            $this->assertSame('int', $typeTest[0]->type_name); //INTEGER AUTOINC
            $this->assertSame('varchar', $typeTest[1]->type_name);  //VARCHAR
            $this->assertSame('char', $typeTest[2]->type_name);  //CHAR
            $this->assertSame('text', $typeTest[3]->type_name);  //TEXT
            $this->assertSame('smallint', $typeTest[4]->type_name);  //SMALLINT
            $this->assertSame('int', $typeTest[5]->type_name);  //INTEGER
            $this->assertSame('float', $typeTest[6]->type_name);  //FLOAT
            $this->assertSame('numeric', $typeTest[7]->type_name);  //NUMERIC
            $this->assertNull($typeTest[8]->type_name);  //DATE
            $this->assertNull($typeTest[9]->type_name);  //TIME
            $this->assertNull($typeTest[10]->type_name);  //DATETIME
            $this->assertSame('bigint', $typeTest[11]->type_name); //BIGINT
            $this->assertSame('real', $typeTest[12]->type_name);  //REAL
            $this->assertSame('decimal', $typeTest[13]->type_name);  //DECIMAL
        }
    }

    //--------------------------------------------------------------------

    public function testGetDataSeek()
    {
        $data = $this->db->table('job')->get();

        if ($this->db->DBDriver === 'SQLite3') {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('SQLite3 doesn\'t support seeking to other offset.');
        }

        $data->dataSeek(3);

        $details = $data->getResult();
        $this->assertSame('Musician', $details[0]->name);
    }

    //--------------------------------------------------------------------
    public function testGetAnotherDataSeek()
    {
        $data = $this->db->table('job')->get();

        $data->dataSeek(0);

        $details = $data->getResult();

        $this->assertSame('Developer', $details[0]->name);
        $this->assertSame('Politician', $details[1]->name);
        $this->assertSame('Accountant', $details[2]->name);
        $this->assertSame('Musician', $details[3]->name);
    }

    //--------------------------------------------------------------------

    public function testFreeResult()
    {
        $data = $this->db->table('job')->where('id', 4)->get();

        $details = $data->getResult();

        $this->assertSame('Musician', $details[0]->name);

        $data->freeResult();

        $this->assertFalse($data->resultID);
    }

    //--------------------------------------------------------------------

    public function testGetRowWithColumnName()
    {
        $name = $this->db->table('user')->get()->getRow('name', 'array');

        $this->assertSame('Derek Jones', $name);
    }

    //--------------------------------------------------------------------

    public function testGetRowWithReturnType()
    {
        $user = $this->db->table('user')->get()->getRow(0, 'array');

        $this->assertSame('Derek Jones', $user['name']);
    }

    //--------------------------------------------------------------------

    public function testGetRowWithCustomReturnType()
    {
        $testClass = new class() {};

        $user = $this->db->table('user')->get()->getRow(0, get_class($testClass));

        $this->assertSame('Derek Jones', $user->name);
    }

    //--------------------------------------------------------------------

    public function testGetFirstRow()
    {
        $user = $this->db->table('user')->get()->getFirstRow();

        $this->assertSame('Derek Jones', $user->name);
    }

    //--------------------------------------------------------------------

    public function testGetLastRow()
    {
        $user = $this->db->table('user')->get()->getLastRow();

        $this->assertSame('Chris Martin', $user->name);
    }

    //--------------------------------------------------------------------

    public function testGetNextRow()
    {
        $user = $this->db->table('user')->get()->getNextRow();

        $this->assertSame('Ahmadinejad', $user->name);
    }

    //--------------------------------------------------------------------

    public function testGetPreviousRow()
    {
        $user = $this->db->table('user')->get();

        $user->currentRow = 3;

        $user = $user->getPreviousRow();

        $this->assertSame('Richard A Causey', $user->name);
    }
}
