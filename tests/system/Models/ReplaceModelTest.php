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

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\I18n\Time;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ReplaceModelTest extends LiveModelTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        Time::setTestNow();
    }

    public function testReplaceRespectsUseTimestamps(): void
    {
        $this->createModel(UserModel::class);

        $data = [
            'name'    => 'Amanda Holmes',
            'email'   => 'amanda@holmes.com',
            'country' => 'US',
        ];

        $id = $this->model->insert($data);

        $data['id']      = $id;
        $data['country'] = 'UK';

        $sql = $this->model->replace($data, true);
        $this->assertStringNotContainsString('created_at', (string) $sql);
        $this->assertStringNotContainsString('updated_at', (string) $sql);

        $this->model = $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $sql = $this->model->replace($data, true);
        $this->assertStringContainsString('created_at', (string) $sql);
        $this->assertStringContainsString('updated_at', (string) $sql);
    }

    public function testReplaceProtectsFieldsAndPreservesPrimaryKey(): void
    {
        $model = $this->createModel(JobModel::class);
        $id    = $model->insert(['name' => 'Original', 'description' => null]);

        $model->setAllowedFields(['name']);

        $this->assertNotFalse($model->replace([
            'id'          => $id,
            'name'        => 'Replacement',
            'description' => 'Protected value',
        ]));

        $job = $model->find($id);

        $this->assertNotNull($job);
        $this->assertSame('Replacement', $job->name);
        $this->assertNull($job->description);
        $this->dontSeeInDatabase('job', ['name' => 'Original']);
    }

    public function testReplaceProtectsFieldsWhenReturningSQL(): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields(['name']);

        $sql = $model->replace([
            'id'          => 1,
            'name'        => 'Replacement',
            'description' => 'Protected value',
        ], true);

        $this->assertIsString($sql);
        $this->assertStringNotContainsString('description', $sql);
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'Developer']);
    }

    public function testReplaceWithProtectionDisabled(): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields([])->protect(false);

        $this->assertNotFalse($model->replace([
            'id'          => 1,
            'name'        => 'Replacement',
            'description' => 'Explicitly allowed',
        ]));

        $this->seeInDatabase('job', [
            'id'          => 1,
            'name'        => 'Replacement',
            'description' => 'Explicitly allowed',
        ]);
    }

    public function testReplaceWithoutAllowedFields(): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields([]);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Allowed fields must be specified for model: "Tests\\Support\\Models\\JobModel"');

        $model->replace(['id' => 1, 'name' => 'Replacement']);
    }

    public function testReplacePreservesCustomPrimaryKey(): void
    {
        $model = $this->createModel(WithoutAutoIncrementModel::class);

        $this->assertNotFalse($model->replace([
            'key'         => 'key',
            'value'       => 'Replacement',
            'not_allowed' => 'Protected value',
        ]));

        $this->seeInDatabase('without_auto_increment', ['key' => 'key', 'value' => 'Replacement']);
    }

    #[DataProvider('provideReplacePreservesFieldOrder')]
    public function testReplacePreservesFieldOrder(bool $primaryKeyFirst): void
    {
        $model = new class ($this->db) extends UserModel {
            protected function doReplace(?array $row = null, bool $returnSQL = false): string
            {
                return implode(',', array_keys((array) $row));
            }
        };

        $data = $primaryKeyFirst
            ? ['id' => 1, 'name' => 'Replacement']
            : ['name' => 'Replacement', 'id' => 1];
        $data['not_allowed'] = 'Protected value';

        $this->assertSame(
            $primaryKeyFirst ? 'id,name' : 'name,id',
            $model->replace($data, true),
        );
    }

    /**
     * @return iterable<string, array{bool}>
     */
    public static function provideReplacePreservesFieldOrder(): iterable
    {
        yield 'primary key first' => [true];

        yield 'primary key last' => [false];
    }

    public function testReplaceUsesCustomFieldProtection(): void
    {
        $model = new class ($this->db) extends JobModel {
            protected function doProtectFields(array $row): array
            {
                $row = parent::doProtectFields($row);

                if (isset($row['name'])) {
                    $row['name'] = strtoupper($row['name']);
                }

                return $row;
            }
        };

        $this->assertNotFalse($model->replace(['id' => 1, 'name' => 'Replacement']));
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'REPLACEMENT']);
    }

    public function testReplaceFiltersInputTimestampsBeforeGeneratingTimestamps(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $model = $this->createModel(JobModel::class);
        $this->setPrivateProperty($model, 'useTimestamps', true);

        $this->assertNotFalse($model->replace([
            'id'         => 1,
            'name'       => 'Replacement',
            'created_at' => 1,
            'updated_at' => 2,
        ]));

        $this->seeInDatabase('job', [
            'id'         => 1,
            'created_at' => Time::now()->getTimestamp(),
            'updated_at' => Time::now()->getTimestamp(),
        ]);
    }

    public function testReplaceValidatesBeforeProtectingFields(): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields(['name']);
        $model->setValidationRules(['description' => 'in_list[Valid]']);

        $this->assertFalse($model->replace([
            'id'          => 1,
            'name'        => 'Replacement',
            'description' => 'Invalid',
        ]));
        $this->assertArrayHasKey('description', $model->errors());
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'Developer']);
    }

    /**
     * @param array{}|null $data
     */
    #[DataProvider('provideReplaceWithEmptyDataUsesBuilderData')]
    public function testReplaceWithEmptyDataUsesBuilderData(?array $data): void
    {
        $model = $this->createModel(JobModel::class);
        $model->builder()->set(['id' => 1, 'name' => 'Replacement']);

        $this->assertNotFalse($model->replace($data));
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'Replacement']);
    }

    /**
     * @return iterable<string, array{array{}|null}>
     */
    public static function provideReplaceWithEmptyDataUsesBuilderData(): iterable
    {
        yield 'null' => [null];

        yield 'empty array' => [[]];
    }

    public function testReplaceWithEmptyDataCanGenerateTimestamps(): void
    {
        $model = $this->createModel(JobModel::class);
        $this->setPrivateProperty($model, 'useTimestamps', true);

        $sql = $model->replace([], true);

        $this->assertIsString($sql);
        $this->assertStringContainsString('created_at', $sql);
        $this->assertStringContainsString('updated_at', $sql);
    }

    public function testReplaceRejectsEmptyDataFromCustomProtectionBeforeTimestamps(): void
    {
        $model = new class ($this->db) extends JobModel {
            protected function doProtectFieldsForReplace(array $row): array
            {
                return $this->doProtectFields($row);
            }
        };
        $model->setAllowedFields(['name']);
        $this->setPrivateProperty($model, 'useTimestamps', true);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to replace.');

        $model->replace(['description' => 'Protected value'], true);
    }

    #[DataProvider('provideReplaceWithOnlyProtectedFieldsHasNoData')]
    public function testReplaceWithOnlyProtectedFieldsHasNoData(bool $useTimestamps, bool $returnSQL): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields(['name']);
        $this->setPrivateProperty($model, 'useTimestamps', $useTimestamps);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to replace.');

        $model->replace(['description' => 'Protected value'], $returnSQL);
    }

    /**
     * @return iterable<string, array{bool, bool}>
     */
    public static function provideReplaceWithOnlyProtectedFieldsHasNoData(): iterable
    {
        yield 'without timestamps, execute' => [false, false];

        yield 'with timestamps, execute' => [true, false];

        yield 'without timestamps, return SQL' => [false, true];

        yield 'with timestamps, return SQL' => [true, true];
    }

    #[DataProvider('provideReplaceWithOnlyPrimaryKeyAfterProtectionHasNoData')]
    public function testReplaceWithOnlyPrimaryKeyAfterProtectionHasNoData(bool $useTimestamps, bool $allowPrimaryKey): void
    {
        $model = $this->createModel(JobModel::class);
        $model->setAllowedFields($allowPrimaryKey ? ['id', 'name'] : ['name']);
        $this->setPrivateProperty($model, 'useTimestamps', $useTimestamps);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to replace.');

        $model->replace(['id' => 1, 'description' => 'Protected value'], true);
    }

    /**
     * @return iterable<string, array{bool, bool}>
     */
    public static function provideReplaceWithOnlyPrimaryKeyAfterProtectionHasNoData(): iterable
    {
        yield 'without timestamps, primary key protected' => [false, false];

        yield 'with timestamps, primary key protected' => [true, false];

        yield 'without timestamps, primary key allowed' => [false, true];

        yield 'with timestamps, primary key allowed' => [true, true];
    }

    public function testReplaceWithExplicitPrimaryKeyOnlyStillReturnsSQL(): void
    {
        $model = $this->createModel(JobModel::class);

        $this->assertIsString($model->replace(['id' => 1], true));
    }

    public function testReplaceAfterRejectedProtectedData(): void
    {
        $model = $this->createModel(JobModel::class)->setAllowedFields(['name']);
        $this->setPrivateProperty($model, 'useTimestamps', true);

        try {
            $model->replace(['id' => 1, 'description' => 'Protected value']);
            $this->fail('Expected an exception for an empty dataset after field protection.');
        } catch (DataException $e) {
            $this->assertSame('There is no data to replace.', $e->getMessage());
        }

        $this->seeInDatabase('job', ['id' => 1, 'name' => 'Developer']);
        $this->assertNotFalse($model->replace(['id' => 1, 'name' => 'Replacement']));
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'Replacement']);
    }
}
