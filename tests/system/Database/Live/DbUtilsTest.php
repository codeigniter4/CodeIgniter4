<?php

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\DatabaseFactory;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class DbUtilsTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

	//--------------------------------------------------------------------

	public function testUtilsBackup()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->backup();
	}

	//--------------------------------------------------------------------

	public function testUtilsBackupWithParamsArray()
	{
		$util = (new DatabaseFactory())->utils($this->db);

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
		$util = (new DatabaseFactory())->utils($this->db);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->backup('db_jobs');
	}

	//--------------------------------------------------------------------

	public function testUtilsListDatabases()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		if ($this->db->DBDriver === 'MySQLi')
		{
			$databases = $util->listDatabases();

			$this->assertTrue(in_array('test', $databases));
		}
		elseif ($this->db->DBDriver === 'Postgre')
		{
			$databases = $util->listDatabases();

			$this->assertTrue(in_array('test', $databases));
		}
		elseif ($this->db->DBDriver === 'SQLite')
		{
			$this->expectException(DatabaseException::class);
			$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

			$util->listDatabases();
		}
	}

	//--------------------------------------------------------------------

	public function testUtilsDatabaseExist()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		if ($this->db->DBDriver === 'MySQLi')
		{
			$exist = $util->databaseExists('test');

			$this->assertTrue($exist);
		}
		elseif ($this->db->DBDriver === 'Postgre')
		{
			$exist = $util->databaseExists('test');

			$this->assertTrue($exist);
		}
		elseif ($this->db->DBDriver === 'SQLite')
		{
			$this->expectException(DatabaseException::class);
			$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

			$util->databaseExists('test');
		}
	}

	//--------------------------------------------------------------------

	public function testUtilsOptimizeDatabase()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$d = $util->optimizeDatabase();

		$this->assertTrue((bool)$d);
	}

	//--------------------------------------------------------------------

	public function testUtilsOptimizeTableFalseOptimizeDatabase()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$this->setPrivateProperty($util, 'optimizeTable', false);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->optimizeDatabase();
	}

	//--------------------------------------------------------------------

	public function testUtilsOptimizeTable()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$d = $util->optimizeTable('db_job');

		if ($this->db->DBDriver === 'Postgre' || $this->db->DBDriver === 'SQLite')
		{
			$this->assertFalse((bool)$d);
		}
		else
		{
			$this->assertTrue((bool)$d);
		}
	}

	//--------------------------------------------------------------------

	public function testUtilsOptimizeTableFalseOptimizeTable()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$this->setPrivateProperty($util, 'optimizeTable', false);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->optimizeTable('db_job');
	}

	//--------------------------------------------------------------------

	public function testUtilsRepairTable()
	{
		$util = (new DatabaseFactory())->utils($this->db);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->repairTable('db_job');
	}

	//--------------------------------------------------------------------

	public function testUtilsCSVFromResult()
	{
		$data = $this->db->table('job')->get();

		$util = (new DatabaseFactory())->utils($this->db);

		$data = $util->getCSVFromResult($data);

		$data = array_filter(preg_split('/(\r\n|\n|\r)/', $data));

		$this->assertEquals('"1","Developer","Awesome job, but sometimes makes you bored","","",""', $data[1]);
	}

	//--------------------------------------------------------------------

	public function testUtilsXMLFromResult()
	{
		$data = $this->db->table('job')->where('id', 4)->get();

		$util = (new DatabaseFactory())->utils($this->db);

		$data = $util->getXMLFromResult($data);

		$expected = '<root><element><id>4</id><name>Musician</name><description>Only Coldplay can actually called Musician</description><created_at></created_at><updated_at></updated_at><deleted_at></deleted_at></element></root>';

		$actual = preg_replace('#\R+#', '', $data);
		$actual = preg_replace('/[ ]{2,}|[\t]/', '', $actual);

		$this->assertEquals($expected, $actual);
	}

	//--------------------------------------------------------------------
}
