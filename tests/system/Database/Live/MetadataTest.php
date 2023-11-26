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
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class MetadataTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    /**
     * The seed file used for all tests within this test case.
     *
     * @var string
     */
    protected $seed = CITestSeeder::class;

    private array $expectedTables = [];

    protected function setUp(): void
    {
        parent::setUp();

        $prefix = $this->db->getPrefix();

        $tables = [
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
            $tables[] = $prefix . 'ci_sessions';
        }

        sort($tables);
        $this->expectedTables = $tables;
    }

    private function createExtraneousTable(): void
    {
        $oldPrefix = $this->db->getPrefix();
        $this->db->setPrefix('tmp_');

        Database::forge($this->DBGroup)
            ->addField([
                'name'       => ['type' => 'varchar', 'constraint' => 31],
                'created_at' => ['type' => 'datetime', 'null' => true],
            ])
            ->createTable('widgets');

        $this->db->setPrefix($oldPrefix);
    }

    private function dropExtraneousTable(): void
    {
        $oldPrefix = $this->db->getPrefix();
        $this->db->setPrefix('tmp_');

        Database::forge($this->DBGroup)->dropTable('widgets');

        $this->db->setPrefix($oldPrefix);
    }

    public function testListTablesUnconstrainedByPrefixReturnsAllTables(): void
    {
        try {
            $this->createExtraneousTable();

            $tables = $this->db->listTables();
            $this->assertIsArray($tables);
            $this->assertNotSame([], $tables);

            $expectedTables   = $this->expectedTables;
            $expectedTables[] = 'tmp_widgets';

            sort($tables);
            $this->assertSame($expectedTables, array_values($tables));
        } finally {
            $this->dropExtraneousTable();
        }
    }

    public function testListTablesConstrainedByPrefixReturnsOnlyTablesWithMatchingPrefix(): void
    {
        try {
            $this->createExtraneousTable();

            $tables = $this->db->listTables(true);
            $this->assertIsArray($tables);
            $this->assertNotSame([], $tables);

            sort($tables);
            $this->assertSame($this->expectedTables, array_values($tables));
        } finally {
            $this->dropExtraneousTable();
        }
    }

    public function testListTablesConstrainedByExtraneousPrefixReturnsOnlyTheExtraneousTable(): void
    {
        $oldPrefix = '';

        try {
            $this->createExtraneousTable();

            $oldPrefix = $this->db->getPrefix();
            $this->db->setPrefix('tmp_');

            $tables = $this->db->listTables(true);
            $this->assertIsArray($tables);
            $this->assertNotSame([], $tables);

            sort($tables);
            $this->assertSame(['tmp_widgets'], array_values($tables));
        } finally {
            $this->db->setPrefix($oldPrefix);
            $this->dropExtraneousTable();
        }
    }
}
