<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Set the size of common field types to be consistent between tables.
 *
 * Changing all bonfire tables to use the same types and constraints when referring
 * to user IDs, email addresses, IP addresses, and module names.
 *
 * - The user_id fields in other tables (activities, user_cookies, user_meta) should
 *   match the definition of users.id. This does not address the inability of the
 *   primary keys on these tables to keep up with the users table.
 *
 * - Email address fields should be able to store the largest valid email address
 *   (254 characters), and the login_attempts table should be able to store email
 *   addresses in the login field, since the system can use them in place of user
 *   names.
 *
 * - IP address fields should be able to store a value from CI's sessions table.
 *
 * - Module names should be consistent across tables.
 *
 * - Timezones in CI2 can be up to 6 characters, but in CI3 and PHP they can be
 *   up to 32 characters ('America/Argentina/ComodRivadavia', 40 adds a little room
 *   for future growth, or in case I missed a longer one). Changing the type from
 *   char to varchar potentially reduces the storage impact of this change on most
 *   databases, especially when continuing to use CI2 timezones.
 *
 * Notes:
 *  1: It is left to the user to ensure that activities, user_cookies, and user_meta
 *     do not contain user_id values which are out of range (negative values) before
 *     performing this migration.
 *
 *  2: Some databases will complain loudly (or even throw errors and refuse to
 *     continue) when reducing the size of fields, so uninstalling this migration
 *     may be difficult.
 *
 *  3: On some databases the varchar constraint is the number of bytes rather than
 *     characters, so multi-byte character sets like UTF-8 could exceed the constraint
 *     before exceeding the maximum valid length of the data being stored in that
 *     field.
 *
 *  4: This is not intended to fix cross-database compatibility issues. This is
 *     simply intended to improve the consistency of the database fields within
 *     Bonfire. There is still more work to be done in both consistency and compatibility.
 */
class Migration_Common_Field_Types extends Migration
{
    /**
     * @var array Table names/field data for this version.
     *
     * The keys on the first level are the table names. The values are CI field
     * arrays containing the definitions of the fields for that table.
     */
    private $upData = array(
        'activities' => array(
            'user_id' => array(
                'type'       => 'bigint',
                'constraint' => '20',
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ),
        ),
        'email_queue' => array(
            'to_email' => array(
                'type'       => 'varchar',
                'constraint' => '254',
                'null'       => false,
            ),
        ),
        'login_attempts' => array(
            'ip_address' => array(
                'type'       => 'varchar',
                'constraint' => '45',
                'null'       => false,
            ),
            'login' => array(
                'type'       => 'varchar',
                'constraint' => '255',
                'null'       => false,
            ),
        ),
        'settings' => array(
            'module' => array(
                'type'       => 'varchar',
                'constraint' => '255',
                'null'       => false,
            ),
        ),
        'user_cookies' => array(
            'user_id' => array(
                'type'       => 'bigint',
                'constraint' => '20',
                'unsigned'   => true,
                'null'       => false,
            ),
        ),
        'user_meta' => array(
            'user_id'   => array(
                'type'       => 'bigint',
                'constraint' => '20',
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ),
        ),
        'users' => array(
            'email' => array(
                'type'       => 'varchar',
                'constraint' => '254',
                'null'       => false,
            ),
            'last_ip' => array(
                'type'       => 'varchar',
                'constraint' => '45',
                'default'    => '',
                'null'       => false,
            ),
            'timezone' => array(
                'type'       => 'varchar',
                'constraint' => '40',
                'default'    => 'UM6',
                'null'       => false,
            ),
        ),
    );

    /** @var array Table names/field data for the previous version. */
    private $downData = array(
        'activities' => array(
            'user_id' => array(
                'type'       => 'bigint',
                'constraint' => 20,
                'unsigned'   => false,
                'default'    => 0,
                'null'       => false,
            ),
        ),
        'email_queue' => array(
            'to_email' => array(
                'type'       => 'varchar',
                'constraint' => '128',
                'null'       => false,
            ),
        ),
        'login_attempts' => array(
            'ip_address' => array(
                'type'       => 'varchar',
                'constraint' => '40',
                'null'       => false,
            ),
            'login' => array(
                'type'       => 'varchar',
                'constraint' => '50',
                'null'       => false,
            ),
        ),
        'settings' => array(
            'module' => array(
                'type'       => 'varchar',
                'constraint' => '50',
                'null'       => false,
            ),
        ),
        'user_cookies' => array(
            'user_id' => array(
                'type'       => 'bigint',
                'constraint' => '20',
                'unsigned'   => false,
                'null'       => false,
            ),
        ),
        'user_meta' => array(
            'user_id'   => array(
                'type'       => 'int',
                'constraint' => '20',
                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ),
        ),
        'users' => array(
            'email' => array(
                'type'       => 'varchar',
                'constraint' => '120',
                'null'       => false,
            ),
            'last_ip' => array(
                'type'       => 'varchar',
                'constraint' => '40',
                'default'    => '',
                'null'       => false,
            ),
            'timezone' => array(
                'type'       => 'char',
                'constraint' => '4',
                'default'    => 'UM6',
                'null'       => false,
            ),
        ),
    );

    /**
     * Migrate to this version.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->upData as $tableName => $fields) {
            $this->dbforge->modify_column($tableName, $fields);
        }
    }

    /**
     * Migrate to the previous version.
     *
     * @return void
     */
    public function down()
    {
        foreach ($this->downData as $tableName => $fields) {
            $this->dbforge->modify_column($tableName, $fields);
        }
    }
}
