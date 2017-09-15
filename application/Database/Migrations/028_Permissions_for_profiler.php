<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add view permissions for the Profiler
 */
class Migration_Permissions_for_profiler extends Migration
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
	 * @var array The permission to insert
	 */
	private $data = array(
		array(
			'name' => 'Bonfire.Profiler.View',
			'description' => 'To view the Console Profiler Bar.',
		),
	);

	/**
	 * @var int The role_id of the Administrator role
	 */
	private $admin_role_id = 1;

	/**
	 * @var int The role_id of the Developer role
	 */
	private $developer_role_id = 6;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$roles = array();
		foreach ($this->data as $permission)
		{
			$this->db->insert($this->table_name, $permission);
			$permission_id = $this->db->insert_id();

			// setup the permission to be added to the admin role
			$roles[] = array(
				'role_id' => $this->admin_role_id,
				'permission_id' => $permission_id,
			);

			// setup the permission to be added to the developer role
			$roles[] = array(
				'role_id' => $this->developer_role_id,
				'permission_id' => $permission_id,
			);
		}

		$this->db->insert_batch($this->ref_table, $roles);
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