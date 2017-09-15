<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add timezone to the users table
 */
class Migration_User_timezones extends Migration
{
	/**
	 * @var string The name of the table
	 */
	private $table_name = 'users';

	/**
	 * @var array The new field to add to the table
	 */
	private $field = array(
		'timezone' => array(
			'type'			=> 'char',
			'constraint'	=> 4,
			'default'		=> 'UM6',
			'null'			=> false,
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
		$this->dbforge->add_column($this->table_name, $this->field);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		foreach ($this->field as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->table_name, $column_name);
		}
	}
}