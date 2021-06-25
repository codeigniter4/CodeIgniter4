<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Exceptions\ModelException;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
final class FindModelTest extends LiveModelTestCase
{
    public function testFindReturnsRow(): void
    {
        $this->createModel(JobModel::class);
        $this->assertSame('Musician', $this->model->find(4)->name);
    }

    public function testFindReturnsMultipleRows(): void
    {
        $this->createModel(JobModel::class);
        $jobs = $this->model->find([1, 4]);

        $this->assertSame('Developer', $jobs[0]->name);
        $this->assertSame('Musician', $jobs[1]->name);
    }

    public function testGetColumnWithStringColumnName(): void
    {
        $this->createModel(JobModel::class);
        $job = $this->model->findColumn('name');

        $this->assertSame('Developer', $job[0]);
        $this->assertSame('Politician', $job[1]);
        $this->assertSame('Accountant', $job[2]);
        $this->assertSame('Musician', $job[3]);
    }

    public function testGetColumnsWithMultipleColumnNames(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Only single column allowed in Column name.');
        $this->createModel(JobModel::class)->findColumn('name,description');
    }

    public function testFindActsAsGetWithNoParams(): void
    {
        $this->createModel(JobModel::class);

        $jobs = $this->model->asArray()->find();
        $this->assertCount(4, $jobs);

        $names = array_column($jobs, 'name');
        $this->assertContains('Developer', $names);
        $this->assertContains('Politician', $names);
        $this->assertContains('Accountant', $names);
        $this->assertContains('Musician', $names);
    }

    public function testFindRespectsReturnArray(): void
    {
        $job = $this->createModel(JobModel::class)->asArray()->find(4);
        $this->assertIsArray($job);
    }

    public function testFindRespectsReturnObject(): void
    {
        $job = $this->createModel(JobModel::class)->asObject()->find(4);
        $this->assertIsObject($job);
    }

    public function testFindRespectsSoftDeletes(): void
    {
        $this->db->table('user')
            ->where('id', 4)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $this->createModel(UserModel::class);

        $user = $this->model->find(4);
        $this->assertEmpty($user);

        $user  = $this->model->withDeleted()->find(4);
        $count = is_object($user) ? 1 : 0;
        $this->assertSame(1, $count);
    }

    public function testFindClearsBinds(): void
    {
        $this->createModel(JobModel::class);
        $this->model->find(1);
        $this->model->find(1);

        // Binds should be reset to 0 after each one
        $binds = $this->model->builder()->getBinds();
        $this->assertCount(0, $binds);

        $query = $this->model->getLastQuery();
        $this->assertCount(1, $this->getPrivateProperty($query, 'binds'));
    }

    public function testFindAllReturnsAllRecords(): void
    {
        $users = $this->createModel(UserModel::class)->findAll();
        $this->assertCount(4, $users);
    }

    public function testFindAllRespectsLimits(): void
    {
        $users = $this->createModel(UserModel::class)->findAll(2);
        $this->assertCount(2, $users);
        $this->assertSame('Derek Jones', $users[0]->name);
    }

    public function testFindAllRespectsLimitsAndOffset(): void
    {
        $users = $this->createModel(UserModel::class)->findAll(2, 2);
        $this->assertCount(2, $users);
        $this->assertSame('Richard A Causey', $users[0]->name);
    }

    public function testFindAllRespectsSoftDeletes(): void
    {
        $this->db->table('user')
            ->where('id', 4)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $this->createModel(UserModel::class);

        $user = $this->model->findAll();
        $this->assertCount(3, $user);

        $user = $this->model->withDeleted()->findAll();
        $this->assertCount(4, $user);
    }

    public function testFirst(): void
    {
        $user  = $this->createModel(UserModel::class)->where('id >', 2)->first();
        $count = is_object($user) ? 1 : 0;
        $this->assertSame(1, $count);
        $this->assertSame(3, (int) $user->id);
    }

    /**
     * @dataProvider provideGroupBy
     */
    public function testFirstAggregate($groupBy, $total): void
    {
        $this->createModel(UserModel::class);

        if ($groupBy) {
            $this->model->groupBy('id');
        }

        $user = $this->model->select('SUM(id) as total')->where('id >', 2)->first();
        $this->assertSame($total, (int) $user->total);
    }

    public static function provideGroupBy(): iterable
    {
        return [
            [true, 3],
            [false, 7],
        ];
    }

    /**
     * @dataProvider provideAggregateAndGroupBy
     */
    public function testFirstRespectsSoftDeletes($aggregate, $groupBy): void
    {
        $this->db->table('user')
            ->where('id', 1)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $this->createModel(UserModel::class);

        if ($aggregate) {
            $this->model->select('SUM(id) as id');
        }

        if ($groupBy) {
            $this->model->groupBy('id');
        }

        $user = $this->model->first();

        if (! $aggregate || $groupBy) {
            $count = is_object($user) ? 1 : 0;
            $this->assertSame(1, $count);
            $this->assertSame(2, (int) $user->id);
        } else {
            $this->assertSame(9, (int) $user->id);
        }

        $user = $this->model->withDeleted()->first();
        $this->assertSame(1, (int) $user->id);
    }

    /**
     * @dataProvider provideAggregateAndGroupBy
     */
    public function testFirstRecoverTempUseSoftDeletes($aggregate, $groupBy): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        if ($aggregate) {
            $this->model->select('sum(id) as id');
        }

        if ($groupBy) {
            $this->model->groupBy('id');
        }

        $user = $this->model->withDeleted()->first();
        $this->assertSame(1, (int) $user->id);

        $user2 = $this->model->first();
        $this->assertSame(2, (int) $user2->id);
    }

    public static function provideAggregateAndGroupBy(): iterable
    {
        return [
            [true, true],
            [false, false],
            [true, false],
            [false, true],
        ];
    }

    public function testFirstWithNoPrimaryKey(): void
    {
        $this->createModel(SecondaryModel::class);

        $this->db->table('secondary')->insert([
            'key'   => 'foo',
            'value' => 'bar',
        ]);

        $this->db->table('secondary')->insert([
            'key'   => 'bar',
            'value' => 'baz',
        ]);

        $record = $this->model->first();
        $this->assertInstanceOf('stdClass', $record);
        $this->assertSame('foo', $record->key);
    }

    public function testThrowsWithNoPrimaryKey(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('`Tests\Support\Models\UserModel` model class does not specify a Primary Key.');

        $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'primaryKey', '');
        $this->model->find(1);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
     */
    public function testSoftDeleteWithTableJoinsFindAll(): void
    {
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

        $results = $this->createModel(UserModel::class)->join('job', 'job.id = user.id')->findAll();
        $this->assertCount(4, $results);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
     */
    public function testSoftDeleteWithTableJoinsFind(): void
    {
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

        $results = $this->createModel(UserModel::class)->join('job', 'job.id = user.id')->find(1);
        $this->assertSame(1, (int) $results->id);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1881
     */
    public function testSoftDeleteWithTableJoinsFirst(): void
    {
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at' => null]);

        $results = $this->createModel(UserModel::class)->join('job', 'job.id = user.id')->first(1);
        $this->assertSame(1, (int) $results->id);
    }
}
