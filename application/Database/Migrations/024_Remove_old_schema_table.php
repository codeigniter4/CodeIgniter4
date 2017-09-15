<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Remove schema_version_old table left in place by
 * 012_Migration_schema_change.php
 */
class Migration_Remove_old_schema_table extends Migration
{
    /**
     * @var string The name of the table
     */
    private $table_name = 'schema_version_old';

    /**
     * @var array Fields used to rebuild the table in down()
     */
    private $fields = array(
        'version' => array(
            'type' => 'int',
            'constraint' => 4,
            'default' => 0,
            'null' => false,
        ),
        'app_version' => array(
            'type' => 'int',
            'constraint' => 4,
            'default' => 0,
            'null' => false,
        ),
    );

    /**
     * @var array Default data to insert into the table in down()
     */
    private $data = array(
        'version' => 11,
        'app_version' => 0,
    );

    /****************************************************************
     * Migration methods
     */
    /**
     * Install this migration
     */
    public function up()
    {
        if ($this->db->table_exists($this->table_name)) {
            $this->dbforge->drop_table($this->table_name);
        }
    }

    /**
     * Uninstall this migration
     */
    public function down()
    {
        $this->dbforge->add_field($this->fields);
        $this->dbforge->create_table($this->table_name);

        $this->db->insert($this->table_name, $this->data);
    }
}
