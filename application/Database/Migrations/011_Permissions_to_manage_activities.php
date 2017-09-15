<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Add permissions to manage Activities
 * Add soft deletes support to Activities
 */
class Migration_Permissions_to_manage_activities extends Migration
{
    /****************************************************************
     * Table names
     */
    /**
     * @var string Name of the Activities table
     */
    private $activities_table = 'activities';

    /**
     * @var string Name of the Permissions table
     */
    private $permissions_table = 'permissions';

    /**
     * @var string Name of the Role_permissions table
     */
    private $role_permissions_table = 'role_permissions';

    /****************************************************************
     * Field definitions
     */
    /**
     * @var array Fields to be added to the Activities table
     */
    private $activities_fields = array(
        'deleted' => array(
            'type' => 'TINYINT',
            'constraint' => 12,
            'default' => 0,
            'null' => false,
        ),
    );

    /****************************************************************
     * Data to Insert
     */
    /**
     * @var array Permissions data to insert
     */
    private $permissions_data = array(
        array(
           'name' => 'Bonfire.Activities.Manage',
           'description' => 'Allow users to access the Activities Reports',
        ),
        array(
           'name' => 'Activities.Own.View',
           'description' => 'To view the users own activity logs',
        ),
        array(
           'name' => 'Activities.Own.Delete',
           'description' => 'To delete the users own activity logs',
        ),
        array(
           'name' => 'Activities.User.View',
           'description' => 'To view the user activity logs',
        ),
        array(
           'name' => 'Activities.User.Delete',
           'description' => 'To delete the user activity logs, except own',
        ),
        array(
           'name' => 'Activities.Module.View',
           'description' => 'To view the module activity logs',
        ),
        array(
           'name' => 'Activities.Module.Delete',
           'description' => 'To delete the module activity logs',
        ),
        array(
           'name' => 'Activities.Date.View',
           'description' => 'To view the users own activity logs',
        ),
        array(
           'name' => 'Activities.Date.Delete' ,
           'description' => 'To delete the dated activity logs',
        ),
    );

    /**
     * @var int The role_id of the Administrator role
     */
    private $admin_role_id = 1;

    /****************************************************************
     * Migration methods
     */
    /**
     * Install this migration
     */
    public function up()
    {
        // Add the soft deletes column, made it (12) to accomodate time stamp.
        $this->dbforge->add_column($this->activities_table, $this->activities_fields);

        $this->db->insert_batch($this->permissions_table, $this->permissions_data);

        // Give current role (or administrators if fresh install) full right to manage permissions
        $assign_role = array($this->admin_role_id);
        if (class_exists('CI_Session', false)) {
            if ($this->session->userdata('role_id')) {
                $assign_role[] = $this->session->userdata('role_id');
            }
        }

        $permission_names = array();
        foreach ($this->permissions_data as $permission) {
            $permission_names[] = $permission['name'];
        }

        $permissions = $this->db->select('permission_id')
                                ->where_in('name', $permission_names)
                                ->get($this->permissions_table)
                                ->result();

        if (! empty($permissions) && is_array($permissions)) {
            $permissions_data = array();
            foreach ($permissions as $perm) {
                foreach ($assign_role as $roleId) {
                    $permissions_data[] = array(
                        'role_id'       => $roleId,
                        'permission_id' => $perm->permission_id,
                    );
                }
            }

            if (! empty($permissions_data)) {
                $this->db->insert_batch($this->role_permissions_table, $permissions_data);
            }
        }
    }

    /**
     * Uninstall this migration
     */
    public function down()
    {
        $permission_names = array();
        foreach ($this->permissions_data as $permission) {
            $permission_names[] = $permission['name'];
        }

        $query = $this->db->select('permission_id')
                          ->where_in('name', $permission_names)
                          ->get($this->permissions_table)
                          ->result();

        // Delete these permissions from the role_permissions table.
        $permission_ids = array();
        foreach ($query->result_array() as $row) {
            $permission_ids[] = $row['permission_id'];
        }

        if (! empty($permission_ids)) {
            $this->db->where_in('permission_id', $permission_ids)
                     ->delete($this->role_permissions_table);
        }

        // Delete the permissions.
        $this->db->where_in('name', $permission_names)
                 ->delete($this->permissions_table);

        // Drop the added deleted column.
        foreach ($this->activities_fields as $column_name => $column_def) {
            $this->dbforge->drop_column($this->activities_table, $column_name);
        }
    }
}
