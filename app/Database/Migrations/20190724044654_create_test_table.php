<?php namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_create_test_table extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id'  => [
				'type'           => 'integer',
				'constraint'     => 11,
				'auto_increment' => true,
				'unsigned'       => true,
			],
			'key' => [
				'type'       => 'varchar',
				'constraint' => 255,
				'null'       => true,
			],
		]);
		$this->forge->addPrimaryKey('id');
		$this->forge->createTable('test');
	}

	//--------------------------------------------------------------------

	public function down()
	{
		$this->forge->dropTable('test', true);
	}
}
