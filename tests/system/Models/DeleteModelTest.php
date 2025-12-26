<?php

declare(strict_types=1);

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
use CodeIgniter\Database\RawSql;
use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Exceptions\ModelException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\StringifyPkeyModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
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

    public function testDeleteWithRawSql(): void
    {
        $this->createModel(JobModel::class);
        $this->seeInDatabase('job', ['name' => 'Developer']);

        // RawSql objects should be allowed as primary key values
        $result = $this->model->delete(new RawSql('1'));
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
     * When executing a soft delete with where() clause
     * Then an exception should not be thrown
     *
     * This test uses where() so values go into WHERE clause, not through validateID().
     *
     * @param int|string|null $emptyValue
     */
    #[DataProvider('provideDontThrowExceptionWhenSoftDeleteConditionIsSetWithEmptyValue')]
    public function testDontThrowExceptionWhenSoftDeleteConditionIsSetWithEmptyValue($emptyValue): void
    {
        $this->createModel(UserModel::class);

        if ($this->db->DBDriver === 'Postgre' && in_array($emptyValue, ['', true, false], true)) {
            $this->markTestSkipped('PostgreSQL does not allow empty string, true, or false for integer columns');
        }

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->model->where('id', $emptyValue)->delete();
        // Special case: true converted to 1
        if ($emptyValue === true) {
            $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NOT NULL' => null]);
        } else {
            $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
        }
    }

    /**
     * Data provider for tests using where() clause.
     * These values go into WHERE clause, not through validateID().
     *
     * @return iterable<array{bool|int|string|null}>
     */
    public static function provideDontThrowExceptionWhenSoftDeleteConditionIsSetWithEmptyValue(): iterable
    {
        return [
            [0],
            [null],
            ['0'],
            [''],
            [true],
            [false],
        ];
    }

    /**
     * @param int|string|null $emptyValue
     * @param class-string    $exception
     */
    #[DataProvider('emptyPkValues')]
    public function testThrowExceptionWhenSoftDeleteParamIsEmptyValue($emptyValue, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        $this->createModel(UserModel::class)->delete($emptyValue);
    }

    /**
     * @param int|string|null $emptyValue
     * @param class-string    $exception
     */
    #[DataProvider('emptyPkValues')]
    public function testDontDeleteRowsWhenSoftDeleteParamIsEmpty($emptyValue, string $exception, string $exceptionMessage): void
    {
        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);

        try {
            $this->createModel(UserModel::class)->delete($emptyValue);
        } catch (DatabaseException|InvalidArgumentException) {
            // Do nothing - both exceptions are expected for different values.
        }

        $this->seeInDatabase('user', ['name' => 'Derek Jones', 'deleted_at IS NULL' => null]);
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
     * @param int|string|null $id
     * @param class-string    $exception
     */
    #[DataProvider('emptyPkValues')]
    public function testDeleteThrowDatabaseExceptionWithoutWhereClause($id, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        // $useSoftDeletes = false
        $this->createModel(JobModel::class);

        $this->model->delete($id);
    }

    /**
     * @param int|string|null $id
     * @param class-string    $exception
     */
    #[DataProvider('emptyPkValues')]
    public function testDeleteWithSoftDeleteThrowDatabaseExceptionWithoutWhereClause($id, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        // $useSoftDeletes = true
        $this->createModel(UserModel::class);

        $this->model->delete($id);
    }

    /**
     * @return iterable<string, array{mixed, class-string, string}>
     */
    public static function emptyPkValues(): iterable
    {
        return [
            'null' => [
                null,
                DatabaseException::class,
                'Deletes are not allowed unless they contain a "where" or "like" clause.',
            ],
            'false' => [
                false,
                InvalidArgumentException::class,
                'Invalid primary key: boolean false is not allowed.',
            ],
            '0 integer' => [
                0,
                InvalidArgumentException::class,
                'Invalid primary key: 0 is not allowed.',
            ],
            "'0' string" => [
                '0',
                InvalidArgumentException::class,
                "Invalid primary key: '0' is not allowed.",
            ],
            'empty string' => [
                '',
                InvalidArgumentException::class,
                "Invalid primary key: '' is not allowed.",
            ],
            'true' => [
                true,
                InvalidArgumentException::class,
                'Invalid primary key: boolean true is not allowed.',
            ],
            'empty array' => [
                [],
                InvalidArgumentException::class,
                'Invalid primary key: cannot be an empty array.',
            ],
            'nested array' => [
                [[1, 2]],
                InvalidArgumentException::class,
                'Invalid primary key at index 0: nested arrays are not allowed.',
            ],
            'array with null' => [
                [1, null, 3],
                InvalidArgumentException::class,
                'Invalid primary key: NULL is not allowed.',
            ],
            'array with 0' => [
                [1, 0, 3],
                InvalidArgumentException::class,
                'Invalid primary key: 0 is not allowed.',
            ],
            "array with '0'" => [
                [1, '0', 3],
                InvalidArgumentException::class,
                "Invalid primary key: '0' is not allowed.",
            ],
            'array with empty string' => [
                [1, '', 3],
                InvalidArgumentException::class,
                "Invalid primary key: '' is not allowed.",
            ],
            'array with boolean' => [
                [1, false, 3],
                InvalidArgumentException::class,
                'Invalid primary key: boolean false is not allowed.',
            ],
        ];
    }
}
