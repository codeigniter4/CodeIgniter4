<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add the keyboard shortcuts to the Settings
 * Add a permission to manage the UI settings
 */
class Migration_Keyboard_shortcut_permissions extends Migration
{
	/**
	 * @var string The name of the permissions table
	 */
	private $table_name = 'permissions';

	/**
	 * @var string The name of the role/permissions ref table
	 */
	private $ref_table = 'role_permissions';

	/**
	 * @var string The name of the settings table
	 */
	private $settings_table = 'settings';

	/**
	 * @var array The permission to add
	 */
	private $data = array(
		'name'        => 'Bonfire.UI.Manage' ,
		'description' => 'Manage the Bonfire UI settings'
	);

	/**
	 * @var array The data to be added to the settings table
	 */
	private $settings_data = array(
		'name' => 'ui.shortcut_keys',
		'module' => 'core',
		'value' => '',
	);

	/**
	 * @var array The data to be added as the value in the settings data
	 */
	private $keys = array(
		'form_save' => 'ctrl+s/⌘+s',
		'goto_content' => 'alt+c',
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
		// insert the new permission
		$this->db->insert($this->table_name, $this->data);

		// add the permission to the administrator role
		$ref_data = array(
			'role_id' => $this->admin_role_id,
			'permission_id' => $this->db->insert_id(),
		);
		$this->db->insert($this->ref_table, $ref_data);

		// add the keys
		$this->settings_data['value'] = serialize($this->keys);
		$this->db->insert($this->settings_table, $this->settings_data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$query = $this->db->select('permission_id')
			->where('name', $this->data['name'])
			->get($this->table_name);

		$permission_ids = array();
		foreach ($query->result_array() as $row)
		{
			$permission_ids[] = $row['permission_id'];
		}

		// remove the permission from the roles
		if ( ! empty($permission_ids))
		{
			$this->db->where_in('permission_id', $permission_ids)
				->delete($this->ref_table);
		}

		//delete the permission
		$this->db->where('name', $this->data['name'])
			->delete($this->table_name);

		// remove the keys
		$this->db->where('name', $this->settings_data['name'])
			->delete($this->settings_table);
	}
}