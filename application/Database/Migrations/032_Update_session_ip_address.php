<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updates Database Session table for CodeIgniter 2.1.1
 * see {@link http://codeigniter.com/user_guide/libraries/sessions.html}
 */
class Migration_Update_session_ip_address extends Migration
{
	/**
	 * @var string The name of the table to modify
	 */
	private $table_name = 'sessions';

	/**
	 * @var array Updated field definition
	 */
	private $fields = array(
		'ip_address' => array(
			'type' => 'VARCHAR',
			'constraint' => 45,
			'null' => FALSE,
			'default' => '0',
		),
	);

	/**
	 * @var int The old constraint value for the ip_address field
	 */
	private $old_constraint = 16;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->dbforge->modify_column($this->table_name, $this->fields);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$this->fields['ip_address']['constraint'] = $this->old_constraint;

		$this->dbforge->modify_column($this->table_name, $this->fields);
	}
}