<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Database;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DbUtilsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';
    protected static $origDebug;

    //--------------------------------------------------------------------

    /**
     * This test must run first to store the inital debug value before we tinker with it below
     */
    public function testFirst()
    {
        $this::$origDebug = $this->getPrivateProperty($this->db, 'DBDebug');

        $this->assertIsBool($this::$origDebug);
    }

    //--------------------------------------------------------------------

    public function testUtilsBackup()
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup();
    }

    //--------------------------------------------------------------------

    public function testUtilsBackupWithParamsArray()
    {
        $util = (new Database())->loadUtils($this->db);

        $params = [
            'format' => 'json',
        ];
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup($params);
    }

    //--------------------------------------------------------------------

    public function testUtilsBackupWithParamsString()
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup('db_jobs');
    }

    //--------------------------------------------------------------------

    public function testUtilsListDatabases()
    {
        $util = (new Database())->loadUtils($this->db);

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre', 'SQLSRV'], true)) {
            $databases = $util->listDatabases();

            $this->assertTrue(in_array($this->db->getDatabase(), $databases, true));
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

            $util->listDatabases();
        }
    }

    //--------------------------------------------------------------------

    public function testUtilsDatabaseExist()
    {
        $util = (new Database())->loadUtils($this->db);

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre', 'SQLSRV'], true)) {
            $exist = $util->databaseExists($this->db->getDatabase());

            $this->assertTrue($exist);
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

            $util->databaseExists($this->db->getDatabase());
        }
    }

    //--------------------------------------------------------------------

    public function testUtilsOptimizeDatabase()
    {
        $util = (new Database())->loadUtils($this->db);

        $d = $util->optimizeDatabase();

        $this->assertTrue((bool) $d);
    }

    //--------------------------------------------------------------------

    public function testUtilsOptimizeTableFalseOptimizeDatabaseDebugTrue()
    {
        $util = (new Database())->loadUtils($this->db);
        $this->setPrivateProperty($util, 'optimizeTable', false);

        // set debug to true -- WARNING this change will persist!
        $this->setPrivateProperty($this->db, 'DBDebug', true);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');
        $util->optimizeDatabase();

        // this point in code execution will never be reached
    }

    //--------------------------------------------------------------------

    public function testUtilsOptimizeTableFalseOptimizeDatabaseDebugFalse()
    {
        $util = (new Database())->loadUtils($this->db);
        $this->setPrivateProperty($util, 'optimizeTable', false);

        // set debug to false -- WARNING this change will persist!
        $this->setPrivateProperty($this->db, 'DBDebug', false);

        $result = $util->optimizeDatabase();
        $this->assertFalse($result);

        // restore original value grabbed from testFirst -- WARNING this change will persist!
        $this->setPrivateProperty($this->db, 'DBDebug', self::$origDebug);
    }

    //--------------------------------------------------------------------

    public function testUtilsOptimizeTable()
    {
        $util = (new Database())->loadUtils($this->db);

        $d = $util->optimizeTable('db_job');

        $this->assertTrue((bool) $d);
    }

    //--------------------------------------------------------------------

    public function testUtilsOptimizeTableFalseOptimizeTable()
    {
        $util = (new Database())->loadUtils($this->db);

        $this->setPrivateProperty($util, 'optimizeTable', false);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->optimizeTable('db_job');
    }

    //--------------------------------------------------------------------

    public function testUtilsRepairTable()
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->repairTable('db_job');
    }

    //--------------------------------------------------------------------

    public function testUtilsCSVFromResult()
    {
        $data = $this->db->table('job')->get();

        $util = (new Database())->loadUtils($this->db);

        $data = $util->getCSVFromResult($data);

        $data = array_filter(preg_split('/(\r\n|\n|\r)/', $data));

        $this->assertSame('"1","Developer","Awesome job, but sometimes makes you bored","","",""', $data[1]);
    }

    //--------------------------------------------------------------------

    public function testUtilsXMLFromResult()
    {
        $data = $this->db->table('job')->where('id', 4)->get();

        $util = (new Database())->loadUtils($this->db);

        $data = $util->getXMLFromResult($data);

        $expected = '<root><element><id>4</id><name>Musician</name><description>Only Coldplay can actually called Musician</description><created_at></created_at><updated_at></updated_at><deleted_at></deleted_at></element></root>';

        $actual = preg_replace('#\R+#', '', $data);
        $actual = preg_replace('/[ ]{2,}|[\t]/', '', $actual);

        $this->assertSame($expected, $actual);
    }
}
