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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;

/**
 * @internal
 */
final class WhenTest extends CIUnitTestCase
{
    /**
     * @var MockConnection
     */
    protected $db;

    protected function setUp(): void
    {
        parent::setUp();

        $this->db = new MockConnection([]);
    }

    public function testWhenTrue()
    {
        $builder = $this->db->table('jobs');

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $builder = $builder->when(true, static function ($query) {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenTruthy()
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when('abc', static function ($query) {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenRunsDefaultWhenFalse()
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when(false, static function ($query) {
            $query->select('id');
        }, static function ($query) {
            $query->select('name');
        });

        $expectedSQL = 'SELECT "name" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenDoesntModifyWhenFalse()
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when(false, static function ($query) {
            $query->select('id');
        });

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenPassesParemeters()
    {
        $builder = $this->db->table('jobs');
        $name    = 'developer';

        $builder = $builder->when($name, static function ($query, $name) {
            $query->where('name', $name);
        });

        $expectedSQL = 'SELECT * FROM "jobs" WHERE "name" = \'developer\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
