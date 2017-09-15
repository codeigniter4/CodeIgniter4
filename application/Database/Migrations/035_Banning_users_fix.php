<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Banning_users_fix extends Migration
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
	 * @var array Permissions to remove
	 */
	private $data = array(
		array(
			'name' => 'Site.Signin.Allow',
			'description' => 'Allow users to login to the site',
			'status' => 'active',
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
		$permission_ids = array();
		$permission_names = array();
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

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		/*
		HACK.

		Ensure current role (administrators if fresh install)
		can still log in.  If you downgrade the code as well,
		the _other_ roles will be treated as banned :).

		This is a courtesy to Bonfire developers tracking down regressions.
		In general, downgrading in production would not be a good idea.
		*/

		$role_permissions_data = array();

		foreach ($this->data as $permission)
		{
			$this->db->insert($this->table_name, $permission);

			$role_permissions_data[] = array(
				'role_id' => '1',
				'permission_id' => $this->db->insert_id(),
			);
		}

		if ( ! empty($role_permissions_data))
		{
			$this->db->insert_batch($this->ref_table, $role_permissions_data);
		}
	}
}