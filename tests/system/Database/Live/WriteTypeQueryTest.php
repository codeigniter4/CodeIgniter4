<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class WriteTypeQueryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    public function testSet()
    {
        $sql = 'SET FOREIGN_KEY_CHECKS=0';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testInsert()
    {
        $builder = $this->db->table('jobs');

        $insertData = [
            'id'   => 1,
            'name' => 'Grocery Sales',
        ];
        $builder->testMode()->insert($insertData, true);
        $sql = $builder->getCompiledInsert();

        $this->assertTrue($this->db->isWriteType($sql));

        if ($this->db->DBDriver === 'Postgre') {
            $sql = "INSERT INTO my_table (col1, col2) VALUES ('Joe', 'Cool') RETURNING id;";

            $this->assertFalse($this->db->isWriteType($sql));
        }
    }

    //--------------------------------------------------------------------

    public function testUpdate()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $builder->testMode()->where('id', 1)->update(['name' => 'Programmer'], null, null);
        $sql = $builder->getCompiledInsert();

        $this->assertTrue($this->db->isWriteType($sql));

        if ($this->db->DBDriver === 'Postgre') {
            $sql = "UPDATE my_table SET col1 = 'foo' WHERE id = 2 RETURNING *;";

            $this->assertFalse($this->db->isWriteType($sql));
        }
    }

    //--------------------------------------------------------------------

    public function testDelete()
    {
        $builder = $this->db->table('jobs');
        $sql     = $builder->testMode()->delete(['id' => 1], null, true);

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testReplace()
    {
        if (in_array($this->db->DBDriver, ['Postgre', 'SQLSRV'], true)) {
            // these two were complaining about the builder stuff so i just cooked up this
            $sql = 'REPLACE INTO `db_jobs` (`title`, `name`, `date`) VALUES (:title:, :name:, :date:)';
        } else {
            $builder = $this->db->table('jobs');
            $data    = [
                'title' => 'My title',
                'name'  => 'My Name',
                'date'  => 'My date',
            ];
            $sql = $builder->testMode()->replace($data);
        }

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testCreate()
    {
        $sql = 'CREATE DATABASE foo';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testDrop()
    {
        $sql = 'DROP DATABASE foo';

        $this->assertTrue($this->db->isWriteType($sql));

        $sql = 'DROP TABLE foo';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testTruncate()
    {
        $builder = new BaseBuilder('user', $this->db);
        $sql     = $builder->testMode()->truncate();

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testLoad()
    {
        $sql = "LOAD DATA INFILE '/tmp/test.txt' INTO TABLE test FIELDS TERMINATED BY ','  LINES STARTING BY 'xxx';";

        $this->assertTrue($this->db->isWriteType($sql));
    }

    //--------------------------------------------------------------------

    public function testCopy()
    {
        $sql = "COPY demo(firstname, lastname) TO 'demo.txt' DELIMITER ' ';";

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testAlter()
    {
        $sql = 'ALTER TABLE supplier ADD supplier_name char(50);';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testRename()
    {
        if ($this->db->DBDriver === 'SQLSRV') {
            $sql = 'EXEC sp_rename table1 , table2 ;';

            $this->assertTrue($this->db->isWriteType($sql));
        }

        $sql = 'RENAME ...';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testGrant()
    {
        $sql = 'GRANT SELECT ON TABLE my_table TO user1,user2';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testRevoke()
    {
        $sql = 'REVOKE SELECT ON TABLE my_table FROM user1,user2';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testLock()
    {
        $sql = 'LOCK TABLE my_table IN SHARE MODE;';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testUnlock()
    {
        // i think this is only a valid command for MySQL?
        $sql = 'UNLOCK TABLES;';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testReindex()
    {
        // i think this is only a valid command for Postgre?
        $sql = 'REINDEX TABLE foo';

        $this->assertTrue($this->db->isWriteType($sql));
    }

    public function testSelect()
    {
        $builder = new BaseBuilder('users', $this->db);
        $builder->select('*');
        $sql = $builder->getCompiledSelect();

        $this->assertFalse($this->db->isWriteType($sql));
    }

    public function testTrick()
    {
        $builder = new BaseBuilder('users', $this->db);
        $builder->select('UPDATE');
        $sql = $builder->getCompiledSelect();

        $this->assertFalse($this->db->isWriteType($sql));
    }
}
