<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add Permissions for the Settings Context
 */
class Migration_Permission_to_manage_settings extends Migration
{
	/**
	 * @var string Name of the Permissions table
	 */
	private $table_name = 'permissions';

	/**
	 * @var string Name of the Role permissions table
	 */
	private $ref_table = 'role_permissions';

	/**
	 * @var array New permissions
	 */
	private $data = array(
		array(
			'name' => 'Bonfire.Settings.View',
			'description' => 'To view the site settings page.',
		),
		array(
			'name' => 'Bonfire.Settings.Manage',
			'description' => 'To manage the site settings.',
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
		// add the permissions and store the permission_id values
		$roles = array();
		foreach ($this->data as $data)
		{
			$this->db->insert($this->table_name, $data);

			// gather the permission_ids to add to the admin role
			$roles[] = array(
				'role_id' => $this->admin_role_id,
				'permission_id' => $this->db->insert_id(),
			);
		}

		// add the permissions to the admin role
		if ( ! empty($roles))
		{
			$this->db->insert_batch($this->ref_table, $roles);
		}
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$permission_names = array();
		$permission_ids = array();

		foreach ($this->data as $permission)
		{
			$permission_names[] = $permission['name'];
		}

		if ( ! empty($permission_names))
		{
			// get the permission_id values
			$query = $this->db->select('permission_id')
				->where_in('name', $permission_names)
				->get($this->table_name);

			foreach ($query->result() as $row)
			{
				$permission_ids[] = $row->permission_id;
			}

			// use the permission_id values to delete the permissions from the roles
			if ( ! empty($permission_ids))
			{
				$this->db->where_in('permission_id', $permission_ids)
					->delete($this->ref_table);
			}

			// delete the permissions
			$this->db->where_in('name', $permission_names)
				->delete($this->table_name);
		}
	}
}