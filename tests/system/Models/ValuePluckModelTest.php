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
final class ValuePluckModelTest extends LiveModelTestCase
{
    public function testValueRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertNull($this->model->where('id', 1)->value('name'));
        $this->assertSame('Derek Jones', $this->model->withDeleted()->where('id', 1)->value('name'));
    }

    public function testPluckRespectsSoftDeletes(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $this->assertSame(
            ['Ahmadinejad', 'Richard A Causey', 'Chris Martin'],
            $this->model->orderBy('id', 'ASC')->pluck('name'),
        );
        $this->assertSame(
            ['Derek Jones', 'Ahmadinejad', 'Richard A Causey', 'Chris Martin'],
            $this->model->withDeleted()->orderBy('id', 'ASC')->pluck('name'),
        );
    }
}
