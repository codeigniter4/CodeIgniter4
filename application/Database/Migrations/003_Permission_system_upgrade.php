<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Upgrade Permission system by:
 *  Making the name of the Permission a value in the table rather than a column
 *  Adding a Role/Permissions ref table
 */
class Migration_Permission_system_upgrade extends Migration
{
    /****************************************************************
     * Table names
     */
    private $permissions_table = 'permissions';
    private $permissions_old_table = 'permissions_old';
    private $role_permissions_table = 'role_permissions';

    /****************************************************************
     * Field definitions
     */
    /**
     * @var array New fields to add to the Permissions table
     */
    private $permissions_fields = array(
        'Site_Signin_Offline' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'null' => false,
        ),
    );

    /**
     * @var array Fields to modify in the Permissions table
     */
    private $permissions_modify_fields = array(
        'Site_Statistics_View' => array(
            'name' => 'Site_Reports_View',
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'null' => false,
        ),
    );

    /**
     * @var array Fields to modify in the Permissions table during Uninstall
     */
    private $permissions_modify_fields_down = array(
        'Site_Reports_View' => array(
            'name' => 'Site_Statistics_View',
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'null' => false,
        ),
    );

    /**
     * @var array Fields to drop from the permissions table
     */
    private $permissions_drop_fields = array(
        'Site_Appearance_View' => array(
            'type' => 'TINYINT',
            'constraint' => 1,
            'default' => 0,
            'null' => false,
        ),
    );

    /**
     * @var array Fields for the new Permissions table
     */
    private $permissions_new_fields = array(
        'permission_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'auto_increment' => true,
            'null' => false,
        ),
        'name' => array(
            'type' => 'VARCHAR',
            'constraint' => 30,
            'null' => false,
        ),
        'description' => array(
            'type' =>'VARCHAR',
            'constraint' => 100,
            'null' => false,
        ),
        'status' => array(
            'type' => "ENUM('active','inactive','deleted')",
            'default' => 'active',
            'null' => false,
        ),
    );

    /**
     * @var array Fields for the role_permissions table
     */
    private $role_permissions_fields = array(
        'role_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => false,
        ),
        'permission_id' => array(
            'type' => 'INT',
            'constraint' => 11,
            'null' => false,
        ),
    );

    /****************************************************************
     * Data for Insert/Update
     */
    /**
     * @var array Data to update the permissions table
     */
    private $permissions_data = array(
        'Site_Signin_Offline' => 1,
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
        /* Take care of a few preliminaries before updating */
        // Add new Site_Signin_Offline permission
        if (! $this->db->field_exists('Site_Signin_Offline', $this->permissions_table)) {
            $this->dbforge->add_column($this->permissions_table, $this->permissions_fields);

            $this->db->where('role_id', $this->admin_role_id)
                ->update($this->permissions_table, $this->permissions_data);
        }

        // Rename Site_Statistics_View to Site_Reports_View
        if ($this->db->field_exists('Site_Statistics_View', $this->permissions_table)) {
            $this->dbforge->modify_column($this->permissions_table, $this->permissions_modify_fields);
        }

        // Remove Site.Appearance.View
        if ($this->db->field_exists('Site_Appearance_View', $this->permissions_table)) {
            foreach ($this->permissions_drop_fields as $column_name => $column_def) {
                $this->dbforge->drop_column($this->permissions_table, $column_name);
            }
        }

        // Do the actual update.
        //
        // Get the current permissions assigned to each role.
        $permission_query = $this->db->get($this->permissions_table);

        // Get the field names in the current permissions table.
        $permissions_fields = $permission_query->list_fields();

        $old_permissions_array = array();
        foreach ($permission_query->result_array() as $row) {
            $old_permissions_array[$row['role_id']] = $row;
        }

        // Modify the permissions table.
        $this->dbforge->rename_table($this->permissions_table, $this->permissions_old_table);

        // Create the new permissions table.
        $this->dbforge->add_field($this->permissions_new_fields);
        $this->dbforge->add_key('permission_id', true);
        $this->dbforge->create_table($this->permissions_table);

        // add records for each of the old permissions
        $old_permissions_records = array();
        foreach ($permissions_fields as $field) {
            // If this is not the role_id or permission_id field, replace underscores
            // with '.' to generate the permission names.
            if ($field != 'role_id' && $field != 'permission_id') {
                $permission_name = str_replace('_', '.', $field);
                $old_permissions_records[] = array(
                    'name' => $permission_name,
                    'description' => '',
                );
            }
        }

        // Add permissions to access permissions settings.
        $old_permissions_records[] = array(
            'name'          => 'Permissions.Settings.View',
            'description'   => 'Allow access to view the Permissions menu unders Settings Context'
        );
        $old_permissions_records[] = array(
            'name'          => 'Permissions.Settings.Manage',
            'description'   => 'Allow access to manage the Permissions in the system',
        );
        $this->db->insert_batch($this->permissions_table, $old_permissions_records);

        // create the new role_permissions table
        $this->dbforge->add_field($this->role_permissions_fields);
        $this->dbforge->add_key('role_id', true);
        $this->dbforge->add_key('permission_id', true);
        $this->dbforge->create_table($this->role_permissions_table);

        // Add records to allow access to the permissions by the roles.
        //
        // Get the current list of permissions.
        $new_permission_query = $this->db->get($this->permissions_table);
        $role_permissions_records = array();

        // Loop through the current permissions.
        foreach ($new_permission_query->result_array() as $permission_rec) {
            $old_permission_name = str_replace('.', '_', $permission_rec['name']);

            // Loop through the old permissions.
            foreach ($old_permissions_array as $role_id => $role_permissions) {
                // Keep existing role permissions.
                if (isset($role_permissions[$old_permission_name])
                    && $role_permissions[$old_permission_name] == 1
                ) {
                    $role_permissions_records[] = array(
                        'role_id' => $role_id,
                        'permission_id' => $permission_rec['permission_id'],
                    );
                }
            }

            // Give the administrator access to the new "Permissions" permissions
            if ($permission_rec['name'] == 'Permissions.Settings.View'
                || $permission_rec['name'] == 'Permissions.Settings.Manage'
                || $permission_rec['name'] == 'Bonfire.Permissions.Manage'
            ) {
                $role_permissions_records[] = array(
                    'role_id' => $this->admin_role_id,
                    'permission_id' => $permission_rec['permission_id'],
                );
            }
        }

        $this->db->insert_batch($this->role_permissions_table, $role_permissions_records);
    }

    /**
     * Uninstall this migration
     */
    public function down()
    {
        // Drop the tables.
        $this->dbforge->drop_table($this->permissions_table);
        $this->dbforge->drop_table($this->role_permissions_table);

        // Rename the old permissions table.
        $this->dbforge->rename_table($this->permissions_old_table, $this->permissions_table);

        // Rename Site_Reports_View to Site_Statistics_View.
        $this->dbforge->modify_column($this->permissions_table, $this->permissions_modify_fields_down);
    }
}
