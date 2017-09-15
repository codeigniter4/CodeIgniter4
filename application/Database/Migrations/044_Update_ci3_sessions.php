<?php defined('BASEPATH') || exit('No direct script access allowed');

/**
 * Update CI3 sessions table.
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
class Migration_Update_ci3_sessions extends Migration
{
    /**
     * Add the table to the database.
     *
     * @return void
     */
    public function up()
    {
		$fields = array(
			'id' => array(
				'type'		  => 'varchar',
				'constraint'	=> 128,
				'null'		  => false,
			)
		);

		$this->dbforge->modify_column('ci3_sessions', $fields);

        // For Postgres, use this instead...
        // $this->db->query("ALTER TABLE ci3_sessions ALTER COLUMN id SET DATA TYPE varchar(128);");
    }

    /**
     * Remove the table from the database.
     *
     * @return void
     */
    public function down()
    {

    }
}
