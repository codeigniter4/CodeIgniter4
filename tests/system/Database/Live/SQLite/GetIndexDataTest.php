<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\SQLite;

use CodeIgniter\Database\SQLite3\Connection;
use CodeIgniter\Database\SQLite3\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use stdClass;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class GetIndexDataTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * In setUp() db connection is changed. So migration doesn't work
     *
     * @var bool
     */
    protected $migrate = false;

    /**
     * @var Connection
     */
    protected $db;

    private Forge $forge;

    protected function setUp(): void
    {
        parent::setUp();

        $config = [
            'DBDriver' => 'SQLite3',
            'database' => 'database.db',
            'DBDebug'  => true,
        ];

        $this->db    = db_connect($config);
        $this->forge = Database::forge($config);
    }

    public function testGetIndexData(): void
    {
        // INTEGER PRIMARY KEY AUTO_INCREMENT doesn't get an index by default
        $this->forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 3, 'auto_increment' => true],
            'userid'     => ['type' => 'INTEGER', 'constraint' => 3],
            'name'       => ['type' => 'VARCHAR', 'constraint' => 80],
            'email'      => ['type' => 'VARCHAR', 'constraint' => 100],
            'country'    => ['type' => 'VARCHAR', 'constraint' => 40],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
            'deleted_at' => ['type' => 'DATETIME', 'null' => true],
        ])
            ->addKey(['id'], true)
            ->addUniqueKey('email')
            ->addKey('country')
            ->createTable('testuser', true);

        $expectedIndexes = [];

        $row                        = new stdclass();
        $row->name                  = 'PRIMARY';
        $row->fields                = ['id'];
        $row->type                  = 'PRIMARY';
        $expectedIndexes['PRIMARY'] = $row;

        $row                               = new stdclass();
        $row->name                         = 'testuser_email';
        $row->fields                       = ['email'];
        $row->type                         = 'UNIQUE';
        $expectedIndexes['testuser_email'] = $row;

        $row                                 = new stdclass();
        $row->name                           = 'testuser_country';
        $row->fields                         = ['country'];
        $row->type                           = 'INDEX';
        $expectedIndexes['testuser_country'] = $row;

        $indexes = $this->db->getIndexData('testuser');

        $this->assertSame($expectedIndexes['PRIMARY']->fields, $indexes['PRIMARY']->fields);
        $this->assertSame($expectedIndexes['PRIMARY']->type, $indexes['PRIMARY']->type);

        $this->assertSame($expectedIndexes['testuser_email']->fields, $indexes['testuser_email']->fields);
        $this->assertSame($expectedIndexes['testuser_email']->type, $indexes['testuser_email']->type);

        $this->assertSame($expectedIndexes['testuser_country']->fields, $indexes['testuser_country']->fields);
        $this->assertSame($expectedIndexes['testuser_country']->type, $indexes['testuser_country']->type);

        $this->forge->dropTable('testuser', true);
    }
}
