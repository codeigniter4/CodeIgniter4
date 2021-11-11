<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Support\Database\Migrations;

use CodeIgniter\Database\Migration;

class Migration_Create_test_tables extends Migration
{
    public function up()
    {
        // User Table
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'country'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ])->addKey('id', true)->createTable('user', true);

        // Job Table
        $this->forge->addField([
            'id'          => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'        => ['type' => 'VARCHAR', 'constraint' => 40],
            'description' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
            'created_at'  => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
            'updated_at'  => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
            'deleted_at'  => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
        ])->addKey('id', true)->createTable('job', true);

        // Misc Table
        $this->forge->addField([
            'id'    => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'key'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'value' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
        ])->addKey('id', true)->createTable('misc', true);

        // Database Type test table
        // missing types :
        // TINYINT,MEDIUMINT,BIT,YEAR,BINARY , VARBINARY, TINYTEXT,LONGTEXT,YEAR,JSON,Spatial data types
        // id must be interger else SQLite3 error on not null for autoinc field
        $data_type_fields = [
            'id'              => ['type' => 'INTEGER', 'constraint' => 20, 'auto_increment' => true],
            'type_varchar'    => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'type_char'       => ['type' => 'CHAR', 'constraint' => 10, 'null' => true],
            'type_text'       => ['type' => 'TEXT', 'null' => true],
            'type_smallint'   => ['type' => 'SMALLINT', 'null' => true],
            'type_integer'    => ['type' => 'INTEGER', 'null' => true],
            'type_float'      => ['type' => 'FLOAT', 'null' => true],
            'type_numeric'    => ['type' => 'NUMERIC', 'constraint' => '18,2', 'null' => true],
            'type_date'       => ['type' => 'DATE', 'null' => true],
            'type_time'       => ['type' => 'TIME', 'null' => true],
            'type_datetime'   => ['type' => 'DATETIME', 'null' => true],
            'type_timestamp'  => ['type' => 'TIMESTAMP', 'null' => true],
            'type_bigint'     => ['type' => 'BIGINT', 'null' => true],
            'type_real'       => ['type' => 'REAL', 'null' => true],
            'type_enum'       => ['type' => 'ENUM', 'constraint' => ['appel', 'pears'], 'null' => true],
            'type_set'        => ['type' => 'SET', 'constraint' => ['one', 'two'], 'null' => true],
            'type_mediumtext' => ['type' => 'MEDIUMTEXT', 'null' => true],
            'type_double'     => ['type' => 'DOUBLE', 'null' => true],
            'type_decimal'    => ['type' => 'DECIMAL', 'constraint' => '18,4', 'null' => true],
            'type_blob'       => ['type' => 'BLOB', 'null' => true],
            'type_boolean'    => ['type' => 'BOOLEAN', 'null' => true],
        ];

        if ($this->db->DBDriver === 'Postgre') {
            unset(
                $data_type_fields['type_real'],
                $data_type_fields['type_decimal']
            );
        }

        if ($this->db->DBDriver === 'SQLSRV') {
            unset($data_type_fields['type_timestamp']);
        }

        if ($this->db->DBDriver === 'Postgre' || $this->db->DBDriver === 'SQLSRV') {
            unset(
                $data_type_fields['type_enum'],
                $data_type_fields['type_set'],
                $data_type_fields['type_mediumtext'],
                $data_type_fields['type_double'],
                $data_type_fields['type_blob']
            );
        }

        $this->forge->addField($data_type_fields)->addKey('id', true)->createTable('type_test', true);

        // Empty Table
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at' => ['type' => 'DATE', 'null' => true],
            'updated_at' => ['type' => 'DATE', 'null' => true],
        ])->addKey('id', true)->createTable('empty', true);

        // Secondary Table
        $this->forge->addField([
            'id'    => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'key'   => ['type' => 'VARCHAR', 'constraint' => 40],
            'value' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
        ])->addKey('id', true)->createTable('secondary', true);

        // Stringify Primary key Table
        $this->forge->addField([
            'id'    => ['type' => 'VARCHAR', 'constraint' => 3],
            'value' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
        ])->addKey('id', true)->createTable('stringifypkey', true);

        // Table without auto increment field
        $this->forge->addField([
            'key'   => ['type' => 'VARCHAR', 'constraint' => 40, 'unique' => true],
            'value' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
        ])->addKey('key', true)->createTable('without_auto_increment', true);

        // IP Table
        $this->forge->addField([
            'ip'  => ['type' => 'VARCHAR', 'constraint' => 100],
            'ip2' => ['type' => 'VARCHAR', 'constraint' => 100],
        ])->createTable('ip_table', true);

        // Database session table
        if ($this->db->DBDriver === 'MySQLi') {
            $this->forge->addField([
                'id'         => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
                'ip_address' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
                'timestamp timestamp DEFAULT CURRENT_TIMESTAMP NOT NULL',
                'data' => ['type' => 'BLOB', 'null' => false],
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('ci_sessions', true);
        }

        if ($this->db->DBDriver === 'Postgre') {
            $this->forge->addField([
                'id' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
                'ip_address inet NOT NULL',
                'timestamp timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL',
                "data bytea DEFAULT '' NOT NULL",
            ]);
            $this->forge->addKey('id', true);
            $this->forge->createTable('ci_sessions', true);
        }
    }

    public function down()
    {
        $this->forge->dropTable('user', true);
        $this->forge->dropTable('job', true);
        $this->forge->dropTable('misc', true);
        $this->forge->dropTable('type_test', true);
        $this->forge->dropTable('empty', true);
        $this->forge->dropTable('secondary', true);
        $this->forge->dropTable('stringifypkey', true);
        $this->forge->dropTable('without_auto_increment', true);
        $this->forge->dropTable('ip_table', true);

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre'], true)) {
            $this->forge->dropTable('ci_sessions', true);
        }
    }
}
