<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add the show password labels option to the settings table
 */
class Migration_Password_ui_labels_option extends Migration
{
	/**
	 * @var string The name of the settings table
	 */
	private $table_name = 'settings';

	/**
	 * @var array The data to insert
	 */
	private $data = array(
		'name' => 'auth.password_show_labels',
		'module' => 'core',
		'value' => 0,
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->db->insert($this->table_name, $this->data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// remove the setting
		$this->db->where('name', $this->data['name'])
			->delete($this->table_name);
	}
}