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
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ExistsModelTest extends LiveModelTestCase
{
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
