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
use Tests\Support\Models\UserWithEventsModel;
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

        $this->assertStringContainsString('?page_valid=1"', $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=2"', $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=3"', $pager->links('valid'));
        $this->assertStringContainsString('?page_valid=4"', $pager->links('valid'));
        $this->assertStringContainsString('?page_user=1"', $pager->links('user'));
        $this->assertStringContainsString('?page_user=2"', $pager->links('user'));
        $this->assertStringContainsString('?page_user=3"', $pager->links('user'));
        $this->assertStringContainsString('?page_user=4"', $pager->links('user'));
    }

    public function testPaginateWithBeforeFindEvents(): void
    {
        $this->createModel(UserWithEventsModel::class);

        $this->seedPaginateEventModel();

        // Test pagination - beforeFind event should filter to only US users
        $data = $this->model->paginate(2);

        // Should only get US users in results
        $this->assertCount(2, $data);
        $this->assertSame(3, $this->model->pager->getDetails()['total']);
        $this->assertSame(2, $this->model->pager->getPageCount());

        // Verify all returned users are from US
        foreach ($data as $user) {
            $this->assertSame('US', $user->country);
        }
    }

    public function testPaginateWithBeforeFindEventsAndDisabledCallbacks(): void
    {
        $this->createModel(UserWithEventsModel::class);

        $this->seedPaginateEventModel();

        $data = $this->model->allowCallbacks(false)->paginate(2);

        // Should get all users
        $this->assertCount(2, $data);
        $this->assertSame(9, $this->model->pager->getDetails()['total']);

        // Should have users from different countries
        $countries = array_unique(array_column($data, 'country'));
        $this->assertGreaterThan(1, count($countries));
    }

    private function seedPaginateEventModel(): void
    {
        $testData = [
            ['name' => 'Jean', 'email' => 'jean@test.com', 'country' => 'France'],
            ['name' => 'Marie', 'email' => 'marie@test.com', 'country' => 'France'],
            ['name' => 'John', 'email' => 'john@test.com', 'country' => 'US'],
            ['name' => 'Hans', 'email' => 'hans@test.com', 'country' => 'Germany'],
            ['name' => 'Luigi', 'email' => 'luigi@test.com', 'country' => 'Italy'],
        ];

        $this->model->insertBatch($testData);
    }
}
