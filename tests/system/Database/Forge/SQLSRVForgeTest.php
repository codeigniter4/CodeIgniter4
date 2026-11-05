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

namespace CodeIgniter\Database\Forge;

use CodeIgniter\Database\ResultInterface;
use CodeIgniter\Database\SQLSRV\Connection;
use CodeIgniter\Database\SQLSRV\Forge;
use CodeIgniter\Database\SQLSRV\Result;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class SQLSRVForgeTest extends CIUnitTestCase
{
    public function testModifyColumnEscapesTableNameAndColumnNameInDefaultConstraintLookup(): void
    {
        $db = new class () extends Connection {
            /**
             * @var list<string>
             */
            public array $queries = [];

            public function __construct()
            {
                $this->DBDriver   = 'SQLSRV';
                $this->schema     = 'dbo';
                $this->escapeChar = ['[', ']'];
                $this->connID     = false;
                $this->pretend    = true;
            }

            public function initialize(): void
            {
            }

            /**
             * @param array<array-key, mixed>|string|null $binds
             */
            public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): ResultInterface
            {
                $this->queries[] = $sql;

                return new class ($this->connID, $this->resultID) extends Result {
                    public function getRowArray(int $n = 0): array
                    {
                        return ['constraint_name' => 'df_test'];
                    }
                };
            }
        };

        $forge = new Forge($db);

        $forge->modifyColumn("user's", [
            "col'name" => [
                'type'    => 'VARCHAR',
                'default' => "'default_val'",
            ],
        ]);

        $this->assertNotEmpty($db->queries);
        $findSql = $db->queries[0];

        $this->assertStringContainsString("OBJECT_ID(N'[dbo].[user''s]')", $findSql);
        $this->assertStringContainsString("c.name = N'col''name'", $findSql);
    }

    public function testDropColumnEscapesTableNameInDefaultConstraintLookup(): void
    {
        $db = new class () extends Connection {
            /**
             * @var list<string>
             */
            public array $queries = [];

            public function __construct()
            {
                $this->DBDriver   = 'SQLSRV';
                $this->schema     = 'dbo';
                $this->escapeChar = ['[', ']'];
                $this->connID     = false;
                $this->pretend    = true;
            }

            public function initialize(): void
            {
            }

            /**
             * @param array<array-key, mixed>|string|null $binds
             */
            public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): ResultInterface
            {
                $this->queries[] = $sql;

                return new class ($this->connID, $this->resultID) extends Result {
                    public function getResultArray(): array
                    {
                        return [];
                    }
                };
            }
        };

        $forge = new Forge($db);

        $forge->dropColumn("user's", "col'name");

        $this->assertCount(3, $db->queries);
        $dropDefaultSql = $db->queries[1];

        $this->assertStringContainsString("parent_object_id = OBJECT_ID(N'[dbo].[user''s]')", $dropDefaultSql);
        $this->assertStringContainsString("object_id = OBJECT_ID(N'[dbo].[user''s]')", $dropDefaultSql);
    }
}
