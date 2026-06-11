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
final class StrictFieldProtectionModelTest extends LiveModelTestCase
{
    public function testDefaultFieldProtectionStillDiscardsDisallowedFields(): void
    {
        $this->createModel(UserModel::class)->insert([
            'name'     => 'Strict Default',
            'email'    => 'strict-default@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);

        $this->seeInDatabase('user', [
            'email' => 'strict-default@example.com',
        ]);
    }

    public function testStrictFieldProtectionThrowsOnInsertDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->strictFieldProtection()->insert([
            'name'     => 'Strict Insert',
            'email'    => 'strict-insert@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);
    }

    public function testStrictFieldProtectionThrowsOnInsertBatchDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->strictFieldProtection()->insertBatch([
            [
                'name'     => 'Strict Batch',
                'email'    => 'strict-batch@example.com',
                'country'  => 'US',
                'timezone' => 'UTC',
            ],
        ]);
    }

    public function testStrictFieldProtectionThrowsOnUpdateDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->strictFieldProtection()->update(1, [
            'name'     => 'Strict Update',
            'timezone' => 'UTC',
        ]);
    }

    public function testStrictFieldProtectionThrowsOnSaveDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->strictFieldProtection()->save([
            'name'     => 'Strict Save',
            'email'    => 'strict-save@example.com',
            'country'  => 'US',
            'timezone' => 'UTC',
        ]);
    }

    public function testStrictFieldProtectionAllowsPrimaryKeyDuringUpdate(): void
    {
        $result = $this->createModel(UserModel::class)->strictFieldProtection()->update(1, [
            'id'   => 1,
            'name' => 'Strict Primary Key',
        ]);

        $this->assertTrue($result);
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Strict Primary Key',
        ]);
    }

    public function testStrictFieldProtectionAllowsBatchIndexDuringUpdateBatch(): void
    {
        $result = $this->createModel(UserModel::class)->strictFieldProtection()->updateBatch([
            [
                'id'   => 1,
                'name' => 'Strict Batch One',
            ],
            [
                'id'   => 2,
                'name' => 'Strict Batch Two',
            ],
        ], 'id');

        $this->assertSame(2, $result);
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Strict Batch One',
        ]);
        $this->seeInDatabase('user', [
            'id'   => 2,
            'name' => 'Strict Batch Two',
        ]);
    }

    public function testStrictFieldProtectionThrowsOnUpdateBatchDisallowedFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields are not allowed for model "Tests\Support\Models\UserModel": timezone');

        $this->createModel(UserModel::class)->strictFieldProtection()->updateBatch([
            [
                'id'       => 1,
                'name'     => 'Strict Batch',
                'timezone' => 'UTC',
            ],
        ], 'id');
    }

    public function testStrictFieldProtectionAllowsNonAutoIncrementPrimaryKeyDuringInsert(): void
    {
        $result = $this->createModel(WithoutAutoIncrementModel::class)->strictFieldProtection()->insert([
            'key'   => 'strict-key',
            'value' => 'strict value',
        ]);

        $this->assertSame('strict-key', $result);
        $this->seeInDatabase('without_auto_increment', [
            'key'   => 'strict-key',
            'value' => 'strict value',
        ]);
    }

    public function testProtectFalseBypassesStrictFieldProtection(): void
    {
        $result = $this->createModel(UserModel::class)->strictFieldProtection()->protect(false)->update(1, [
            'name'       => 'Strict Disabled',
            'created_at' => '2026-01-01 12:00:00',
        ]);

        $this->assertTrue($result);
    }

    public function testValidationRunsBeforeStrictFieldProtection(): void
    {
        $model = $this->createModel(ValidModel::class)->strictFieldProtection();

        $this->assertFalse($model->insert([
            'description' => 'Missing required name',
            'extra'       => 'discarded after validation',
        ]));
        $this->assertArrayHasKey('name', $model->errors());
    }
}
