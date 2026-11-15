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

namespace CodeIgniter\Database\Live\Postgre;

use CodeIgniter\Database\Postgre\Connection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class InsertIDTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);

        if ($this->db->DBDriver !== 'Postgre') {
            $this->markTestSkipped('This test is only for Postgre.');
        }
    }

    public function testInsertIDWithApostropheInColumnName(): void
    {
        $tableName  = 'temp_insertid_test';
        $columnName = "id'col";

        $this->db->query(
            'CREATE TEMPORARY TABLE ' . $this->db->escapeIdentifiers($tableName) . ' ('
            . $this->db->escapeIdentifiers($columnName) . ' SERIAL PRIMARY KEY, "name" VARCHAR(255))',
        );

        try {
            $this->db->query(
                'INSERT INTO ' . $this->db->escapeIdentifiers($tableName) . ' ("name") VALUES (?)',
                ['Test Name'],
            );

            /** @var Connection $db */
            $db = $this->db;

            $id = $db->insertID($tableName, $columnName);

            $this->assertSame(1, $id);
        } finally {
            $this->db->query('DROP TABLE IF EXISTS ' . $this->db->escapeIdentifiers($tableName));
        }
    }
}
