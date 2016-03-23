<?php namespace CodeIgniter\Database;

class BaseQueryTest extends \CIUnitTestCase
{
	public function testQueryStoresSQL()
	{
	    $query = new BaseQuery();

		$sql = "SELECT * FROM users";

		$query->setQuery($sql);

		$this->assertEquals($sql, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testStoresDuration()
	{
	    $query = new BaseQuery();

		$start = microtime(true);

		$query->setDuration($start, $start+5);

		$this->assertEquals(5, $query->getDuration());
	}

	//--------------------------------------------------------------------

	public function testsStoresErrorInformation()
	{
	    $query = new BaseQuery();

		$code = 13;
		$msg  = 'Oops, yo!';

		$this->assertFalse($query->hasError());

		$query->setError($code, $msg);
		$this->assertTrue($query->hasError());
		$this->assertEquals($code, $query->getErrorCode());
		$this->assertEquals($msg, $query->getErrorMessage());
	}

	//--------------------------------------------------------------------

	public function testSwapPrefix()
	{
	    $query = new BaseQuery();

		$origPrefix = 'db_';
		$newPrefix  = 'ci_';

		$origSQL = 'SELECT * FROM db_users WHERE db_users.id = 1';
		$newSQL  = 'SELECT * FROM ci_users WHERE ci_users.id = 1';

		$query->setQuery($origSQL);
		$query->swapPrefix($origPrefix, $newPrefix);

		$this->assertEquals($newSQL, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function queryTypes()
	{
		return [
			'select' => [false, 'SELECT * FROM users'],
		    'set' => [true, 'SET ...'],
		    'insert' => [true, 'INSERT INTO ...'],
		    'update' => [true, 'UPDATE ...'],
		    'delete' => [true, 'DELETE ...'],
		    'replace' => [true, 'REPLACE ...'],
		    'create' => [true, 'CREATE ...'],
		    'drop' => [true, 'DROP ...'],
		    'truncate' => [true, 'TRUNCATE ...'],
		    'load' => [true, 'LOAD ...'],
		    'copy' => [true, 'COPY ...'],
		    'alter' => [true, 'ALTER ...'],
		    'rename' => [true, 'RENAME ...'],
		    'grant' => [true, 'GRANT ...'],
		    'revoke' => [true, 'REVOKE ...'],
		    'lock' => [true, 'LOCK ...'],
		    'unlock' => [true, 'UNLOCK ...'],
		    'reindex' => [true, 'REINDEX ...'],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider queryTypes
	 */
	public function testIsWriteType($expected, $sql)
	{
	    $query = new BaseQuery();

		$query->setQuery($sql);
		$this->assertSame($expected, $query->isWriteType());
	}

	//--------------------------------------------------------------------


}
