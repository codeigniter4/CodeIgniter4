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

use CodeIgniter\Database\Exceptions\ConstraintViolationException;
use CodeIgniter\Database\Exceptions\ForeignKeyConstraintViolationException;
use CodeIgniter\Database\Exceptions\NotNullConstraintViolationException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ConstraintViolationExceptionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private ?Forge $forge = null;
    protected $refresh    = true;
    protected $seed       = CITestSeeder::class;

    protected function tearDown(): void
    {
        $this->enableDBDebug();

        if ($this->forge instanceof Forge) {
            $this->forge->dropTable('cv_child', true);
            $this->forge->dropTable('cv_parent', true);
        }

        parent::tearDown();
    }

    public function testThrowsNotNullConstraintViolationExceptionWithDebugEnabled(): void
    {
        $this->enableDBDebug();

        $this->expectException(NotNullConstraintViolationException::class);

        $this->db->table('user')->insert([
            'name'    => null,
            'email'   => 'not-null@example.com',
            'country' => 'US',
        ]);
    }

    public function testThrowsNotNullConstraintViolationExceptionForUpdateWithDebugEnabled(): void
    {
        $this->enableDBDebug();

        $this->expectException(NotNullConstraintViolationException::class);

        $this->db->table('user')
            ->where('id', 1)
            ->update(['name' => null]);
    }

    public function testThrowsConstraintViolationExceptionForForeignKeyWithDebugEnabled(): void
    {
        $this->enableDBDebug();
        $this->createForeignKeyTables();

        $expectedException = $this->db->DBDriver === 'SQLSRV'
            ? ConstraintViolationException::class
            : ForeignKeyConstraintViolationException::class;

        $this->expectException($expectedException);

        $this->db->table('cv_child')->insert([
            'id'        => 1,
            'parent_id' => 999,
        ]);
    }

    public function testStoresNotNullConstraintViolationExceptionWithDebugDisabled(): void
    {
        $this->disableDBDebug();

        $result = $this->db->table('user')->insert([
            'name'    => null,
            'email'   => 'not-null@example.com',
            'country' => 'US',
        ]);

        $this->assertFalse($result);
        $this->assertInstanceOf(NotNullConstraintViolationException::class, $this->db->getLastException());
    }

    private function createForeignKeyTables(): void
    {
        $this->forge = Database::forge($this->DBGroup);

        $this->forge->dropTable('cv_child', true);
        $this->forge->dropTable('cv_parent', true);

        $this->forge->addField([
            'id' => ['type' => 'INTEGER', 'constraint' => 3],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('cv_parent');

        $this->forge->addField([
            'id'        => ['type' => 'INTEGER', 'constraint' => 3],
            'parent_id' => ['type' => 'INTEGER', 'constraint' => 3],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey(
            'parent_id',
            'cv_parent',
            'id',
            '',
            '',
            $this->db->DBDriver === 'SQLite3' ? '' : 'fk_cv_child_parent',
        );
        $this->forge->createTable('cv_child');

        $this->db->enableForeignKeyChecks();
    }
}
