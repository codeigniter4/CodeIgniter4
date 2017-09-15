<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Add offline reason to settings.
 */
class Migration_Add_offline_reason extends Migration
{
    /** @var string The name of the settings table. */
    private $tableName = 'settings';

    /** @var array The settings to migrate. */
    private $data = array(
        array(
            'name'   => 'site.offline_reason',
            'module' => 'core',
            'value'  => '',
        ),
    );

    /**
     * Add the settings to the database.
     *
     * @return void
     */
    public function up()
    {
        if ($this->db->table_exists($this->tableName)) {
            $this->db->insert_batch($this->tableName, $this->data);
        }
    }

    /**
     * Remove the settings from the database.
     *
     * @return void
     */
    public function down()
    {
        if ($this->db->table_exists($this->tableName)) {
            $names = array();
            $module = '';
            foreach ($this->data as $setting) {
                $names[] = $setting['name'];
                $module = $setting['module'];
            }

            $this->db->where('module', $module)
                     ->where_in('name', $names)
                     ->delete($this->tableName);
        }
    }
}
