<?php namespace CodeIgniter\Database;

use Tests\Support\Database\MockConnection;

class QueryTest extends \CIUnitTestCase
{
	protected $db;

	//--------------------------------------------------------------------

	protected function setUp(): void
	{
		parent::setUp();

		$this->db = new MockConnection([]);
	}

	//--------------------------------------------------------------------

	public function testQueryStoresSQL()
	{
		$query = new Query($this->db);

		$sql = 'SELECT * FROM users';

		$query->setQuery($sql);

		$this->assertEquals($sql, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testStoresDuration()
	{
		$query = new Query($this->db);

		$start = microtime(true);

		$query->setDuration($start, $start + 5);

		$this->assertEquals(5, $query->getDuration());
	}

	//--------------------------------------------------------------------

	public function testGetStartTime()
	{
		$query = new Query($this->db);

		$start = round(microtime(true));

		$query->setDuration($start, $start + 5);

		$this->assertEquals($start, $query->getStartTime(true));
	}

	//--------------------------------------------------------------------

	public function testGetStartTimeNumberFormat()
	{
		$query = new Query($this->db);

		$start = microtime(true);

		$query->setDuration($start, $start + 5);

		$this->assertEquals(number_format($start, 6), $query->getStartTime());
	}

	//--------------------------------------------------------------------

	public function testsStoresErrorInformation()
	{
		$query = new Query($this->db);

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
		$query = new Query($this->db);

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
			'select'   => [
				false,
				'SELECT * FROM users',
			],
			'set'      => [
				true,
				'SET ...',
			],
			'insert'   => [
				true,
				'INSERT INTO ...',
			],
			'update'   => [
				true,
				'UPDATE ...',
			],
			'delete'   => [
				true,
				'DELETE ...',
			],
			'replace'  => [
				true,
				'REPLACE ...',
			],
			'create'   => [
				true,
				'CREATE ...',
			],
			'drop'     => [
				true,
				'DROP ...',
			],
			'truncate' => [
				true,
				'TRUNCATE ...',
			],
			'load'     => [
				true,
				'LOAD ...',
			],
			'copy'     => [
				true,
				'COPY ...',
			],
			'alter'    => [
				true,
				'ALTER ...',
			],
			'rename'   => [
				true,
				'RENAME ...',
			],
			'grant'    => [
				true,
				'GRANT ...',
			],
			'revoke'   => [
				true,
				'REVOKE ...',
			],
			'lock'     => [
				true,
				'LOCK ...',
			],
			'unlock'   => [
				true,
				'UNLOCK ...',
			],
			'reindex'  => [
				true,
				'REINDEX ...',
			],
		];
	}

	//--------------------------------------------------------------------

	/**
	 * @dataProvider queryTypes
	 */
	public function testIsWriteType($expected, $sql)
	{
		$query = new Query($this->db);

		$query->setQuery($sql);
		$this->assertSame($expected, $query->isWriteType());
	}

	//--------------------------------------------------------------------

	public function testSingleBindingOutsideOfArray()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE id = ?', 13);

		$expected = 'SELECT * FROM users WHERE id = 13';

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testBindingSingleElementInArray()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE id = ?', [13]);

		$expected = 'SELECT * FROM users WHERE id = 13';

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testBindingMultipleItems()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE id = ? OR name = ?', [13, 'Vader']);

		$expected = "SELECT * FROM users WHERE id = 13 OR name = 'Vader'";

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testBindingAutoEscapesParameters()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE name = ?', ["O'Reilly"]);

		$expected = "SELECT * FROM users WHERE name = 'O''Reilly'";

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	public function testNamedBinds()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE id = :id: OR name = :name:', ['id' => 13, 'name' => 'Geoffrey']);

		$expected = "SELECT * FROM users WHERE id = 13 OR name = 'Geoffrey'";

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	/**
	 * @group single
	 *
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/201
	 */
	public function testSimilarNamedBinds()
	{
		$query = new Query($this->db);

		$query->setQuery('SELECT * FROM users WHERE sitemap = :sitemap: OR site = :site:', ['sitemap' => 'sitemap', 'site' => 'site']);

		$expected = "SELECT * FROM users WHERE sitemap = 'sitemap' OR site = 'site'";

		$this->assertEquals($expected, $query->getQuery());
	}

	//--------------------------------------------------------------------

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
	 */
	public function testSetQueryBindsWithSetEscapeTrue()
	{
		$query = new Query($this->db);

		$query->setQuery('UPDATE user_table SET `x` = NOW() WHERE `id` = :id:', ['id' => 22], true);

		$expected = 'UPDATE user_table SET `x` = NOW() WHERE `id` = 22';

		$this->assertEquals($expected, $query->getQuery());
	}

	/**
	 * @see https://github.com/codeigniter4/CodeIgniter4/issues/1705
	 */
	public function testSetQueryBindsWithSetEscapeFalse()
	{
		$query = new Query($this->db);

		// The only time setQuery is called with setEscape = false
		// is when the query builder has already stored the escaping info...
		$binds = [
			'id' => [
				22,
				1,
			],
		];

		$query->setQuery('UPDATE user_table SET `x` = NOW() WHERE `id` = :id:', $binds, false);

		$expected = 'UPDATE user_table SET `x` = NOW() WHERE `id` = 22';

		$this->assertEquals($expected, $query->getQuery());
	}
}
