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

/**
 * @internal
 *
 * @group Others
 */
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

        $tableName = TableName::create($this->db, $table);

        $this->assertInstanceOf(TableName::class, $tableName);
    }

    public function testCreateAndTableName(): void
    {
        $table = 'table';

        $tableName = TableName::create($this->db, $table);

        $this->assertSame($table, $tableName->getTableName());
        $this->assertSame('db_table', $tableName->getActualTableName());
    }

    public function testFromActualNameAndTableName(): void
    {
        $table = 'table';

        $tableName = TableName::fromActualName($this->db, $table);

        $this->assertSame($table, $tableName->getTableName());
        $this->assertSame($table, $tableName->getActualTableName());
    }

    public function testGetAlias(): void
    {
        $table = 'table';
        $alias = 't';

        $tableName = TableName::create($this->db, $table, $alias);

        $this->assertSame($alias, $tableName->getAlias());
    }

    public function testGetSchema(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';

        $tableName = TableName::fromFullName($this->db, $table, $schema, $database);

        $this->assertSame($schema, $tableName->getSchema());
    }

    public function testGetDatabase(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';

        $tableName = TableName::fromFullName($this->db, $table, $schema, $database);

        $this->assertSame($database, $tableName->getDatabase());
    }

    public function testGetEscapedTableName(): void
    {
        $table = 'table';

        $tableName = TableName::create($this->db, $table);

        $this->assertSame('"db_table"', $tableName->getEscapedTableName());
    }

    public function testGetEscapedTableNameFullName(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';

        $tableName = TableName::fromFullName($this->db, $table, $schema, $database);

        $this->assertSame('"test"."dbo"."db_table"', $tableName->getEscapedTableName());
    }

    public function testGetEscapedTableNameWithAlias(): void
    {
        $table = 'table';
        $alias = 't';

        $tableName = TableName::create($this->db, $table, $alias);

        $this->assertSame('"db_table" "t"', $tableName->getEscapedTableNameWithAlias());
    }

    public function testGetEscapedTableNameWithAliasWithoutAlias(): void
    {
        $table = 'table';

        $tableName = TableName::create($this->db, $table);

        $this->assertSame('"db_table"', $tableName->getEscapedTableNameWithAlias());
    }

    public function testGetEscapedTableNameWithAliasFullName(): void
    {
        $table    = 'table';
        $schema   = 'dbo';
        $database = 'test';
        $alias    = 't';

        $tableName = TableName::fromFullName($this->db, $table, $schema, $database, $alias);

        $this->assertSame(
            '"test"."dbo"."db_table" "t"',
            $tableName->getEscapedTableNameWithAlias()
        );
    }
}
