<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\ModelException;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\StringifyPkeyModel;
use Tests\Support\Models\UserModel;

/**
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
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        $this->createModel(JobModel::class);
        $this->seeInDatabase('job', ['name' => 'Developer']);

        $result = $this->model->where('name123', 'Developer')->delete();
        $this->assertFalse($result);
        $this->seeInDatabase('job', ['name' => 'Developer']);
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
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NOT NULL' => null]);
    }

    public function testDeleteWithSoftDeleteFail(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        $this->createModel(UserModel::class);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $result = $this->model->where('name123', 'Derek Jones')->delete();
        $this->assertFalse($result);
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
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
     * If where condition is set, beyond the value was empty (0,'', NULL, etc.),
     * Exception should not be thrown because condition was explicity set
     *
     * @dataProvider emptyPkValues
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
     */
    public function testDontDeleteRowsWhenSoftDeleteParamIsEmpty($emptyValue): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('Deletes are not allowed unless they contain a "where" or "like" clause.');

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->createModel(UserModel::class)->delete($emptyValue);
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
        $this->expectExceptionMessage('`Tests\Support\Models\UserModel` model class does not have a valid dateFormat.');

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
}
