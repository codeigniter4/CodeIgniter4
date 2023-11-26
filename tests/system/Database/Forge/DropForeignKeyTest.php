<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Forge;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Forge;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class DropForeignKeyTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();
        $this->db = new MockConnection([]);
    }

    public function testDropForeignKeyWithEmptyDropConstraintStrProperty(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', true);

        $forge = new Forge($this->db);

        $this->expectException(DatabaseException::class);

        $forge->dropForeignKey('id', 'fail');
    }
}
