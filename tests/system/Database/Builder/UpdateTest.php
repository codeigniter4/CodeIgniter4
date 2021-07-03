<?php

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\BaseBuilder;
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

    public function testUpdate()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $builder->testMode()->where('id', 1)->update(['name' => 'Programmer'], null, null);

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

    public function testUpdateThrowsExceptionWithNoData()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException('CodeIgniter\Database\Exceptions\DatabaseException');
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
            WHEN "id" = :id: THEN :name:
            WHEN "id" = :id.1: THEN :name.1:
            ELSE "name" END, "description" = CASE{$space}
            WHEN "id" = :id: THEN :description:
            WHEN "id" = :id.1: THEN :description.1:
            ELSE "description" END
            WHERE "id" IN(:id:,:id.1:)
            EOF;

        $this->assertSame($expected, $query->getOriginalQuery());

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

    public function testUpdateBatchThrowsExceptionWithNoData()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
        $this->expectExceptionMessage('You must use the "set" method to update an entry.');

        $builder->updateBatch(null, 'id');
    }

    public function testUpdateBatchThrowsExceptionWithNoID()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
        $this->expectExceptionMessage('You must specify an index to match on for batch updates.');

        $builder->updateBatch([]);
    }

    public function testUpdateBatchThrowsExceptionWithEmptySetArray()
    {
        $builder = new BaseBuilder('jobs', $this->db);

        $this->expectException('\CodeIgniter\Database\Exceptions\DatabaseException');
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

        $expectedSQL   = 'UPDATE "mytable" SET field = field+1 WHERE "id" = 2';
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

        $expectedSQL   = 'UPDATE "mytable" SET "foo" = \'bar\', field = field+1 WHERE "id" = 2';
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
