<?php 

namespace App\Database\Migrations;


/**
 * Add CI3 sessions table.
 *
 * For additional instructions/information, see
 * http://www.codeigniter.com/userguide3/libraries/sessions.html#database-driver
 *
 * The created table does not include an index/key on the timestamp field. The link
 * includes the command to create the index on PostgreSQL, but it is included within
 * the table definition in MySQL. To create the index in MySQL, after running the
 * migration below, execute the following command on your MySQL server:
 *
 * alter table ci3_sessions add index ci_sessions_timestamp (timestamp)
 */
class Migration_Add_ci3_sessions extends \CodeIgniter\Database\Migration
{
    private $sessionsTable = array(
        'name' => 'ci3_sessions', // the name of the table
        'key' => 'id',            // the primary key
        'fields' => array(        // field definitions
            'id' => array(
                'type'       => 'varchar',
                'constraint' => 40,
                'null'       => false,
            ),
            'ip_address' => array(
                'type'       => 'varchar',
                'constraint' => 45,
                'null'       => false,
            ),
            'timestamp' => array(
                'type'       => 'int',
                'constraint' => 10,
//                'unsigned'   => true,
                'default'    => 0,
                'null'       => false,
            ),
            // For PostgreSQL, use this timestamp definition, instead.
            // 'timestamp' => array(
            //     'type'    => 'bigint',
            //     'default' => 0,
            //     'null'    => false,
            // ),
            'data' => array(
                'type' => 'BYTEA',
                'null' => false,
            ),
        ),
    );

    /**
     * Add the table to the database.
     *
     * @return void
     */
    public function up()
    {
        $this->forge->addField($this->sessionsTable['fields']);
        $this->forge->addKey($this->sessionsTable['key'], true);

        // Create the table if it does not already exist.
        $this->forge->createTable($this->sessionsTable['name'], true);
    }

    /**
     * Remove the table from the database.
     *
     * @return void
     */
    public function down()
    {
        // Since CI2 does not have the second parameter for dropTable(), call
        // tableExists() first to make this work in CI2 and CI3.
        if ($this->db->tableExists($this->sessionsTable['name'])) {
            $this->forge->dropTable($this->sessionsTable['name']);
        }
    }
}
