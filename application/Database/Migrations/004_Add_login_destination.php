<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add the login_destination field to the roles table
 */
class Migration_Add_login_destination extends Migration
{
	/****************************************************************
	 * Table Names
	 */
	private $roles_table = 'roles';

	/****************************************************************
	 * Field Definitions
	 */
	private $roles_fields = array(
		'login_destination'	=> array(
			'type'			=> 'VARCHAR',
			'constraint'	=> 255,
			'default'		=> '/',
			'null'			=> false,
		),
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->dbforge->add_column($this->roles_table, $this->roles_fields);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		foreach ($this->roles_fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->roles_table, $column_name);
		}
	}
}