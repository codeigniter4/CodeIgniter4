<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Remove the settings formerly used by the updates module from the database
 */
class Migration_Remove_updates_settings extends Migration
{
    /**
     * @var string The name of the settings table
     */
    private $tableName = 'settings';

    /**
     * @var array The settings to migrate
     */
    private $data = array(
		array(
			'name'   => 'updates.do_check',
			'module' => 'core',
			'value'  => '0',
		),
		array(
			'name'   => 'updates.bleeding_edge',
			'module' => 'core',
			'value'  => '0',
		),
    );

    /**
     * Install this version
     *
     * @return void
     */
    public function up()
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

    /**
     * Uninstall this version
     *
     * @return void
     */
    public function down()
    {
        if ($this->db->table_exists($this->tableName)) {
            $this->db->insert_batch($this->tableName, $this->data);
        }
    }
}