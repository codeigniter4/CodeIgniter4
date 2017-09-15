<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Modify the size of the type field in the schema_version table
 */
class Migration_Modify_schema_version_type extends Migration
{
	/**
	 * @var string The name of the table
	 */
	private $table_name = 'schema_version';

	/**
	 * @var array The field to modify
	 */
	private $field = array(
		'type' => array(
			'type' => 'VARCHAR',
			'constraint' => 40,
			'null' => false,
		),
	);

	/**
	 * @var int Old value for the type constraint
	 */
	private $old_constraint = 20;

	/****************************************************************
	 * Migration methods
	 */
	/**
	 * Install this migration
	 */
	public function up()
	{
		$this->dbforge->modify_column($this->table_name, $this->field);
	}

	/**
	 * Uninstall this migration
	 */
	public function down()
	{
		$this->field['type']['constraint'] = $this->old_constraint;

		$this->dbforge->modify_column($this->table_name, $this->field);
	}
}