<?php

class Migration_Create_test_tables extends \CodeIgniter\Database\Migration
{
	public function up()
	{
		// User Table
		$this->forge->addField(array(
			'id' => array(
				'type' => 'INTEGER',
				'constraint' => 3,
				'auto_increment' => true
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 40
			),
			'email' => array(
				'type' => 'VARCHAR',
				'constraint' => 100
			),
			'country' => array(
				'type' => 'VARCHAR',
				'constraint' => 40
			)
		));
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('user', TRUE);

		// Job Table
		$this->forge->addField(array(
			'id' => array(
				'type' => 'INTEGER',
				'constraint' => 3,
				'auto_increment' => true
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 40
			),
			'description' => array(
				'type' => 'TEXT'
			)
		));
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('job', TRUE);

		// Misc Table
		$this->forge->addField(array(
			'id' => array(
				'type' => 'INTEGER',
				'constraint' => 3,
				'auto_increment' => true
			),
			'key' => array(
				'type' => 'VARCHAR',
				'constraint' => 40
			),
			'value' => array(
				'type' => 'TEXT'
			)
		));
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('misc', TRUE);

		// Empty Table
		$this->forge->addField(array(
			'id' => array(
				'type' => 'INTEGER',
				'constraint' => 3,
				'auto_increment' => true
			),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 40
			),
		));
		$this->forge->addKey('id', TRUE);
		$this->forge->createTable('empty', TRUE);
	}

	//--------------------------------------------------------------------

	public function down()
	{
	    $this->forge->dropTable('user');
	    $this->forge->dropTable('job');
	    $this->forge->dropTable('misc');
	    $this->forge->dropTable('empty');
	}

	//--------------------------------------------------------------------


}
