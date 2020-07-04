<?php namespace Tests\Support\DatabaseTestMigrations\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModelWithoutAutoincrement extends Migration
{
	public function up()
	{
		// Table without autoincrement field
		$this->forge->addField([
			'key'   => [
				'type'       => 'VARCHAR',
				'constraint' => 40,
			],
			'value' => ['type' => 'TEXT'],
		]);
		$this->forge->addKey('key', true);
		$this->forge->createTable('without_autoincrement', true);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('without_autoincrement', true);
	}

	//--------------------------------------------------------------------

}
