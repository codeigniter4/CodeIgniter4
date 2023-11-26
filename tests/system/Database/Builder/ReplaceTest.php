<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 *
 * @group Others
 */
final class ReplaceTest extends CIUnitTestCase
{
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testSimpleReplace(): void
    {
        $builder = $this->db->table('jobs');

        $expected = 'REPLACE INTO "jobs" ("title", "name", "date") VALUES (:title:, :name:, :date:)';

        $data = [
            'title' => 'My title',
            'name'  => 'My Name',
            'date'  => 'My date',
        ];

        $this->assertSame($expected, $builder->testMode()->replace($data));
    }

    public function testReplaceThrowsExceptionWithNoData(): void
    {
        $builder = $this->db->table('jobs');

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->replace();
    }
}
