<?php namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Test\CIDatabaseTestCase;
use CodeIgniter\Database\SQLite3\Table;
use Config\Database;

/**
 * @group DatabaseLive
 */
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

	public function setUp(): void
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

	public function tearDown(): void
	{
		parent::tearDown();

		$this->forge->dropTable('foo', true);
		$this->forge->dropTable('foo_fk', true);
	}

	public function testFromTableThrowsOnNoTable()
	{
		$this->expectException('CodeIgniter\Database\Exceptions\DataException');
		$this->expectExceptionMessage('Table `foo` was not found in the current database.');

		$this->table->fromTable('foo');
	}

	public function testFromTableFillsDetails()
	{
		$this->createTable('foo');

		$this->assertTrue($this->db->tableExists('foo'));

		$this->table->fromTable('foo');

		$fields = $this->getPrivateProperty($this->table, 'fields');

		$this->assertCount(4, $fields);
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

	public function testDropColumnMaintainsKeys()
	{
		$this->createTable('foo');

		$oldKeys = $this->db->getIndexData('foo');

		$this->assertTrue(array_key_exists('foo_name', $oldKeys));
		$this->assertTrue(array_key_exists('foo_email', $oldKeys));

		$result = $this->table
			->fromTable('foo')
			->dropColumn('name')
			->run();

		$newKeys = $this->db->getIndexData('foo');

		$this->assertFalse(array_key_exists('foo_name', $newKeys));
		$this->assertTrue(array_key_exists('foo_email', $newKeys));

		$this->assertTrue($result);
	}

	public function testModifyColumnSuccess()
	{
		$this->createTable('janky');

		$result = $this->table
			->fromTable('janky')
			->modifyColumn([
				[
					'name'       => 'name',
					'new_name'   => 'serial',
					'type'       => 'int',
					'constraint' => 11,
					'null'       => true,
				],
			])
			->run();

		$this->assertTrue($result);

		$this->assertFalse($this->db->fieldExists('name', 'janky'));
		$this->assertTrue($this->db->fieldExists('serial', 'janky'));
	}

	public function testDropForeignKeySuccess()
	{
		$this->createTable('aliens');

		$keys = $this->db->getForeignKeyData('aliens');
		$this->assertEquals('key_id to aliens_fk.id', $keys[0]->constraint_name);

		$result = $this->table
			->fromTable('aliens')
			->dropForeignKey('key_id')
			->run();

		$this->assertTrue($result);

		$keys = $this->db->getForeignKeyData('aliens');
		$this->assertTrue(empty($keys));
	}

	public function testProcessCopiesOldData()
	{
		$this->createTable('foo');

		$this->db->table('foo_fk')->insert([
			'id'   => 1,
			'name' => 'bar',
		]);

		$this->db->table('foo')->insert([
			'id'     => 1,
			'name'   => 'George Clinton',
			'email'  => 'funkalicious@example.com',
			'key_id' => 1,
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
		// Create support table for foreign keys
		$this->forge->addField([
			'id'   => [
				'type'           => 'integer',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'name' => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => false,
			],
		]);
		$this->forge->createTable($tableName . '_fk');

		// Create main table
		$this->forge->addField([
			'id'     => [
				'type'           => 'integer',
				'constraint'     => 11,
				'unsigned'       => true,
				'auto_increment' => true,
			],
			'name'   => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => false,
			],
			'email'  => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
			'key_id' => [
				'type'       => 'integer',
				'constraint' => 11,
				'unsigned'   => true,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->addKey('name');
		$this->forge->addUniqueKey('email');
		$this->forge->addForeignKey('key_id', $tableName . '_fk', 'id');
		$this->forge->createTable($tableName);
	}
}
