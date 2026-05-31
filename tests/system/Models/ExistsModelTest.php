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

use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExistsModelTest extends LiveModelTestCase
{
    public function testExistsByIdReturnsTrueForExistingPrimaryKey(): void
    {
        $this->createModel(UserModel::class);

        $this->assertTrue($this->model->existsById(1));
    }

    public function testExistsByIdReturnsFalseForMissingPrimaryKey(): void
    {
        $this->createModel(UserModel::class);

        $this->assertFalse($this->model->existsById(999));
    }

    public function testExistsByIdRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertFalse($this->model->existsById(1));
        $this->assertTrue($this->model->withDeleted()->existsById(1));
    }

    public function testExistsByIdWorksWithCurrentModelQuery(): void
    {
        $this->createModel(UserModel::class);

        $this->assertTrue($this->model->where('country', 'US')->existsById(1));
        $this->assertFalse($this->model->where('country', 'UK')->existsById(1));
    }

    public function testExistsByIdDoesNotTriggerFindCallbacks(): void
    {
        $model = $this->createModel(EventModel::class);

        $this->assertTrue($model->existsById(1));
        $this->assertFalse($model->hasToken('beforeFind'));
        $this->assertFalse($model->hasToken('afterFind'));
    }

    public function testExistsRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertFalse($this->model->where('id', 1)->exists());
        $this->assertTrue($this->model->withDeleted()->where('id', 1)->exists());
    }

    public function testDoesntExistRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertTrue($this->model->where('id', 1)->doesntExist());
        $this->assertFalse($this->model->withDeleted()->where('id', 1)->doesntExist());
    }
}
