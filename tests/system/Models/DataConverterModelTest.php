<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Models;

use CodeIgniter\I18n\Time;
use Tests\Support\Entity\CustomUser;
use Tests\Support\Models\UserCastsTimestampModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DataConverterModelTest extends LiveModelTestCase
{
    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $seed        = '';

    public function testFindAsArray(): void
    {
        $id = $this->prepareOneRecord();

        $user = $this->model->find($id);

        $this->assertIsInt($user['id']);
        $this->assertInstanceOf(Time::class, $user['created_at']);
    }

    /**
     * @return int|string Insert ID
     */
    private function prepareOneRecord(): int|string
    {
        $this->createModel(UserCastsTimestampModel::class);
        $this->db->table('user')->truncate();

        $data = [
            'name'    => 'John Smith',
            'email'   => 'john@example.com',
            'country' => 'US',
        ];

        return $this->model->insert($data, true);
    }

    public function testFindAsObject(): void
    {
        $id = $this->prepareOneRecord();

        $user = $this->model->asObject()->find($id);

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }

    public function testFindAsCustomObject(): void
    {
        $id = $this->prepareOneRecord();

        $user = $this->model->asObject(CustomUser::class)->find($id);

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }

    public function testFindAllAsArray(): void
    {
        $this->prepareTwoRecords();

        $users = $this->model->findAll();

        $this->assertIsInt($users[0]['id']);
        $this->assertInstanceOf(Time::class, $users[0]['created_at']);
        $this->assertIsInt($users[1]['id']);
        $this->assertInstanceOf(Time::class, $users[1]['created_at']);
    }

    private function prepareTwoRecords(): void
    {
        $this->prepareOneRecord();

        $data = [
            'name'    => 'Mike Smith',
            'email'   => 'mike@example.com',
            'country' => 'CA',
        ];
        $this->model->insert($data);
    }

    public function testFindAllAsObject(): void
    {
        $this->prepareTwoRecords();

        $users = $this->model->asObject()->findAll();

        $this->assertIsInt($users[0]->id);
        $this->assertInstanceOf(Time::class, $users[0]->created_at);
        $this->assertIsInt($users[1]->id);
        $this->assertInstanceOf(Time::class, $users[1]->created_at);
    }

    public function testFindAllAsCustomObject(): void
    {
        $this->prepareTwoRecords();

        $users = $this->model->asObject(CustomUser::class)->findAll();

        $this->assertIsInt($users[0]->id);
        $this->assertInstanceOf(Time::class, $users[0]->created_at);
        $this->assertIsInt($users[1]->id);
        $this->assertInstanceOf(Time::class, $users[1]->created_at);
    }

    public function testFindColumn(): void
    {
        $this->prepareTwoRecords();

        $users = $this->model->findColumn('created_at');

        $this->assertInstanceOf(Time::class, $users[0]);
        $this->assertInstanceOf(Time::class, $users[1]);
    }

    public function testFirstAsArray(): void
    {
        $this->prepareTwoRecords();

        $user = $this->model->first();

        $this->assertIsInt($user['id']);
        $this->assertInstanceOf(Time::class, $user['created_at']);
    }

    public function testFirstAsObject(): void
    {
        $this->prepareTwoRecords();

        $user = $this->model->asObject()->first();

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }

    public function testFirstAsCustomObject(): void
    {
        $this->prepareTwoRecords();

        $user = $this->model->asObject(CustomUser::class)->first();

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }
}
