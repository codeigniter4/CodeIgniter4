<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add deleted field to roles
 * Add Bonfire.Roles.Delete permission
 */
class Migration_Add_role_delete_permissions extends Migration
{
	/****************************************************************
	 * Table Names
	 */
	/**
	 * @var array The name of the roles table
	 */
	private $roles_table = 'roles';

	/****************************************************************
	 * Field Definitions
	 */
	/**
	 * @var array Fields to be added to the roles table
	 */
	private $roles_fields = array(
		'deleted'	=> array(
			'type'			=> 'INT',
			'constraint'	=> 1,
			'default'		=> 0,
			'null'			=> false,
		),
	);

	/****************************************************************
	 * Data for Insert
	 */
	/**
	 * @var array Data to be inserted into the permissions table
	 * This is being inserted via the model rather than using Active Record
	 */
	private $permissions_data = array(
		'name'			=> 'Bonfire.Roles.Delete',
		'description'	=> '',
		'status'		=> 'active',
	);

	/**
	 * @var int The role_id of the Administrator role
	 */
	private $admin_role_id = 1;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// Add the new permission
		$ci =& get_instance();
		$ci->load->model('permissions/permission_model');
		$ci->load->model('roles/role_permission_model');

		$pid = $ci->permission_model->insert($this->permissions_data);

		if ($pid)
		{
			// Add the permission to the admin role.
			$ci->role_permission_model->create($this->admin_role_id, $pid);
		}

		// Add the deleted field to the roles table
		$this->dbforge->add_column($this->roles_table, $this->roles_fields);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// Delete the permissions assigned to roles
		$ci =& get_instance();
		$ci->load->model('permissions/permission_model');

		foreach ($this->permissions_data as $data)
		{
			$name = $data['name'];
			$perm = $ci->permission_model->find_by('name', $name);
			if ($perm)
			{
				$ci->permission_model->delete($perm->permission_id);
			}
		}

		// Remove the deleted column from roles
		foreach ($this->roles_fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->roles_table, $column_name);
		}
	}
}