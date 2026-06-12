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
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ThrowOnDisallowedFieldsModelTest extends LiveModelTestCase
{
    public function testDefaultFieldProtectionStillDiscardsDisallowedFields(): void
    {
        $this->createModel(UserModel::class)->insert([
            'name'     => 'Disallowed Default',
            'email'    => 'disallowed-default@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);

        $this->seeInDatabase('user', [
            'email' => 'disallowed-default@example.com',
        ]);
    }

    public function testThrowOnDisallowedFieldsThrowsOnInsertDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->throwOnDisallowedFields()->insert([
            'name'     => 'Disallowed Insert',
            'email'    => 'disallowed-insert@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);
    }

    public function testThrowOnDisallowedFieldsThrowsOnInsertBatchDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->throwOnDisallowedFields()->insertBatch([
            [
                'name'     => 'Disallowed Batch',
                'email'    => 'disallowed-batch@example.com',
                'country'  => 'US',
                'timezone' => 'UTC',
            ],
        ]);
    }

    public function testThrowOnDisallowedFieldsThrowsOnUpdateDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->throwOnDisallowedFields()->update(1, [
            'name'     => 'Disallowed Update',
            'timezone' => 'UTC',
        ]);
    }

    public function testThrowOnDisallowedFieldsThrowsOnSaveDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->throwOnDisallowedFields()->save([
            'name'     => 'Disallowed Save',
            'email'    => 'disallowed-save@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);
    }

    public function testThrowOnDisallowedFieldsAllowsPrimaryKeyDuringUpdate(): void
    {
        $result = $this->createModel(UserModel::class)->throwOnDisallowedFields()->update(1, [
            'id'   => 1,
            'name' => 'Disallowed Primary Key',
        ]);

        $this->assertTrue($result);
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Disallowed Primary Key',
        ]);
    }

    public function testThrowOnDisallowedFieldsAllowsPrimaryKeyDuringConditionalUpdate(): void
    {
        $result = $this->createModel(UserModel::class)->throwOnDisallowedFields()
            ->where('id', 1)
            ->update(null, [
                'id'   => 1,
                'name' => 'Disallowed Conditional Primary Key',
            ]);

        $this->assertTrue($result);
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Disallowed Conditional Primary Key',
        ]);
    }

    public function testThrowOnDisallowedFieldsAllowsBatchIndexDuringUpdateBatch(): void
    {
        $result = $this->createModel(UserModel::class)->throwOnDisallowedFields()->updateBatch([
            [
                'id'   => 1,
                'name' => 'Disallowed Batch One',
            ],
            [
                'id'   => 2,
                'name' => 'Disallowed Batch Two',
            ],
        ], 'id');

        $this->assertSame(2, $result);
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Disallowed Batch One',
        ]);
        $this->seeInDatabase('user', [
            'id'   => 2,
            'name' => 'Disallowed Batch Two',
        ]);
    }

    public function testThrowOnDisallowedFieldsThrowsOnUpdateBatchDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->throwOnDisallowedFields()->updateBatch([
            [
                'id'       => 1,
                'name'     => 'Disallowed Batch',
                'timezone' => 'UTC',
            ],
        ], 'id');
    }

    public function testThrowOnDisallowedFieldsAllowsNonAutoIncrementPrimaryKeyDuringInsert(): void
    {
        $result = $this->createModel(WithoutAutoIncrementModel::class)->throwOnDisallowedFields()->insert([
            'key'   => 'disallowed-key',
            'value' => 'disallowed value',
        ]);

        $this->assertSame('disallowed-key', $result);
        $this->seeInDatabase('without_auto_increment', [
            'key'   => 'disallowed-key',
            'value' => 'disallowed value',
        ]);
    }

    public function testProtectFalseBypassesThrowOnDisallowedFields(): void
    {
        $result = $this->createModel(UserModel::class)->throwOnDisallowedFields()->protect(false)->update(1, [
            'name'       => 'Disallowed Disabled',
            'created_at' => '2026-01-01 12:00:00',
        ]);

        $this->assertTrue($result);
    }

    public function testValidationRunsBeforeThrowOnDisallowedFields(): void
    {
        $model = $this->createModel(ValidModel::class)->throwOnDisallowedFields();

        $this->assertFalse($model->insert([
            'description' => 'Missing required name',
            'extra'       => 'discarded after validation',
        ]));
        $this->assertArrayHasKey('name', $model->errors());
    }
}
