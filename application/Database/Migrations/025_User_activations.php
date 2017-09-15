<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add User activation fields to the database
 */
class Migration_User_activations extends Migration
{
	/**
	 * @var string The name of the users table
	 */
	private $table_name = 'users';

	/**
	 * @var string The name of the settings table
	 */
	private $settings_table = 'settings';

	/**
	 * @var array Fields to add to the users table
	 */
	private $fields = array(
		'active' => array(
			'type'			=> 'tinyint',
			'constraint'	=> 1,
			'default'		=> '0',
			'null'			=> false,
		),
		'activate_hash' => array(
			'type'			=> 'VARCHAR',
			'constraint'	=> 40,
			'default'		=> '',
			'null'			=> false,
		),
	);

	/**
	 * @var array Data used to update the Users table
	 */
	private $data = array(
		'active' => 1,
	);

	/**
	 * @var array Data to insert into the settings table
	 */
	private $settings_data = array(
		'name' => 'auth.user_activation_method',
		'module' => 'core',
		'value' => '0',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// add fields to the users table
        $this->dbforge->add_column($this->table_name, $this->fields);

		// set all of the users active
		$this->db->update($this->table_name, $this->data);

		// insert the auth.user_activation_method setting into the settings table
		$this->db->insert($this->settings_table, $this->settings_data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// remove the new fields from the users table
		foreach ($this->fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->table_name, $column_name);
		}

		// delete the new setting from the settings table
		$this->db->where('name', $this->settings_data['name'])
			->delete($this->settings_table);
	}
}