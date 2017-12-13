<?php namespace CodeIgniter\Database\Live;

/**
 * @group DatabaseLive
 */
class ForgeTest extends \CIDatabaseTestCase
{
	protected $refresh = true;

	protected $seed = 'CITestSeeder';

	public function setUp()
	{
		parent::setUp();
		$this->forge = \Config\Database::forge($this->DBGroup);
	}

	public function testCreateTable()
	{
		$this->forge->dropTable('forge_test_table', true);

		$this->forge->addField([
			'id'       => [
				'type'           => 'INTEGER',
				'constraint'     => 11,
				'unsigned'       => false,
				'auto_increment' => true,
			],
		]);

		$this->forge->addKey('id', true);
		$this->forge->createTable('forge_test_table');

		$exist = $this->db->tableExists('forge_test_table');
		$this->forge->dropTable('forge_test_table', true);

		$this->assertTrue($exist);
	}

	public function testAddFields()
	{

		$this->forge->dropTable('forge_test_fields', true);

		$this->forge->addField([
			'id'       => [
				'type'           => 'INTEGER',
				'constraint'     => 11,
				'unsigned'       => false,
				'auto_increment' => true,
			],
			'username' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
				'unique'     => false,
			],
			'name'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
			'active'   => [
				'type'       => 'INTEGER',
				'constraint' => 11,
				'default'    => 0,
			],
		]);

		$this->forge->addKey('id', true);
		$create = $this->forge->createTable('forge_test_fields', true);

		//Check Field names
		$fieldsNames = $this->db->getFieldNames('forge_test_fields');
		$this->assertContains('id', $fieldsNames);
		$this->assertContains('username', $fieldsNames);
		$this->assertContains('name', $fieldsNames);
		$this->assertContains('active', $fieldsNames);


		$fieldsData = $this->db->getFieldData('forge_test_fields');

		$this->assertContains($fieldsData[0]->name, ['id', 'name', 'username', 'active']);
		$this->assertContains($fieldsData[1]->name, ['id', 'name', 'username', 'active']);

		if ($this->db->DBDriver === 'MySQLi')
		{
			//Check types
			$this->assertEquals($fieldsData[0]->type, 'int');
			$this->assertEquals($fieldsData[1]->type, 'varchar');

			$this->assertEquals($fieldsData[0]->max_length, 11);

			$this->assertNull($fieldsData[0]->default);
			$this->assertNull($fieldsData[1]->default);

			$this->assertEquals($fieldsData[0]->primary_key, 1);

			$this->assertEquals($fieldsData[1]->max_length, 255);

		}
		elseif ($this->db->DBDriver === 'Postgre')
		{
			//Check types
			$this->assertEquals($fieldsData[0]->type, 'integer');
			$this->assertEquals($fieldsData[1]->type, 'character varying');

			$this->assertEquals($fieldsData[0]->max_length, 32);
			$this->assertNull($fieldsData[1]->default);

			$this->assertEquals($fieldsData[1]->max_length, 255);
		}
		else
		{
			$this->assertTrue(false, "DB Driver not supported");
		}

		$this->forge->dropTable('forge_test_fields', true);

	}

	public function testCompositeKey()
	{
		$this->forge->addField([
			'id'      => [
				'type'           => 'INTEGER',
				'constraint'     => 3,
				'auto_increment' => true,
			],
			'code'    => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'company' => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addKey(['code', 'company']);
		$this->forge->createTable('forge_test_1', true);

		$keys = $this->db->getIndexData('forge_test_1');
		$this->assertEquals($keys[0]->fields, ['id']);
		$this->assertEquals($keys[1]->fields, ['code', 'company']);

		$this->forge->dropTable('forge_test_1', true);
	}

	public function testForeignKey()
	{

		$attributes = [];

		if ($this->db->DBDriver == 'MySQLi')
		{
			$attributes = ['ENGINE' => 'InnoDB'];
		}

		$this->forge->addField([
			'id'   => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'name' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('forge_test_users', true, $attributes);

		$this->forge->addField([
			'id'       => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'users_id' => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'name'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

		$this->forge->createTable('forge_test_invoices', true, $attributes);

		$foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

		$this->assertEquals($foreignKeyData[0]->constraint_name,
			$this->db->DBPrefix.'forge_test_invoices_users_id_foreign');
		$this->assertEquals($foreignKeyData[0]->table_name, $this->db->DBPrefix.'forge_test_invoices');
		$this->assertEquals($foreignKeyData[0]->foreign_table_name, $this->db->DBPrefix.'forge_test_users');

		$this->forge->dropTable('forge_test_invoices', true);
		$this->forge->dropTable('forge_test_users', true);

	}

	public function testDropForeignKey()
	{

		$attributes = [];

		if ($this->db->DBDriver == 'MySQLi')
		{
			$attributes = ['ENGINE' => 'InnoDB'];
		}

		$this->forge->addField([
			'id'   => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'name' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('forge_test_users', true, $attributes);

		$this->forge->addField([
			'id'       => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'users_id' => [
				'type'       => 'INTEGER',
				'constraint' => 11,
			],
			'name'     => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->addForeignKey('users_id', 'forge_test_users', 'id', 'CASCADE', 'CASCADE');

		$this->forge->createTable('forge_test_invoices', true, $attributes);

		$this->forge->dropForeignKey('forge_test_invoices', 'forge_test_invoices_users_id_foreign');

		$foreignKeyData = $this->db->getForeignKeyData('forge_test_invoices');

		$this->assertEmpty($foreignKeyData);

		$this->forge->dropTable('forge_test_invoices', true);
		$this->forge->dropTable('forge_test_users', true);

	}
}
