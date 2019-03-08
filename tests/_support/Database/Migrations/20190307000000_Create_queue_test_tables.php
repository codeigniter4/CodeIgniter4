<?php namespace Tests\Support\Database\Migrations;

class Migration_Create_queue_test_tables extends \CodeIgniter\Database\Migration
{
	public function up()
	{
		\CodeIgniter\Queue\Handlers\DatabaseHandler::migrateUp($this->forge);
	}

	//--------------------------------------------------------------------

	public function down()
	{
		\CodeIgniter\Queue\Handlers\DatabaseHandler::migrateDown($this->forge);
	}
}
