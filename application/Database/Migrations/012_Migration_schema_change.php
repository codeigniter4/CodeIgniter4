<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * This Migration file should not be used in an install situation
 * because the migration table schema will already be setup correctly
 * and the old table will not exist
 */

/**
 * Update the Migration Schema (schema_version table)
 */
class Migration_Migration_schema_change extends Migration
{
	/****************************************************************
	 * Table names
	 */
	/**
	 * @var string The name of the Schema_version table
	 */
	private $table_name = 'schema_version';

	/**
	 * @var string The name of the backup Schema_version table
	 */
	private $backup_table = 'schema_version_old';

	/****************************************************************
	 * Field definitions
	 */
	/**
	 * @var array Fields for the new schema_version table
	 */
	private $fields = array(
		'type' => array(
			'type' => 'VARCHAR',
			'constraint' => 20,
			'null' => false,
		),
		'version' => array(
			'type' => 'INT',
			'constraint' => '4',
			'default' => 0,
			'null' => false,
		),
	);

	/**
	 * @var string Name of new key field to be added to the table
	 */
	private $new_key = 'type';

    //--------------------------------------------------------------------------
	// Migration methods
    //--------------------------------------------------------------------------

	/**
	 * Install this migration
	 */
	public function up()
	{
		// Determine whether the table is in the old format by checking for the
        // existence of the 'type' column. To ensure the db class is checking
        // the current state of the table, query the table before calling
        // $this->db->field_exists().

        $versionQuery = $this->db->get($this->table_name);

		if ( ! $this->db->field_exists($this->new_key, $this->table_name)) {
            // Get the existing data from the table.
            $versionArray = $versionQuery->row_array();

			// Backup the table.
			$this->dbforge->rename_table($this->table_name, $this->backup_table);

			// Modify the table to conform to the schema for the new version.
			$this->dbforge->add_field($this->fields);
			$this->dbforge->add_key($this->new_key, true);
			$this->dbforge->create_table($this->table_name);

			// Add records for each of the old permissions.
			$permissionRecords = array();
			foreach ($versionArray as $type => $versionNum) {
				if ($type == 'version') {
					$typeField = 'core';
					$versionNum++;
				} else {
					$typeField = str_replace('version', '', $type);
				}

				$permissionRecords[] = array(
					'type'    => $typeField,
					'version' => $versionNum,
				);
			}

			if ( ! empty($permissionRecords)) {
				$this->db->insert_batch($this->table_name, $permissionRecords);
			}
		}
	}

	/**
	 * Install this migration
	 */
	public function down()
	{
		// Determine whether the table is in the new format.
		if ($this->db->table_exists($this->backup_table)) {
			// Drop the table and rename the backup table.
			$this->dbforge->drop_table($this->table_name);
			$this->dbforge->rename_table($this->backup_table, $this->table_name);
		}
	}
}