<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add the user's language to the users table
 * Add available languages to the settings table
 */
class Migration_User_language extends Migration
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
	 * @var array The field to add to the users table
	 */
	private $field = array(
		'language' => array(
			'type'			=> 'varchar',
			'constraint'	=> 20,
			'default'		=> 'english',
			'null'			=> false,
		),
	);

	/**
	 * @var array The field to add to the settings table
	 */
	private $settings_field = array(
		'name'		=> 'site.languages',
		'module'	=> 'core',
		'value'		=> '',
	);

	/**
	 * @var array The languages available for the site, will be
	 * 				serialized and inserted into the settings field
	 */
	private $languages = array(
		'english',
		'portuguese',
		'persian',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// Add the language field to the users table
		$this->dbforge->add_column($this->table_name, $this->field);

		// Add the site languages to the settings table
		$this->settings_field['value'] = serialize($this->languages);
		$this->db->insert($this->settings_table, $this->settings_field);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		// Drop the language field from the users table
		foreach ($this->field as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->table_name, $column_name);
		}

		$this->db->where('name', $this->settings_field['name'])->delete($this->settings_table);

	}
}