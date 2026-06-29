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
use Tests\Support\Entity\User;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class InsertOnlyFieldsModelTest extends LiveModelTestCase
{
    public function testInsertAllowsInsertOnlyFields(): void
    {
        $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->insert([
            'name'    => 'Insert Only',
            'email'   => 'insert-only@example.com',
            'country' => 'US',
        ]);

        $this->seeInDatabase('user', [
            'email' => 'insert-only@example.com',
        ]);
    }

    public function testInsertBatchAllowsInsertOnlyFields(): void
    {
        $result = $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->insertBatch([
            [
                'name'    => 'Insert Only Batch',
                'email'   => 'insert-only-batch@example.com',
                'country' => 'US',
            ],
        ]);

        $this->assertSame(1, $result);
        $this->seeInDatabase('user', [
            'email' => 'insert-only-batch@example.com',
        ]);
    }

    public function testUpdateThrowsOnInsertOnlyFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields cannot be updated for model "Tests\Support\Models\UserModel": email');

        $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->update(1, [
            'name'  => 'Insert Only Update',
            'email' => 'insert-only-update@example.com',
        ]);
    }

    public function testSaveUpdateThrowsOnInsertOnlyFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields cannot be updated for model "Tests\Support\Models\UserModel": email');

        $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->save([
            'id'    => 1,
            'name'  => 'Insert Only Save',
            'email' => 'insert-only-save@example.com',
        ]);
    }

    public function testSetUpdateThrowsOnInsertOnlyFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields cannot be updated for model "Tests\Support\Models\UserModel": email');

        $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])
            ->where('id', 1)
            ->set('email', 'insert-only-set@example.com')
            ->update(null, ['name' => 'Insert Only Set']);
    }

    public function testUpdateBatchThrowsOnInsertOnlyFields(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields cannot be updated for model "Tests\Support\Models\UserModel": email');

        $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->updateBatch([
            [
                'id'    => 1,
                'name'  => 'Insert Only Batch',
                'email' => 'insert-only-update-batch@example.com',
            ],
        ], 'id');
    }

    public function testUpdateBatchAllowsInsertOnlyFieldAsIndex(): void
    {
        $result = $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->updateBatch([
            [
                'email' => 'derek@world.com',
                'name'  => 'Insert Only Batch Index',
            ],
        ], 'email');

        $this->assertSame(1, $result);
        $this->seeInDatabase('user', [
            'email' => 'derek@world.com',
            'name'  => 'Insert Only Batch Index',
        ]);
    }

    public function testEntityUpdateThrowsOnChangedInsertOnlyFields(): void
    {
        $model = new class ($this->db) extends UserModel {
            protected $returnType       = User::class;
            protected $insertOnlyFields = ['email'];
        };

        $user = $model->find(1);
        $this->assertInstanceOf(User::class, $user);

        $user->email = 'insert-only-entity@example.com';

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Fields cannot be updated for model "' . $model::class . '": email');

        $model->update($user->id, $user);
    }

    public function testEntityUpdateAllowsUnchangedInsertOnlyFields(): void
    {
        $model = new class ($this->db) extends UserModel {
            protected $returnType       = User::class;
            protected $insertOnlyFields = ['email'];
        };

        $user = $model->find(1);
        $this->assertInstanceOf(User::class, $user);

        $user->name = 'Insert Only Entity';

        $this->assertTrue($model->update($user->id, $user));
        $this->seeInDatabase('user', [
            'id'   => 1,
            'name' => 'Insert Only Entity',
        ]);
    }

    public function testProtectFalseBypassesInsertOnlyFields(): void
    {
        $result = $this->createModel(UserModel::class)->setInsertOnlyFields(['email'])->protect(false)->update(1, [
            'email' => 'insert-only-disabled@example.com',
        ]);

        $this->assertTrue($result);
        $this->seeInDatabase('user', [
            'id'    => 1,
            'email' => 'insert-only-disabled@example.com',
        ]);
    }

    public function testValidationRunsBeforeInsertOnlyFields(): void
    {
        $model = $this->createModel(ValidModel::class)->setInsertOnlyFields(['description']);
        $this->setPrivateProperty($model, 'cleanValidationRules', false);

        $this->assertFalse($model->update(1, [
            'description' => 'Insert only description',
        ]));
        $this->assertArrayHasKey('name', $model->errors());
    }
}
