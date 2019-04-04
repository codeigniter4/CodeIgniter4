<?php namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Database;
use CodeIgniter\Test\CIDatabaseTestCase;

/**
 * @group DatabaseLive
 */
class DbUtils extends CIDatabaseTestCase
{
	//--------------------------------------------------------------------

	public function testUtilsBackup()
	{
		$util = (new Database())->loadUtils($this->db);

		$this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
		$this->expectExceptionMessage('Unsupported feature of the database platform you are using.');

		$util->backup();
	}

	//--------------------------------------------------------------------
}
