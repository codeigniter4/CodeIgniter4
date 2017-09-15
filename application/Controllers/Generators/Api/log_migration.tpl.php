<?php

$today = date('Y-m-d H:ia');

//--------------------------------------------------------------------
// The Template
//--------------------------------------------------------------------

echo "<?php

/**
 * Migration: Create API Log table.
 *
 * Created by: SprintPHP
 * Created on: {$today}
 */
class Migration_Create_api_log_table extends CI_Migration {

    public function up ()
    {
		\$fields = [
			'id' => [
				'type' => 'BIGINT',
				'constraint' => '20',
				'null' => false,
				'unsigned' => true,
				'auto_increment' => true
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,         // To log unauthorized requests
				'unsigned' => true
			],
			'ip_address' => [
				'type' => 'VARCHAR',
				'constraint' => 45,
				'null' => false
			],
			'duration' => [
				'type' => 'FLOAT',
				'null' => false
			],
			'request' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => false
			],
			'method' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => false
			],
			'created_on' => [
				'type' => 'DATETIME',
				'null' => false
			]
		];
		\$this->dbforge->add_field(\$fields);
		\$this->dbforge->add_key('id', true);
		\$this->dbforge->add_key('user_id');

		\$this->dbforge->create_table('api_logs', true, config_item('migration_create_table_attr'));
    }

    //--------------------------------------------------------------------

    public function down ()
    {
		\$this->dbforge->drop_column('users', 'api_key');
    }

    //--------------------------------------------------------------------

}";
