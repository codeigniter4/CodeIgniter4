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

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use CodeIgniter\Test\Mock\MockQuery;

/**
 * @internal
 */
final class UpdateTest extends CIUnitTestCase
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

    public function testUpdateArray()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $data = ['name' => 'Programmer'];
        $builder->testMode()->where('id', 1)->update($data, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'Programmer\' WHERE "id" = 1';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
            'name' => [
                'Programmer',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateObject()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $data = (object) ['name' => 'Programmer'];
        $builder->testMode()->where('id', 1)->update($data, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'Programmer\' WHERE "id" = 1';
        $expectedBinds = [
            'id' => [
                1,
                true,
            ],
            'name' => [
                'Programmer',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateInternalWhereAndLimit()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->update(['name' => 'Programmer'], ['id' => 1], 5);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'Programmer\' WHERE "id" = 1  LIMIT 5';
        $expectedBinds = [
            'name' => [
                'Programmer',
                true,
            ],
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithSet()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->set('name', 'Programmer')->where('id', 1)->update(null, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'Programmer\' WHERE "id" = 1';
        $expectedBinds = [
            'name' => [
                'Programmer',
                true,
            ],
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithSetAsInt()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->set('age', 22)->where('id', 1)->update(null, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "age" = 22 WHERE "id" = 1';
        $expectedBinds = [
            'age' => [
                22,
                true,
            ],
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithSetAsBoolean()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->set('manager', true)->where('id', 1)->update(null, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "manager" = 1 WHERE "id" = 1';
        $expectedBinds = [
            'manager' => [
                true,
                true,
            ],
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithSetAsArray()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->set(['name' => 'Programmer', 'age' => 22, 'manager' => true])->where('id', 1)->update(null, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'Programmer\', "age" = 22, "manager" = 1 WHERE "id" = 1';
        $expectedBinds = [
            'name' => [
                'Programmer',
                true,
            ],
            'age' => [
                22,
                true,
            ],
            'manager' => [
                true,
                true,
            ],
            'id' => [
                1,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateThrowsExceptionWithNoData()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->update(null, null, null);
    }

    public function testUpdateBatch()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $updateData = [
            [
                'id'          => 2,
                'name'        => 'Comedian',
                'description' => 'There\'s something in your teeth',
            ],
            [
                'id'          => 3,
                'name'        => 'Cab Driver',
                'description' => 'I am yellow',
            ],
        ];

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->updateBatch($updateData, 'id');

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(MockQuery::class, $query);

        $space = ' ';

        $expected = <<<EOF
            UPDATE "jobs" SET "name" = CASE{$space}
            WHEN "id" = 2 THEN 'Comedian'
            WHEN "id" = 3 THEN 'Cab Driver'
            ELSE "name" END, "description" = CASE{$space}
            WHEN "id" = 2 THEN 'There''s something in your teeth'
            WHEN "id" = 3 THEN 'I am yellow'
            ELSE "description" END
            WHERE "id" IN(2,3)
            EOF;

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetUpdateBatchWithoutEscape()
    {
        $builder = new BaseBuilder('jobs', $this->db);
        $escape  = false;

        $builder->setUpdateBatch([
            [
                'id'          => 2,
                'name'        => 'SUBSTRING(name, 1)',
                'description' => 'SUBSTRING(description, 3)',
            ],
            [
                'id'          => 3,
                'name'        => 'SUBSTRING(name, 2)',
                'description' => 'SUBSTRING(description, 4)',
            ],
        ], 'id', $escape);

        $this->db->shouldReturn('execute', 1)->shouldReturn('affectedRows', 1);
        $builder->updateBatch(null, 'id');

        $query = $this->db->getLastQuery();
        $this->assertInstanceOf(MockQuery::class, $query);

        $space = ' ';

        $expected = <<<EOF
            UPDATE "jobs" SET "name" = CASE{$space}
            WHEN "id" = 2 THEN SUBSTRING(name, 1)
            WHEN "id" = 3 THEN SUBSTRING(name, 2)
            ELSE "name" END, "description" = CASE{$space}
            WHEN "id" = 2 THEN SUBSTRING(description, 3)
            WHEN "id" = 3 THEN SUBSTRING(description, 4)
            ELSE "description" END
            WHERE "id" IN(2,3)
            EOF;

        $this->assertSame($expected, $query->getQuery());
    }

    public function testUpdateBatchThrowsExceptionWithNoData()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->updateBatch(null, 'id');
    }

    public function testUpdateBatchThrowsExceptionWithNoID()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must specify an index to match on for batch updates.');

        $builder->updateBatch([]);
    }

    public function testUpdateBatchThrowsExceptionWithEmptySetArray()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('updateBatch() called with no data');

        $builder->updateBatch([], 'id');
    }

    public function testUpdateWithWhereSameColumn()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->update(['name' => 'foobar'], ['name' => 'Programmer'], null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'foobar\' WHERE "name" = \'Programmer\'';
        $expectedBinds = [
            'name' => [
                'foobar',
                true,
            ],
            'name.1' => [
                'Programmer',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithWhereSameColumn2()
    {
        // calling order: set() -> where()
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()
            ->set('name', 'foobar')
            ->where('name', 'Programmer')
            ->update(null, null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'foobar\' WHERE "name" = \'Programmer\'';
        $expectedBinds = [
            'name' => [
                'foobar',
                true,
            ],
            'name.1' => [
                'Programmer',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testUpdateWithWhereSameColumn3()
    {
        // calling order: where() -> set() in update()
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()
            ->where('name', 'Programmer')
            ->update(['name' => 'foobar'], null, null);

        $expectedSQL   = 'UPDATE "jobs" SET "name" = \'foobar\' WHERE "name" = \'Programmer\'';
        $expectedBinds = [
            'name' => [
                'Programmer',
                true,
            ],
            'name.1' => [
                'foobar',
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    /**
     * @see https://codeigniter4.github.io/CodeIgniter4/database/query_builder.html#updating-data
     */
    public function testSetWithoutEscape()
    {
        $builder = new BaseBuilder('mytable', $this->db);

        $builder->testMode()
            ->set('field', 'field+1', false)
            ->where('id', 2)
            ->update(null, null, null);

        $expectedSQL   = 'UPDATE "mytable" SET "field" = field+1 WHERE "id" = 2';
        $expectedBinds = [
            'id' => [
                2,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }

    public function testSetWithAndWithoutEscape()
    {
        $builder = new BaseBuilder('mytable', $this->db);

        $builder->testMode()
            ->set('foo', 'bar')
            ->set('field', 'field+1', false)
            ->where('id', 2)
            ->update(null, null, null);

        $expectedSQL   = 'UPDATE "mytable" SET "foo" = \'bar\', "field" = field+1 WHERE "id" = 2';
        $expectedBinds = [
            'foo' => [
                'bar',
                true,
            ],
            'id' => [
                2,
                true,
            ],
        ];

        $this->assertSame($expectedSQL, str_replace("\n", ' ', $builder->getCompiledUpdate()));
        $this->assertSame($expectedBinds, $builder->getBinds());
    }
}
