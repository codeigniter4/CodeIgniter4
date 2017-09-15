<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add permission to add new roles
 */
class Migration_Permission_bonfire_roles_add extends Migration
{
	/**
	 * @var string The name of the permissions table
	 */
	private $table_name = 'permissions';

	/**
	 * @var string The name of the Role permissions table
	 */
	private $ref_table = 'role_permissions';

	/**
	 * @var array The permissions to migrate
	 */
	private $permission_array = array(
		array(
			'name' => 'Bonfire.Roles.Add',
			'description' => 'To add New Roles',
			'status' => 'active',
		),
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
		$role_permissions_data = array();
		foreach ($this->permission_array as $permission_value)
		{
			$this->db->insert($this->table_name, $permission_value);

			// collect the permission_ids to add to the admin role
			$role_permissions_data[] = array(
				'role_id' => $this->admin_role_id,
				'permission_id' => $this->db->insert_id(),
			);
		}

		// add the permissions to the admin role
		if ( ! empty($role_permissions_data))
		{
			$this->db->insert_batch($this->ref_table, $role_permissions_data);
		}
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$permission_names = array();
		$permission_ids = array();
		foreach($this->permission_array as $permission_value)
		{
			$permission_names[] = $permission_value['name'];
		}

		if ( ! empty($permission_names))
		{
			$query = $this->db->select('permission_id')
				->where_in('name', $permission_names)
				->get($this->table_name);

			foreach ($query->result() as $row)
			{
				$permission_ids[] = $row->permission_id;
			}

			if ( ! empty($permission_ids))
			{
				$this->db->where_in('permission_id', $permission_ids)
					->delete($this->ref_table);
			}

			$this->db->where_in('name', $permission_names)
				->delete($this->table_name);
		}
	}
}