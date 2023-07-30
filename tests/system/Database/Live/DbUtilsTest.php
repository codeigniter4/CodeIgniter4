<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Database;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DbUtilsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testUtilsBackup(): void
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup();
    }

    public function testUtilsBackupWithParamsArray(): void
    {
        $util = (new Database())->loadUtils($this->db);

        $params = [
            'format' => 'json',
        ];
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup($params);
    }

    public function testUtilsBackupWithParamsString(): void
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->backup('db_jobs');
    }

    public function testUtilsListDatabases(): void
    {
        $util = (new Database())->loadUtils($this->db);

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre', 'SQLSRV', 'OCI8'], true)) {
            $databases = $util->listDatabases();

            $this->assertContains($this->db->getDatabase(), $databases);
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

            $util->listDatabases();
        }
    }

    public function testUtilsDatabaseExist(): void
    {
        $util = (new Database())->loadUtils($this->db);

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre', 'SQLSRV', 'OCI8'], true)) {
            $exist = $util->databaseExists($this->db->getDatabase());

            $this->assertTrue($exist);
        } elseif ($this->db->DBDriver === 'SQLite3') {
            $this->expectException(DatabaseException::class);
            $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

            $util->databaseExists($this->db->getDatabase());
        }
    }

    public function testUtilsOptimizeDatabase(): void
    {
        $util = (new Database())->loadUtils($this->db);

        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped(
                'Unsupported feature of the oracle database platform.'
            );
        }

        $d = $util->optimizeDatabase();

        $this->assertTrue((bool) $d);
    }

    public function testUtilsOptimizeTableFalseOptimizeDatabaseDebugTrue(): void
    {
        $util = (new Database())->loadUtils($this->db);
        $this->setPrivateProperty($util, 'optimizeTable', false);

        $this->enableDBDebug();

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->optimizeDatabase();

        // this point in code execution will never be reached
    }

    public function testUtilsOptimizeTableFalseOptimizeDatabaseDebugFalse(): void
    {
        $util = (new Database())->loadUtils($this->db);
        $this->setPrivateProperty($util, 'optimizeTable', false);

        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        $result = $util->optimizeDatabase();
        $this->assertFalse($result);

        $this->enableDBDebug();
    }

    public function testUtilsOptimizeTable(): void
    {
        $util = (new Database())->loadUtils($this->db);

        if ($this->db->DBDriver === 'OCI8') {
            $this->markTestSkipped(
                'Unsupported feature of the oracle database platform.'
            );
        }

        $d = $util->optimizeTable('db_job');

        $this->assertTrue($d);
    }

    public function testUtilsOptimizeTableFalseOptimizeTable(): void
    {
        $util = (new Database())->loadUtils($this->db);

        $this->setPrivateProperty($util, 'optimizeTable', false);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->optimizeTable('db_job');
    }

    public function testUtilsRepairTable(): void
    {
        $util = (new Database())->loadUtils($this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

        $util->repairTable('db_job');
    }

    public function testUtilsCSVFromResult(): void
    {
        $data = $this->db->table('job')->get();

        $util = (new Database())->loadUtils($this->db);

        $data = $util->getCSVFromResult($data);

        $data = array_filter(preg_split('/(\r\n|\n|\r)/', $data));

        $this->assertSame('"1","Developer","Awesome job, but sometimes makes you bored","","",""', $data[1]);
    }

    public function testUtilsXMLFromResult(): void
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
