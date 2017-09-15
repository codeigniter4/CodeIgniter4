<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add Permissions for the core modules
 * Remove/Rename old permissions
 */
class Migration_Permissions_for_core_modules extends Migration
{
	/**
	 * @var string The name of the Permissions table
	 */
	private $table_name = 'permissions';

	/**
	 * @var string The name of the Role Permissions table
	 */
	private $ref_table = 'role_permissions';

	/**
	 * @var array Core Module permissions to be added
	 */
	private $data = array(
		array(
			'name' => 'Bonfire.Activities.View',
			'description' => 'To view the Activities menu.',
		),
		array(
			'name' => 'Bonfire.Database.View',
			'description' => 'To view the Database menu.',
		),
		array(
			'name' => 'Bonfire.Migrations.View',
			'description' => 'To view the Migrations menu.',
		),
		array(
			'name' => 'Bonfire.Modulebuilder.View',
			'description' => 'To view the Modulebuilder menu.',
		),
		array(
			'name' => 'Bonfire.Roles.View',
			'description' => 'To view the Roles menu.',
		),
		array(
			'name' => 'Bonfire.Sysinfo.View',
			'description' => 'To view the System Information page.',
		),
		array(
			'name' => 'Bonfire.Translate.Manage',
			'description' => 'To manage the Language Translation.',
		),
		array(
			'name' => 'Bonfire.Translate.View',
			'description' => 'To view the Language Translate menu.',
		),
		array(
			'name' => 'Bonfire.UI.View',
			'description' => 'To view the UI/Keyboard Shortcut menu.',
		),
		array(
			'name' => 'Bonfire.Update.Manage',
			'description' => 'To manage the Bonfire Update.',
		),
		array(
			'name' => 'Bonfire.Update.View',
			'description' => 'To view the Developer Update menu.',
		),
	);

	/**
	 * @var array The names of the permissions to be renamed
	 */
	private $old_permission_names = array(
		'Permissions.Settings.Manage',
		'Permissions.Settings.View',
	);

	/**
	 * @var array The new names of the permissions to be renamed
	 */
	private $new_permission_names = array(
		'Bonfire.Permissions.Manage',
		'Bonfire.Permissions.View',
	);

	/**
	 * @var array Permissions to remove
	 */
	private $remove_permissions = array(
		array(
			'name' => 'Permissions.Banned.Manage',
			'description' => 'To manage the access control permissions for the Banned role.',
		),
		array(
			'name' => 'Bonfire.Activities.Manage',
			'description' => 'Allow users to access the Activities Reports.',
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
		// Rename some permissions
		$permission_count = count($this->old_permission_names);
		for ($x=0; $x < $permission_count; $x++)
		{
			$this->db->where('name', $this->old_permission_names[$x])
				->update($this->table_name, array('name' => $this->new_permission_names[$x]));
		}

		// Add new permissions
		$permission_ids = array();
		foreach ($this->data as $permission)
		{
			$this->db->insert($this->table_name, $permission);

			// Collect the permission_ids and prepare to add them to the admin role
			$permission_ids[] = array(
				'role_id' => $this->admin_role_id,
				'permission_id' => $this->db->insert_id(),
			);
		}

		// Add the new permissions to the admin role
		if ( ! empty($permission_ids))
		{
			$this->db->insert_batch($this->ref_table, $permission_ids);
		}

		// remove old permissions
		$remove_ids = array();
		$remove_names = array();
		foreach ($this->remove_permissions as $permission)
		{
			$remove_names[] = $permission['name'];

			// get the permission_ids to remove
			$query = $this->db->select('permission_id')
				->where('name', $permission['name'])
				->get($this->table_name);
			foreach ($query->result() as $row)
			{
				$remove_ids[] = $row->permission_id;
			}
		}

		if ( ! empty($remove_ids))
		{
			// remove the permissions from the roles
			$this->db->where_in('permission_id', $remove_ids)
				->delete($this->ref_table);
		}
		if ( ! empty($remove_names))
		{
			// delete the permissions
			$this->db->where_in('name', $remove_names)
				->delete($this->table_name);
		}
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$permission_ids = array();
		$permission_names = array();
		foreach ($this->data as $permission)
		{
			$permission_names[] = $permission['name'];

			$query = $this->db->select('permission_id')
				->where('name', $permission['name'])
				->get($this->table_name);

			foreach ($query->result() as $row)
			{
				$permission_ids[] = $row->permission_id;
			}
		}

		if ( ! empty($permission_ids))
		{
			$this->db->where_in('permission_id', $permission_ids)
				->delete($this->ref_table);
		}
		if ( ! empty($permission_names))
		{
			$this->db->where_in('name', $permission_names)
				->delete($this->table_name);
		}

		$ref_data = array();
		foreach ($this->remove_permissions as $permission)
		{
			$this->db->insert($this->table_name, $permission);
			$ref_data[] = array(
				'role_id' => $this->admin_role_id,
				'permission_id' => $this->db->insert_id(),
			);
		}

		if ( ! empty($ref_data))
		{
			$this->db->insert_batch($this->ref_table, $ref_data);
		}
	}
}