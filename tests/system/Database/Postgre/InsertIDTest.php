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

namespace CodeIgniter\Database\Postgre;

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;
use stdClass;

/**
 * Mock pg_version in the Postgre namespace to test insertID without live DB connection.
 *
 * @param mixed $connection
 *
 * @return array{server: string}
 */
function pg_version($connection = null): array
{
    return ['server' => '15.0'];
}

/**
 * Mock pg_escape_literal in the Postgre namespace for testing without live DB connection.
 *
 * @param mixed $connection
 */
function pg_escape_literal($connection, string $string): string
{
    return "'" . str_replace("'", "''", $string) . "'";
}

/**
 * @internal
 */
#[Group('Others')]
final class InsertIDTest extends CIUnitTestCase
{
    public function testInsertIDQuotesTableColumnAndSequenceNames(): void
    {
        $connection = new class () extends Connection {
            /**
             * @var list<string>
             */
            public array $queries = [];

            public function __construct()
            {
                $this->DBDriver = 'Postgre';
                $this->connID   = false;
            }

            public function initialize(): void
            {
            }

            /**
             * @param array<array-key, mixed>|string|null $binds
             */
            public function query(string $sql, $binds = null, bool $setEscapeFlags = true, string $queryClass = ''): Result
            {
                $this->queries[] = $sql;

                $row         = new stdClass();
                $row->seq    = "user's_id_seq";
                $row->ins_id = 123;

                $result = new class ($this->connID, $this->resultID) extends Result {
                    public function setRowData(stdClass $row): void
                    {
                        $this->resultObject = [$row];
                    }
                };
                $result->setRowData($row);

                return $result;
            }
        };

        $id = $connection->insertID("user's", "id'col");

        $this->assertSame(123, $id);
        $this->assertCount(2, $connection->queries);
        $this->assertSame(
            "SELECT pg_get_serial_sequence('user''s', 'id''col') AS seq",
            $connection->queries[0],
        );
        $this->assertSame(
            "SELECT CURRVAL('user''s_id_seq') AS ins_id",
            $connection->queries[1],
        );
    }
}
