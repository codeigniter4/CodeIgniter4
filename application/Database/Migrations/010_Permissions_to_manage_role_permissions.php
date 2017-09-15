<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Add permissions to manage each role's permissions
 */
class Migration_Permissions_to_manage_role_permissions extends Migration
{
    /****************************************************************
     * Table names
     */
    /**
     * @var string Name of the Permissions table
     */
    private $permissions_table = 'permissions';

    /**
     * @var string Name of the Role_Permissions table
     */
    private $role_permissions_table = 'role_permissions';

    /**
     * @var string Name of the Roles table
     */
    private $roles_table = 'roles';

    /****************************************************************
     * Field definitions
     */
    /**
     * @var array Field to modify
     */
    private $permissions_fields = array(
        'name' => array(
            'type' => 'VARCHAR',
            'constraint' => 255,
            'null' => false,
        ),
    );

    /**
     * @var array Field definition to restore
     */
    private $permissions_fields_down = array(
        'name' => array(
            'type' => 'VARCHAR',
            'constraint' => 30,
            'null' => false,
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
        // Permissions table's name field is too short, bump it up to 255.
        $this->dbforge->modify_column($this->permissions_table, $this->permissions_fields);

        $roles = $this->db->select('role_name')
                          ->get($this->roles_table)
                          ->result();

        $role_permissions_data = array();
        if (! empty($roles) && is_array($roles)) {
            // Give the new permissions to the current role
            // (or administrators if fresh install).
            $assign_role = array($this->admin_role_id);
            if (class_exists('CI_Session', false)) {
                if ($this->session->userdata('role_id')) {
                    $assign_role[] = $this->session->userdata('role_id');
                }
            }

            foreach ($roles as $role) {
                // Add the permission
                $permissions_data = array(
                    'name' => 'Permissions.' . ucwords($role->role_name) . '.Manage',
                    'description' => 'To manage the access control permissions for the ' . ucwords($role->role_name) . ' role.',
                );
                $this->db->insert($this->permissions_table, $permissions_data);

                // Give roles full right to manage permissions.
                $permissionId = $this->db->insert_id();
                foreach ($assign_role as $roleId) {
                    $role_permissions_data[] = array(
                        'role_id' => $roleId,
                        'permission_id' => $permissionId,
                    );
                }
            }

            if (! empty($role_permissions_data)) {
                $this->db->insert_batch($this->role_permissions_table, $role_permissions_data);
            }
        }
    }

    /**
     * Uninstall this migration
     */
    public function down()
    {
        $roles = $this->role_model->find_all();
        if (! empty($roles) && is_array($roles)) {
            $permission_ids = array();
            $permission_names = array();
            foreach ($roles as $role) {
                // Delete any of these permissions from the role_permissions table.
                $query = $this->db->select('permission_id')
                                  ->where('name', "Permissions.{$role->role_name}.Manage")
                                  ->get($this->permissions_table);

                foreach ($query->result() as $row) {
                    $permission_id[] = $row->permission_id;
                }

                // Delete the role.
                $permission_names[] = "Permissions.{$role->role_name}.Manage";
            }

            if (! empty($permission_ids)) {
                $this->db->where_in('permission_id', $permission_ids)
                         ->delete($this->role_permissions_table);
            }

            if (! empty($permission_names)) {
                $this->db->where_in('name', $permission_names)
                         ->delete($this->permissions_table);
            }
        }

        // Restore the shorter table field size back to 30.
        $this->dbforge->modify_column($this->permissions_table, $this->permissions_fields_down);
    }
}
