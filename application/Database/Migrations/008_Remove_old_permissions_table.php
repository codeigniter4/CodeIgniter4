<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Remove the backup permissions table left behind by 003_Permission_system_upgrade
 */
class Migration_Remove_old_permissions_table extends Migration
{
	/****************************************************************
	 * Table names
	 */
	/**
	 * @var string Table to remove
	 */
	private $table_name = 'permissions_old';

	/****************************************************************
	 * Field definitions
	 */
	/**
	 * @var array Fields for the table for use by down() method
	 */
	private $fields = array(
		'permission_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'auto_increment' => true,
			'null' => false,
		),
		'role_id' => array(
			'type' => 'INT',
			'constraint' => 11,
			'null' => false,
		),
		'Site_Signin_Allow' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Site_Signin_Offline' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Site_Content_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Site_Reports_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Site_Settings_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Site_Developer_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Roles_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Users_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Users_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Users_Add' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Database_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Emailer_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Emailer_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Logs_View' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'Bonfire_Logs_Manage' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
	);

	/****************************************************************
	 * Data for Insert
	 */
	/**
	 * @var array Permissions data for restoring the table
	 */
	private $data = array(
		array(
			'role_id' => 1,
			'Site_Signin_Allow' => 1,
			'Site_Signin_Offline' => 1,
			'Site_Content_View' => 1,
			'Site_Reports_View' => 1,
			'Site_Settings_View' => 1,
			'Site_Developer_View' => 1,
			'Bonfire_Roles_Manage' => 1,
			'Bonfire_Users_Manage' => 1,
			'Bonfire_Users_View' => 1,
			'Bonfire_Users_Add' => 1,
			'Bonfire_Database_Manage' => 1,
			'Bonfire_Emailer_Manage' => 1,
			'Bonfire_Emailer_View' => 1,
			'Bonfire_Logs_View' => 1,
			'Bonfire_Logs_Manage' => 1,
		),
		array(
			'role_id' => 2,
			'Site_Signin_Allow' => 1,
			'Site_Signin_Offline' => 1,
			'Site_Content_View' => 1,
			'Site_Reports_View' => 0,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Emailer_View' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
		array(
			'role_id' => 6,
			'Site_Signin_Allow' => 1,
			'Site_Signin_Offline' => 1,
			'Site_Content_View' => 1,
			'Site_Reports_View' => 1,
			'Site_Settings_View' => 1,
			'Site_Developer_View' => 1,
			'Bonfire_Roles_Manage' => 1,
			'Bonfire_Users_Manage' => 1,
			'Bonfire_Users_View' => 1,
			'Bonfire_Users_Add' => 1,
			'Bonfire_Database_Manage' => 1,
			'Bonfire_Emailer_Manage' => 1,
			'Bonfire_Emailer_View' => 1,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
		array(
			'role_id' => 3,
			'Site_Signin_Allow' => 0,
			'Site_Signin_Offline' => 0,
			'Site_Content_View' => 0,
			'Site_Reports_View' => 0,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Emailer_View' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
		),
		array(
			'role_id' => 4,
			'Site_Signin_Allow' => 1,
			'Site_Signin_Offline' => 0,
			'Site_Content_View' => 0,
			'Site_Reports_View' => 0,
			'Site_Settings_View' => 0,
			'Site_Developer_View' => 0,
			'Bonfire_Roles_Manage' => 0,
			'Bonfire_Users_Manage' => 0,
			'Bonfire_Users_View' => 0,
			'Bonfire_Users_Add' => 0,
			'Bonfire_Database_Manage' => 0,
			'Bonfire_Emailer_Manage' => 0,
			'Bonfire_Emailer_View' => 0,
			'Bonfire_Logs_View' => 0,
			'Bonfire_Logs_Manage' => 0,
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
		$this->dbforge->drop_table($this->table_name);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// Permissions
		$this->dbforge->add_field($this->fields);
		$this->dbforge->add_key('permission_id', true);
		$this->dbforge->add_key('role_id');
		$this->dbforge->create_table($this->table_name);

		$this->db->insert_batch($this->table_name, $this->data);
	}
}