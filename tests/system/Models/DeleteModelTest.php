<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\ModelException;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\StringifyPkeyModel;
use Tests\Support\Models\UserModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DeleteModelTest extends LiveModelTestCase
{
    public function testDeleteBasics(): void
    {
        $this->createModel(JobModel::class);
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $result = $this->model->delete(1);
        $this->assertTrue($result);
        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testDeleteFail(): void
    {
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        $this->createModel(JobModel::class);

        $this->seeInDatabase('job', ['name' => 'Developer']);

        $result = $this->model->where('name123', 'Developer')->delete();

        $this->assertFalse($result);
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->enableDBDebug();
    }

    public function testDeleteStringPrimaryKey(): void
    {
        $this->createModel(StringifyPkeyModel::class);
        $this->seeInDatabase('stringifypkey', ['value' => 'test']);

        $this->model->delete('A01');
        $this->dontSeeInDatabase('stringifypkey', ['value' => 'test']);
    }

    public function testDeleteWithSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $result = $this->model->delete(1);
        $this->assertTrue($result);
        $this->assertSame(1, $this->db->affectedRows());
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NOT NULL' => null]);

        $this->model->delete(1);
        $this->assertSame(0, $this->db->affectedRows());
    }

    public function testDeleteWithSoftDeleteFail(): void
    {
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        $this->createModel(UserModel::class);

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $result = $this->model->where('name123', 'Derek Jones')->delete();

        $this->assertFalse($result);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->enableDBDebug();
    }

    public function testDeleteWithSoftDeletesPurge(): void
    {
        $this->createModel(UserModel::class);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->model->delete(1, true);
        $this->dontSeeInDatabase('user', ['name' => 'Derek Jones']);
    }

    public function testDeleteMultiple(): void
    {
        $this->createModel(JobModel::class);
        $this->seeInDatabase('job', ['name' => 'Developer']);
        $this->seeInDatabase('job', ['name' => 'Politician']);

        $this->model->delete([1, 2]);
        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
        $this->dontSeeInDatabase('job', ['name' => 'Politician']);
        $this->seeInDatabase('job', ['name' => 'Accountant']);
    }

    public function testDeleteNoParams(): void
    {
        $this->createModel(JobModel::class);
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $this->model->where('id', 1)->delete();
        $this->dontSeeInDatabase('job', ['name' => 'Developer']);
    }

    public function testPurgeDeleted(): void
    {
        $this->createModel(UserModel::class);

        $this->db->table('user')
            ->where('id', 1)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $this->model->purgeDeleted();

        $users = $this->model->withDeleted()->findAll();
        $this->assertCount(3, $users);
    }

    public function testOnlyDeleted(): void
    {
        $this->createModel(UserModel::class);

        $this->db->table('user')
            ->where('id', 1)
            ->update(['deleted_at' => date('Y-m-d H:i:s')]);

        $users = $this->model->onlyDeleted()->findAll();
        $this->assertCount(1, $users);
    }

    /**
     * Given an explicit empty value in the WHERE condition
     * When executing a soft delete
     * Then an exception should not be thrown
     *
     * @dataProvider emptyPkValues
     *
     * @param int|string|null $emptyValue
     */
    public function testDontThrowExceptionWhenSoftDeleteConditionIsSetWithEmptyValue($emptyValue): void
    {
        $this->createModel(UserModel::class);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->model->where('id', $emptyValue)->delete();
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
    }

    /**
     * @dataProvider emptyPkValues
     *
     * @param int|string|null $emptyValue
     */
    public function testThrowExceptionWhenSoftDeleteParamIsEmptyValue($emptyValue): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Deletes are not allowed unless they contain a "where" or "like" clause.');

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->createModel(UserModel::class)->delete($emptyValue);
    }

    /**
     * @dataProvider emptyPkValues
     *
     * @param int|string|null $emptyValue
     */
    public function testDontDeleteRowsWhenSoftDeleteParamIsEmpty($emptyValue): void
    {
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        try {
            $this->createModel(UserModel::class)->delete($emptyValue);
        } catch (DatabaseException $e) {
            // Do nothing.
        }

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
    }

    public static function emptyPkValues(): iterable
    {
        return [
            [0],
            [null],
            ['0'],
        ];
    }

    public function testThrowsWithNoDateFormat(): void
    {
        $this->expectException(ModelException::class);
        $this->expectExceptionMessage('"Tests\Support\Models\UserModel" model class does not have a valid dateFormat.');

        $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'dateFormat', '');
        $this->model->delete(1);
    }

    public function testDeleteWithSoftDelete(): void
    {
        $this->createModel(JobModel::class);
        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->setPrivateProperty($this->model, 'useSoftDeletes', true);

        $this->model->delete(1);
        $this->seeInDatabase('job', ['id' => 1, 'deleted_at IS NOT NULL' => null]);
    }

    public function testPurgeDeletedWithSoftDeleteFalse(): void
    {
        $this->db->table('job')
            ->where('id', 1)
            ->update(['deleted_at' => time()]);

        $this->createModel(JobModel::class);
        $this->model->purgeDeleted();

        $jobs = $this->model->findAll();
        $this->assertCount(4, $jobs);
    }

    /**
     * @dataProvider emptyPkValues
     *
     * @param int|string|null $id
     */
    public function testDeleteThrowDatabaseExceptionWithoutWhereClause($id): void
    {
        // BaseBuilder throws Exception.
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(
            'Deletes are not allowed unless they contain a "where" or "like" clause.'
        );

        // $useSoftDeletes = false
        $this->createModel(JobModel::class);

        $this->model->delete($id);
    }

    /**
     * @dataProvider emptyPkValues
     *
     * @param int|string|null $id
     */
    public function testDeleteWithSoftDeleteThrowDatabaseExceptionWithoutWhereClause($id): void
    {
        // Model throws Exception.
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(
            'Deletes are not allowed unless they contain a "where" or "like" clause.'
        );

        // $useSoftDeletes = true
        $this->createModel(UserModel::class);

        $this->model->delete($id);
    }
}
