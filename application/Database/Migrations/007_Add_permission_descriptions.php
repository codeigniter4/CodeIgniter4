<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add descriptions to the permissions
 */
class Migration_Add_permission_descriptions extends Migration
{
	/****************************************************************
	 * Table Names
	 */
	/**
	 * @var string Name of the Permissions table
	 */
	private $table_name = 'permissions';

	/****************************************************************
	 * Data for Insert
	 */
	/**
	 * @var array Permissions data
	 */
	private $data = array(
		array(
			'description' => 'Allow users to login to the site',
			'name' => 'Site.Signin.Allow',
		),
		array(
			'description' => 'Allow users to login to the site when the site is offline',
			'name' => 'Site.Signin.Offline',
		),
		array(
			'description' => 'Allow users to view the Content Context',
			'name' => 'Site.Content.View',
		),
		array(
			'description' => 'Allow users to view the Reports Context',
			'name' => 'Site.Reports.View',
		),
		array(
			'description' => 'Allow users to view the Settings Context',
			'name' => 'Site.Settings.View',
		),
		array(
			'description' => 'Allow users to view the Developer Context',
			'name' => 'Site.Developer.View',
		),
		array(
			'description' => 'Allow users to manage the user Roles',
			'name' => 'Bonfire.Roles.Manage',
		),
		array(
			'description' => 'Allow users to delete user Roles',
			'name' => 'Bonfire.Roles.Delete',
		),
		array(
			'description' => 'Allow users to manage the site Users',
			'name' => 'Bonfire.Users.Manage',
		),
		array(
			'description' => 'Allow users access to the User Settings',
			'name' => 'Bonfire.Users.View',
		),
		array(
			'description' => 'Allow users to add new Users',
			'name' => 'Bonfire.Users.Add',
		),
		array(
			'description' => 'Allow users to manage the Database settings',
			'name' => 'Bonfire.Database.Manage',
		),
		array(
			'description' => 'Allow users access to the Emailer settings',
			'name' => 'Bonfire.Emailer.View',
		),
		array(
			'description' => 'Allow users to manage the Emailer settings',
			'name' => 'Bonfire.Emailer.Manage',
		),
		array(
			'description' => 'Allow users access to the Log details',
			'name' => 'Bonfire.Logs.View',
		),
		array(
			'description' => 'Allow users to manage the Log files',
			'name' => 'Bonfire.Logs.Manage',
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
		$this->db->update_batch($this->table_name, $this->data, 'name');
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$update_data = array();
		foreach ($this->data as $data)
		{
			$update_data[] = array(
				'description' => '',
				'name' => $data['name'],
			);
		}
		$this->db->update_batch($this->table_name, $update_data, 'name');
	}
}