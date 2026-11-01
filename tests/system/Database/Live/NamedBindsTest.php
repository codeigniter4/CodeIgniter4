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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class NamedBindsTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    public function testNamedBindsAreNotReplacedInsideEscapedValues(): void
    {
        $sql = 'SELECT ' . $this->db->escape(':id:') . ' AS lit, :id: AS id';

        if ($this->db->DBDriver === 'OCI8') {
            $sql = 'SELECT ' . $this->db->escape(':id:') . ' AS "lit", :id: AS "id" FROM DUAL';
        }

        $row = $this->db->query($sql, ['id' => 5])->getRowArray();

        $this->assertSame(':id:', $row['lit']);
        $this->assertSame(5, (int) $row['id']);
    }

    public function testNamedBindsWithDialectSpecificQuotingAndComments(): void
    {
        [$sql, $expected] = match ($this->db->DBDriver) {
            'MySQLi' => [
                "SELECT 10--2 AS diff, ':id:' AS lit, :id: AS id # it's",
                ['diff' => '12', 'lit' => ':id:', 'id' => '5'],
            ],
            'Postgre' => [
                "SELECT \$\$it's :id:\$\$ AS lit, 1 /* a /* it's */ :id: */ AS one, :id: AS id",
                ['lit' => "it's :id:", 'one' => '1', 'id' => '5'],
            ],
            'SQLSRV' => [
                "SELECT 1 AS [it's], 1 /* a /* it's */ :id: */ AS one, ':id:' AS lit, :id: AS id",
                ["it's" => '1', 'one' => '1', 'lit' => ':id:', 'id' => '5'],
            ],
            'SQLite3' => [
                "SELECT 1 AS [it's], ':id:' AS lit, :id: AS id",
                ["it's" => '1', 'lit' => ':id:', 'id' => '5'],
            ],
            'OCI8' => [
                "SELECT q'[it's :id:]' AS \"lit\", nq'{:id:}' AS \"lit2\", :id: AS \"id\" FROM DUAL",
                ['lit' => "it's :id:", 'lit2' => ':id:', 'id' => '5'],
            ],
            default => $this->markTestSkipped('Not supported for ' . $this->db->DBDriver . '.'),
        };

        $row = $this->db->query($sql, ['id' => 5])->getRowArray();

        $this->assertSame($expected, array_map(strval(...), $row));
    }

    public function testNamedBindsWithMultibyteConnectionCharset(): void
    {
        // 0xBF5C is a single GBK character whose trailing byte is a backslash.
        [$charset, $value] = match ($this->db->DBDriver) {
            'MySQLi'  => ['gbk', "x\xbf\x5c"],
            'Postgre' => ['GBK', "a\\\xbf\x5c"], // the backslash makes pg_escape_literal() create E'...'
            default   => $this->markTestSkipped('Not needed for ' . $this->db->DBDriver . '.'),
        };

        $config            = config(Database::class)->tests;
        $config['charset'] = $charset;

        if ($this->db->DBDriver === 'Postgre') {
            // pg_connect() reuses an open connection with the same DSN, so make
            // the DSN unique to avoid changing or closing the shared connection.
            $config['DSN'] = sprintf(
                "host=%s port=%s user=%s password='%s' dbname=%s application_name=named_binds_test",
                $config['hostname'],
                $config['port'] !== '' ? $config['port'] : 5432,
                $config['username'],
                $config['password'],
                $config['database'],
            );
        }

        $db = Database::connect($config, false);

        try {
            $row = $db->query(
                'SELECT ' . $db->escape($value) . ' AS a, ' . $db->escape(':id:') . ' AS lit, :id: AS id',
                ['id' => 5],
            )->getRowArray();
        } finally {
            $db->close();
        }

        $this->assertSame(['a' => $value, 'lit' => ':id:', 'id' => '5'], array_map(strval(...), $row));
    }
}
