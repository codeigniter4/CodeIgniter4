<?php

namespace CodeIgniter\Models;

use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;

/**
 * @internal
 */
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
        $this->model->groupBy('id');
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
}
