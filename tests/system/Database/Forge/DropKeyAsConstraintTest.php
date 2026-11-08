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

use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Database\OCI8\Forge as OCI8Forge;
use CodeIgniter\Database\Postgre\Connection as PostgreConnection;
use CodeIgniter\Database\Postgre\Forge as PostgreForge;
use CodeIgniter\Database\SQLSRV\Connection as SQLSRVConnection;
use CodeIgniter\Database\SQLSRV\Forge as SQLSRVForge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DropKeyAsConstraintTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testPostgreDropKeyAsConstraintEscaping(): void
    {
        $this->setPrivateProperty($this->db, 'schema', "my'schema");

        /** @var PostgreConnection $db */
        $db = $this->db;

        $forge  = new PostgreForge($db);
        $method = $this->getPrivateMethodInvoker($forge, '_dropKeyAsConstraint');

        $sql = $method("user's", "fk'id");

        $expected = <<<'SQL'
            SELECT con.conname
            FROM pg_catalog.pg_constraint con
            INNER JOIN pg_catalog.pg_class rel
                ON rel.oid = con.conrelid
            INNER JOIN pg_catalog.pg_namespace nsp
                ON nsp.oid = connamespace
            WHERE nsp.nspname = 'my''schema'
                AND rel.relname = 'user''s'
                AND con.conname = 'fk''id'
            SQL;

        $this->assertSame($expected, $sql);
    }

    public function testOCI8DropKeyAsConstraintEscaping(): void
    {
        /** @var OCI8Connection $db */
        $db = $this->db;

        $forge  = new OCI8Forge($db);
        $method = $this->getPrivateMethodInvoker($forge, '_dropKeyAsConstraint');

        $sql = $method("user's", "fk'id");

        $expected = <<<'SQL'
            SELECT constraint_name
            FROM all_constraints
            WHERE table_name = 'user''s'
                AND index_name = 'fk''id'
            SQL;

        $this->assertSame($expected, $sql);
    }

    public function testSQLSRVDropKeyAsConstraintEscaping(): void
    {
        $this->setPrivateProperty($this->db, 'schema', 'dbo');

        /** @var SQLSRVConnection $db */
        $db = $this->db;

        $forge  = new SQLSRVForge($db);
        $method = $this->getPrivateMethodInvoker($forge, '_dropKeyAsConstraint');

        $sql = $method("user's", "fk'id");

        $expected = <<<'SQL'
            SELECT CONSTRAINT_NAME
            FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS
            WHERE TABLE_NAME = 'user''s'
                AND CONSTRAINT_NAME = 'fk''id'
            SQL;

        $this->assertSame($expected, $sql);
    }
}
