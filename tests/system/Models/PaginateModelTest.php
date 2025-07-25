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
use Tests\Support\Models\ValidModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class PaginateModelTest extends LiveModelTestCase
{
    public function testPaginate(): void
    {
        $data = $this->createModel(ValidModel::class)->paginate();
        $this->assertCount(4, $data);
    }

    public function testPaginateChangeConfigPager(): void
    {
        $perPage = config('Pager')->perPage;

        config('Pager')->perPage = 1;

        $data = $this->createModel(ValidModel::class)->paginate();
        $this->assertCount(1, $data);

        config('Pager')->perPage = $perPage;
    }

    public function testPaginatePassPerPageParameter(): void
    {
        $data = $this->createModel(ValidModel::class)->paginate(2);
        $this->assertCount(2, $data);
    }

    public function testPaginateForQueryWithGroupBy(): void
    {
        $this->createModel(ValidModel::class);
        $this->model->select('id')->groupBy('id');
        $this->model->paginate();
        $this->assertSame(4, $this->model->pager->getDetails()['total']);
    }

    public function testPaginateWithDeleted(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $data = $this->model->withDeleted()->paginate();
        $this->assertCount(4, $data);
        $this->assertSame(4, $this->model->pager->getDetails()['total']);
    }

    public function testPaginateWithoutDeleted(): void
    {
        $this->createModel(UserModel::class);
        $this->model->delete(1);

        $data = $this->model->withDeleted(false)->paginate();
        $this->assertCount(3, $data);
        $this->assertSame(3, $this->model->pager->getDetails()['total']);
    }

    public function testPaginatePageOutOfRange(): void
    {
        $this->createModel(ValidModel::class);
        $this->model->paginate(1, 'default', -500);
        $this->assertSame(1, $this->model->pager->getCurrentPage());
        $this->model->paginate(1, 'default', 500);
        $this->assertSame($this->model->pager->getPageCount(), $this->model->pager->getCurrentPage());
    }

    public function testMultiplePager(): void
    {
        $_GET = [];

        $validModel = $this->createModel(ValidModel::class);
        $userModel  = $this->createModel(UserModel::class);

        $validModel->paginate(1, 'valid');
        $userModel->paginate(1, 'user');
        $pager = $this->model->pager;

        $this->assertSame($userModel->pager, $validModel->pager);

        $this->assertSame(4, $validModel->countAllResults());
        $this->assertSame(4, $userModel->countAllResults());

        $this->assertStringContainsString('?page_valid=1"', (string) $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=2"', (string) $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=3"', (string) $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=4"', (string) $pager->links('valid'));
        $this->assertStringContainsString('?page_user=1"', (string) $pager->links('user'));
        $this->assertStringContainsString('?page_user=2"', (string) $pager->links('user'));
        $this->assertStringContainsString('?page_user=3"', (string) $pager->links('user'));
        $this->assertStringContainsString('?page_user=4"', (string) $pager->links('user'));
    }
}
