<?php if ( ! defined('BASEPATH')) { exit('No direct script access allowed'); }

/**
 * Remove the password_iterations field from the users table,
 * since this is stored as part of the password hash.
 */
class Migration_Remove_user_password_iterations extends Migration
{
    /**
     * @var String The name of the table to modify
     */
    private $tableName = 'users';

    /**
     * @var Array Field definitions for the migration
     */
    private $fields = array(
        'password_iterations' => array(
            'type'          => 'int',
            'constraint'    => 4,
            'null'          => false,
        ),
    );

    /**
     * Migrate to this version
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->fields as $columnName => $columnDef) {
            $this->dbforge->drop_column($this->tableName, $columnName);
        }
    }

    /**
     * Migrate to the previous version
     *
     * @return void
     */
    public function down()
    {
        $this->dbforge->add_column($this->tableName, $this->fields);
    }
}
/* /bonfire/migrations/038_Remove_user_password_iterations.php */