<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add password strength options to the settings table
 */
class Migration_Password_strength_settings extends Migration
{
	/**
	 * @var string The name of the settings table
	 */
	private $table_name = 'settings';

	/**
	 * @var array The data to insert into the settings table
	 */
	private $data = array(
		array(
			'name'		=> 'auth.password_min_length',
			'module'	=> 'core',
			'value'		=> '8',
		),
		array(
			'name'		=> 'auth.password_force_numbers',
			'module'	=> 'core',
			'value'		=> '0',
		),
		array(
			'name'		=> 'auth.password_force_symbols',
			'module'	=> 'core',
			'value'		=> '0',
		),
		array(
			'name'		=> 'auth.password_force_mixed_case',
			'module'	=> 'core',
			'value'		=> '0',
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
		$this->db->insert_batch($this->table_name, $this->data);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$settings = array();
		foreach ($this->data as $setting)
		{
			$settings[] = $setting['name'];
		}

		if ( ! empty($settings))
		{
			// remove the keys
			$this->db->where_in('name', $settings)
				->delete($this->table_name);
		}
	}
}