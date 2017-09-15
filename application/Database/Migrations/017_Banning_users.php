<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add banned flag to the users table
 * Remove banned from roles
 */
class Migration_banning_users extends Migration
{
	/**
	 * @var string The name of the users table
	 */
	private $users_table = 'users';

	/**
	 * @var string The name of the roles table
	 */
	private $roles_table = 'roles';

	/**
	 * @var array Fields to be added to the Users table
	 */
	private $users_fields = array(
		'banned' => array(
			'type' => 'TINYINT',
			'constraint' => 1,
			'default' => 0,
			'null' => false,
		),
		'ban_message' => array(
			'type' => 'VARCHAR',
			'constraint' => 255,
			'null' => true,
		),
	);

	/**
	 * @var array The role to remove
	 */
	private $roles_data = array(
		'role_name' => 'Banned',
		'description' => 'Banned users are not allowed to sign into your site.',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->dbforge->add_column($this->users_table, $this->users_fields);

		$this->db->where('role_name', $this->roles_data['role_name'])
			->delete($this->roles_table);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		foreach ($this->users_fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->users_table, $column_name);
		}

		$this->db->insert($this->roles_table, $this->roles_data);
	}
}