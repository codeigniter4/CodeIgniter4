<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class MetadataTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    /**
     * Array of expected tables.
     *
     * @var array
     */
    protected $expectedTables;

    protected function setUp(): void
    {
        parent::setUp();

        // Prepare the array of expected tables once
        $prefix               = $this->db->getPrefix();
        $this->expectedTables = [
            $prefix . 'migrations',
            $prefix . 'user',
            $prefix . 'job',
            $prefix . 'misc',
            $prefix . 'type_test',
            $prefix . 'empty',
            $prefix . 'secondary',
            $prefix . 'stringifypkey',
            $prefix . 'without_auto_increment',
            $prefix . 'ip_table',
        ];

        if (in_array($this->db->DBDriver, ['MySQLi', 'Postgre'], true)) {
            $this->expectedTables[] = $prefix . 'ci_sessions';
        }
    }

    public function testListTables()
    {
        $result = $this->db->listTables(true);

        $this->assertSame($this->expectedTables, array_values($result));
    }

    public function testListTablesConstrainPrefix()
    {
        $result = $this->db->listTables(true);

        $this->assertSame($this->expectedTables, array_values($result));
    }

    public function testConstrainPrefixIgnoresOtherTables()
    {
        $this->forge = Database::forge($this->DBGroup);

        // Stash the prefix and change it
        $DBPrefix = $this->db->getPrefix();
        $this->db->setPrefix('tmp_');

        // Create a table with the new prefix
        $fields = [
            'name' => [
                'type'       => 'varchar',
                'constraint' => 31,
            ],
            'created_at' => [
                'type' => 'datetime',
                'null' => true,
            ],
        ];
        $this->forge->addField($fields);
        $this->forge->createTable('widgets');

        // Restore the prefix and get the tables with the original prefix
        $this->db->setPrefix($DBPrefix);
        $result = $this->db->listTables(true);

        $this->assertSame($this->expectedTables, array_values($result));

        // Clean up temporary table
        $this->db->setPrefix('tmp_');
        $this->forge->dropTable('widgets');
        $this->db->setPrefix($DBPrefix);
    }
}
