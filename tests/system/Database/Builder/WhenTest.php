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
 *
 * @group Others
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

    public function testWhenTrue(): void
    {
        $builder = $this->db->table('jobs');

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $builder = $builder->when(true, static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenTruthy(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when('abc', static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenRunsDefaultWhenFalse(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when(false, static function ($query): void {
            $query->select('id');
        }, static function ($query): void {
            $query->select('name');
        });

        $expectedSQL = 'SELECT "name" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenDoesntModifyWhenFalse(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->when(false, static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenPassesParemeters(): void
    {
        $builder = $this->db->table('jobs');
        $name    = 'developer';

        $builder = $builder->when($name, static function ($query, $name): void {
            $query->where('name', $name);
        });

        $expectedSQL = 'SELECT * FROM "jobs" WHERE "name" = \'developer\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenNotFalse(): void
    {
        $builder = $this->db->table('jobs');

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));

        $builder = $builder->whenNot(false, static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenNotFalsey(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->whenNot('0', static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT "id" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenNotRunsDefaultWhenTrue(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->whenNot(true, static function ($query): void {
            $query->select('id');
        }, static function ($query): void {
            $query->select('name');
        });

        $expectedSQL = 'SELECT "name" FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenNotDoesntModifyWhenFalse(): void
    {
        $builder = $this->db->table('jobs');

        $builder = $builder->whenNot(true, static function ($query): void {
            $query->select('id');
        });

        $expectedSQL = 'SELECT * FROM "jobs"';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }

    public function testWhenNotPassesParemeters(): void
    {
        $builder = $this->db->table('jobs');
        $name    = '0';

        $builder = $builder->whenNot($name, static function ($query, $name): void {
            $query->where('name', $name);
        });

        $expectedSQL = 'SELECT * FROM "jobs" WHERE "name" = \'0\'';
        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledSelect()));
    }
}
