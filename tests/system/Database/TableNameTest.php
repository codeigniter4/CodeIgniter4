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

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class TableNameTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([
            'database' => 'test',
            'DBPrefix' => 'db_',
            'schema'   => 'dbo',
        ]);
    }

    public function testInstantiate(): void
    {
        $table = 'table';

        $tableName = TableName::create($this->db->DBPrefix, $table);

        $this->assertInstanceOf(TableName::class, $tableName);
    }

    public function testCreateAndTableName(): void
    {
        $table = 'table';

        $tableName = TableName::create($this->db->DBPrefix, $table);

        $this->assertSame($table, $tableName->getTableName());
        $this->assertSame('db_table', $tableName->getActualTableName());
    }

    public function testFromActualNameAndTableNameWithPrefix(): void
    {
        $actualTable = 'db_table';

        $tableName = TableName::fromActualName($this->db->DBPrefix, $actualTable);

        $this->assertSame('table', $tableName->getTableName());
        $this->assertSame($actualTable, $tableName->getActualTableName());
    }

    public function testFromActualNameAndTableNameWithoutPrefix(): void
    {
        $actualTable = 'table';

        $tableName = TableName::fromActualName($this->db->DBPrefix, $actualTable);

        $this->assertSame('', $tableName->getTableName());
        $this->assertSame($actualTable, $tableName->getActualTableName());
    }

    public function testGetAlias(): void
    {
        $table = 'table';
        $alias = 't';

        $tableName = TableName::create($this->db->DBPrefix, $table, $alias);

        $this->assertSame($alias, $tableName->getAlias());
    }

    public function testGetSchema(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';

        $tableName = TableName::fromFullName($this->db->DBPrefix, $table, $schema, $database);

        $this->assertSame($schema, $tableName->getSchema());
    }

    public function testGetDatabase(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';

        $tableName = TableName::fromFullName($this->db->DBPrefix, $table, $schema, $database);

        $this->assertSame($database, $tableName->getDatabase());
    }
}
