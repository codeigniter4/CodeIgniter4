<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Updates Database Session table for CodeIgniter 2.1
 * see {@link http://codeigniter.com/user_guide/libraries/sessions.html}
 */
class Migration_Update_session_table extends Migration
{
	/**
	 * @var string The name of the table to be modified
	 */
	private $table_name = 'sessions';

	/**
	 * @var array Definition of fields to be modified
	 */
	private $fields = array(
		'user_agent' => array(
			'type' => 'VARCHAR',
			'constraint' => 120,
			'null' => false,
		),
	);

	/**
	 * @var int Old value for user_agent's constraint
	 */
	private $old_constraint = 50;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		// change the length of the 'user_agent' column in the sessions table
		$this->dbforge->modify_column($this->table_name, $this->fields);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$this->fields['user_agent']['constraint'] = $this->old_constraint;

		$this->dbforge->modify_column($this->table_name, $this->fields);
	}
}