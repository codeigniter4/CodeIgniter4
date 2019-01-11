<?php namespace App\Database\Migrations;

class Migration_some_migration extends \CodeIgniter\Database\Migration
{
	public function up()
	{
		$this->forge->addField([
			'key' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		]);
		$this->forge->createTable('foo', true);

		$this->db->table('foo')->insert([
			'key' => 'foobar',
		]);
	}

	public function down()
	{
		$this->forge->dropTable('foo');
	}
}
