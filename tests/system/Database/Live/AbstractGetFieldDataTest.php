<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;

abstract class AbstractGetFieldDataTest extends CIUnitTestCase
{
    /**
     * @var BaseConnection
     */
    protected $db;

    protected Forge $forge;
    protected string $table = 'test1';

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        helper('array');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);

        $this->createForge();
    }

    /**
     * Make sure that $db and $forge are instantiated.
     */
    abstract protected function createForge(): void;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->forge->dropTable($this->table, true);
    }

    protected function createTableForDefault()
    {
        $this->forge->dropTable($this->table, true);

        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'auto_increment' => true,
            ],
            'text_not_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
            ],
            'text_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'null'       => true,
            ],
            'int_default_0' => [
                'type'    => 'INT',
                'default' => 0,
            ],
            'text_default_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => null,
            ],
            'text_default_text_null' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'null',
            ],
            'text_default_abc' => [
                'type'       => 'VARCHAR',
                'constraint' => 64,
                'default'    => 'abc',
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable($this->table);
    }

    protected function createTableForType()
    {
        $this->forge->dropTable($this->table, true);

        // missing types:
        //   TINYINT,MEDIUMINT,BIT,YEAR,BINARY,VARBINARY,TINYTEXT,LONGTEXT,
        //   JSON,Spatial data types
        // `id` must be INTEGER else SQLite3 error on not null for autoincrement field.
        $fields = [
            'id'           => ['type' => 'INTEGER', 'constraint' => 20, 'auto_increment' => true],
            'type_varchar' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'type_char'    => ['type' => 'CHAR', 'constraint' => 10, 'null' => true],
            // TEXT should not be used on SQLSRV. It is deprecated.
            'type_text'     => ['type' => 'TEXT', 'null' => true],
            'type_smallint' => ['type' => 'SMALLINT', 'null' => true],
            'type_integer'  => ['type' => 'INTEGER', 'null' => true],
            'type_float'    => ['type' => 'FLOAT', 'null' => true],
            'type_numeric'  => ['type' => 'NUMERIC', 'constraint' => '18,2', 'null' => true],
            'type_date'     => ['type' => 'DATE', 'null' => true],
            'type_time'     => ['type' => 'TIME', 'null' => true],
            // On SQLSRV `datetime2` is recommended.
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
                $fields['type_enum'],
                $fields['type_set'],
                $fields['type_mediumtext'],
                $fields['type_double'],
                $fields['type_blob']
            );
        }

        if ($this->db->DBDriver === 'SQLSRV') {
            unset(
                $fields['type_set'],
                $fields['type_mediumtext'],
                $fields['type_double'],
                $fields['type_blob']
            );
        }

        $this->forge->addField($fields);
        $this->forge->addKey('id', true);
        $this->forge->createTable($this->table);
    }

    abstract public function testGetFieldDataDefault(): void;

    protected function assertSameFieldData(array $expected, array $actual)
    {
        $expectedArray = json_decode(json_encode($expected), true);
        array_sort_by_multiple_keys($expectedArray, ['name' => SORT_ASC]);

        $fieldsArray = json_decode(json_encode($actual), true);
        array_sort_by_multiple_keys($fieldsArray, ['name' => SORT_ASC]);

        $this->assertSame($expectedArray, $fieldsArray);
    }
}
