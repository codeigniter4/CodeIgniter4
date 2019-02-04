<?php namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Database\SQLite3\Table;
use Config\Database;

class AlterTableTest extends CIDatabaseTestCase
{
	protected $refresh = true;

	/**
	 * @var Table
	 */
	protected $table;

	/**
	 * @var \CodeIgniter\Database\SQLite3\Connection
	 */
	protected $db;

	/**
	 * @var \CodeIgniter\Database\SQLite3\Forge
	 */
	protected $forge;

	public function setUp()
	{
		parent::setUp();

		$config = [
			'DBDriver' => 'SQLite3',
			'database' => ':memory:',
		];

		$this->db    = db_connect($config);
		$this->forge = Database::forge($config);
		$this->table = new Table($this->db, $this->forge);
	}

	public function tearDown()
	{
		parent::tearDown();

		$this->forge->dropTable('foo', true);
	}

	/**
	 * @expectedException        \CodeIgniter\Database\Exceptions\DataException
	 * @expectedExceptionMessage Table `foo` was not found in the current database.
	 */
	public function testFromTableThrowsOnNoTable()
	{
		$this->table->fromTable('foo');
	}

	public function testFromTableFillsDetails()
	{
		$this->createTable('foo');

		$this->assertTrue($this->db->tableExists('foo'));

		$this->table->fromTable('foo');

		$fields = $this->getPrivateProperty($this->table, 'fields');

		$this->assertCount(3, $fields);
		$this->assertTrue(array_key_exists('id', $fields));
		$this->assertNull($fields['id']['default']);
		$this->assertTrue($fields['id']['nullable']);
		$this->assertEquals('integer', strtolower($fields['id']['type']));

		$this->assertTrue(array_key_exists('name', $fields));
		$this->assertNull($fields['name']['default']);
		$this->assertFalse($fields['name']['nullable']);
		$this->assertEquals('varchar', strtolower($fields['name']['type']));

		$this->assertTrue(array_key_exists('email', $fields));
		$this->assertNull($fields['email']['default']);
		$this->assertTrue($fields['email']['nullable']);
		$this->assertEquals('varchar', strtolower($fields['email']['type']));

		$keys = $this->getPrivateProperty($this->table, 'keys');

		$this->assertCount(3, $keys);
		$this->assertTrue(array_key_exists('foo_name', $keys));
		$this->assertEquals(['fields' => ['name'], 'type' => 'index'], $keys['foo_name']);
		$this->assertTrue(array_key_exists('id', $keys));
		$this->assertEquals(['fields' => ['id'], 'type' => 'primary'], $keys['id']);
		$this->assertTrue(array_key_exists('id', $keys));
		$this->assertEquals(['fields' => ['id'], 'type' => 'primary'], $keys['id']);
	}

	public function testDropColumnSuccess()
	{
		$this->createTable('foo');

		$result = $this->table
			->fromTable('foo')
			->dropColumn('name')
			->run();

		$this->assertTrue($result);

		$columns = $this->db->getFieldNames('foo');

		$this->assertFalse(in_array('name', $columns));
		$this->assertTrue(in_array('id', $columns));
		$this->assertTrue(in_array('email', $columns));
	}

	public function testProcessCopiesOldData()
	{
		$this->createTable('foo');

		$this->db->table('foo')->insert([
			'id'    => 1,
			'name'  => 'George Clinton',
			'email' => 'funkalicious@example.com',
		]);

		$this->seeInDatabase('foo', ['name' => 'George Clinton']);

		$result = $this->table
			->fromTable('foo')
			->dropColumn('name')
			->run();

		$this->dontSeeInDatabase('foo', ['name' => 'George Clinton']);
		$this->seeInDatabase('foo', ['email' => 'funkalicious@example.com']);
	}

	protected function createTable(string $tableName = 'foo')
	{
		$this->forge->addField([
			'id'    => [
				'type'           => 'integer',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'name'  => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => false,
			],
			'email' => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->addKey('name');
		$this->forge->addUniqueKey('email');
		$this->forge->createTable($tableName);
	}
}
