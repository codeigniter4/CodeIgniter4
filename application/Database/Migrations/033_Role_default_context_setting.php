<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Add a new column to the roles table to create the default context field.
 * Update existing roles with the default value as well.
 */
class Migration_Role_default_context_setting extends Migration
{
	/**
	 * @var string The name of the roles table
	 */
	private $table_name = 'roles';

	/**
	 * @var array The column to be added to the table
	 */
	private $fields = array(
		'default_context'	=> array(
			'type'			=> 'varchar',
			'constraint'	=> 255,
			'default'		=> 'content',
			'after'         => 'login_destination'
		),
	);

	/**
	 * @var array Data to update the table
	 */
	private $data = array(
		'default_context' => 'content',
	);

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
        $this->dbforge->add_column($this->table_name, $this->fields);

		$this->db->update($this->table_name, $this->data);
	}

	/**
	 * Install this migration
	 */
	public function down()
	{
		// remove the default_context column
		foreach ($this->fields as $column_name => $column_def)
		{
			$this->dbforge->drop_column($this->table_name, $column_name);
		}
    }
}