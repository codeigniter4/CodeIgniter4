<?php namespace Tests\Support\DatabaseTestMigrations\Database\Migrations;

class Migration_Create_test_tables extends \CodeIgniter\Database\Migration
{
	public function up()
	{
		// SQLite3 uses auto increment different
		$unique_or_auto = $this->db->DBDriver === 'SQLite3' ? 'unique' : 'auto_increment';

		// User Table
		$this->forge->addField([
			'id'         => [
				'type'          => 'INTEGER',
				'constraint'    => 3,
				$unique_or_auto => true,
			],
			'name'       => [
				'type'       => 'VARCHAR',
				'constraint' => 80,
			],
			'email'      => [
				'type'       => 'VARCHAR',
				'constraint' => 100,
			],
			'country'    => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'created_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'deleted_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('user', true);

		// Job Table
		$this->forge->addField([
			'id'          => [
				'type'          => 'INTEGER',
				'constraint'    => 3,
				$unique_or_auto => true,
			],
			'name'        => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'description' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'created_at'  => [
				'type'       => 'INTEGER',
				'constraint' => 11,
				'null'       => true,
			],
			'updated_at'  => [
				'type'       => 'INTEGER',
				'constraint' => 11,
				'null'       => true,
			],
			'deleted_at'  => [
				'type'       => 'INTEGER',
				'constraint' => 11,
				'null'       => true,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('job', true);

		// Misc Table
		$this->forge->addField([
			'id'    => [
				'type'          => 'INTEGER',
				'constraint'    => 3,
				$unique_or_auto => true,
			],
			'key'   => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'value' => ['type' => 'TEXT'],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('misc', true);

		// Empty Table
		$this->forge->addField([
			'id'         => [
				'type'          => 'INTEGER',
				'constraint'    => 3,
				$unique_or_auto => true,
			],
			'name'       => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'created_at' => [
				'type' => 'DATE',
				'null' => true,
			],
			'updated_at' => [
				'type' => 'DATE',
				'null' => true,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('empty', true);

		// Secondary Table
		$this->forge->addField([
			'id'    => [
				'type'          => 'INTEGER',
				'constraint'    => 3,
				$unique_or_auto => true,
			],
			'key'   => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'value' => ['type' => 'TEXT'],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('secondary', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('user', true);
		$this->forge->dropTable('job', true);
		$this->forge->dropTable('misc', true);
		$this->forge->dropTable('empty', true);
		$this->forge->dropTable('secondary', true);
	}

	//--------------------------------------------------------------------

}
