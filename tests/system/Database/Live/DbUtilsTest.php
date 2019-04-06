<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Database;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class DbUtilsTest extends CIDatabaseTestCase
{
	//--------------------------------------------------------------------

	public function testUtilsBackup()
	{
		$util = (new Database())->loadUtils($this->db);

		$this->expectException(DatabaseException::class);
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->backup();
	}

	//--------------------------------------------------------------------

	public function testUtilsListDatabases()
	{
		$util = (new Database())->loadUtils($this->db);

		if ($this->db->DBDriver === 'MySQLi')
		{
			$databases = $util->listDatabases();

			$this->assertEquals('test', $databases[0]);
		}
		elseif ($this->db->DBDriver === 'Postgre')
		{
			$databases = $util->listDatabases();

			$this->assertEquals('test', $databases[0]);
		}
		elseif ($this->db->DBDriver === 'SQLite3')
		{
			$this->expectException(DatabaseException::class);
			$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

			$util->listDatabases();
		}
	}

	//--------------------------------------------------------------------

	public function testUtilsDatabaseExist()
	{
		$util = (new Database())->loadUtils($this->db);

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
		elseif ($this->db->DBDriver === 'SQLite3')
		{
			$this->expectException(DatabaseException::class);
			$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

			$util->databaseExists('test');
		}
	}

	//--------------------------------------------------------------------
}
