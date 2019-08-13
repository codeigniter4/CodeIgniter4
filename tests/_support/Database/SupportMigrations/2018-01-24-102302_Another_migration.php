<?php namespace App\Database\Migrations;

class Migration_another_migration extends \CodeIgniter\Database\Migration
{
	public function up()
	{
		$fields = [
			'value' => [
				'type'       => 'VARCHAR',
				'constraint' => 255,
			],
		];
		$this->forge->addColumn('foo', $fields);

		$this->db->table('foo')->insert([
			'key'   => 'foobar',
			'value' => 'raboof',
		]);
	}

	public function down()
	{
		$this->forge->dropColumn('foo', 'value');
	}
}
