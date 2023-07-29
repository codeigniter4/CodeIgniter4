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
 *
 * @group Others
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

    public function testUpdateArray(): void
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

    public function testUpdateObject(): void
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

    public function testUpdateInternalWhereAndLimit(): void
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

    public function testUpdateWithSet(): void
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

    public function testUpdateWithSetAsInt(): void
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

    public function testUpdateWithSetAsBoolean(): void
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

    public function testUpdateWithSetAsArray(): void
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

    public function testUpdateThrowsExceptionWithNoData(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->update(null, null, null);
    }

    public function testUpdateBatch(): void
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

        $expected = <<<'EOF'
            UPDATE "jobs"
            SET
            "description" = _u."description",
            "name" = _u."name"
            FROM (
            SELECT 'There''s something in your teeth' "description", 2 "id", 'Comedian' "name" UNION ALL
            SELECT 'I am yellow' "description", 3 "id", 'Cab Driver' "name"
            ) _u
            WHERE "jobs"."id" = _u."id"
            EOF;

        $this->assertSame($expected, $query->getQuery());
    }

    public function testSetUpdateBatchWithoutEscape(): void
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

        $expected = <<<'EOF'
            UPDATE "jobs"
            SET
            "description" = _u."description",
            "name" = _u."name"
            FROM (
            SELECT SUBSTRING(description, 3) "description", 2 "id", SUBSTRING(name, 1) "name" UNION ALL
            SELECT SUBSTRING(description, 4) "description", 3 "id", SUBSTRING(name, 2) "name"
            ) _u
            WHERE "jobs"."id" = _u."id"
            EOF;

        $this->assertSame($expected, $query->getQuery());
    }

    public function testUpdateBatchThrowsExceptionWithNoData(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('updateBatch() has no data.');

        $builder->updateBatch(null, 'id');
    }

    public function testUpdateBatchThrowsExceptionWithNoID(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must specify a constraint to match on for batch updates.');

        $set = [
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
        ];

        $builder->updateBatch($set, null);
    }

    public function testUpdateBatchThrowsExceptionWithEmptySetArray(): void
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('updateBatch() has no data.');

        $builder->updateBatch([], 'id');
    }

    public function testUpdateWithWhereSameColumn(): void
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

    public function testUpdateWithWhereSameColumn2(): void
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

    public function testUpdateWithWhereSameColumn3(): void
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
    public function testSetWithoutEscape(): void
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

    public function testSetWithAndWithoutEscape(): void
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
