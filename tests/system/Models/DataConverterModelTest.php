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

use CodeIgniter\I18n\Time;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Entity\CustomUser;
use Tests\Support\Entity\User;
use Tests\Support\Models\UserCastsTimestampModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
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
        $this->assertSame('John Smith', $user['name']);
        // `name` is cast by custom CastBase64 handler.
        $this->seeInDatabase('user', ['name' => 'Sm9obiBTbWl0aA==']);
    }

    public function testFindAsArrayReturnsNull(): void
    {
        $this->createModel(UserCastsTimestampModel::class);
        $this->db->table('user')->truncate();

        $user = $this->model->find(1);

        $this->assertNull($user);
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
            'email'   => ['john@example.com'],
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

    public function testFindAsEntity(): void
    {
        $id = $this->prepareOneRecord();

        $user = $this->model->asObject(User::class)->find($id);

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }

    public function testFindArrayAsEntity(): void
    {
        $id = $this->prepareOneRecord();

        $users = $this->model->asObject(User::class)->find([$id, 999]);

        $this->assertCount(1, $users);
        $this->assertIsInt($users[0]->id);
        $this->assertInstanceOf(Time::class, $users[0]->created_at);
    }

    public function testFindNullAsEntity(): void
    {
        $this->prepareOneRecord();

        $users = $this->model->asObject(User::class)->find();

        $this->assertCount(1, $users);
        $this->assertIsInt($users[0]->id);
        $this->assertInstanceOf(Time::class, $users[0]->created_at);
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

    public function testFindAllAsArrayReturnsNull(): void
    {
        $this->createModel(UserCastsTimestampModel::class);
        $this->db->table('user')->truncate();

        $users = $this->model->findAll();

        $this->assertSame([], $users);
    }

    private function prepareTwoRecords(): void
    {
        $this->prepareOneRecord();

        $data = [
            'name'    => 'Mike Smith',
            'email'   => ['mike@example.com'],
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

    public function testFindAllAsEntity(): void
    {
        $this->prepareTwoRecords();

        $users = $this->model->asObject(User::class)->findAll();

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

    public function testFirstAsArrayReturnsNull(): void
    {
        $this->createModel(UserCastsTimestampModel::class);
        $this->db->table('user')->truncate();

        $user = $this->model->first();

        $this->assertNull($user);
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

    public function testFirstAsEntity(): void
    {
        $this->prepareTwoRecords();

        $user = $this->model->asObject(User::class)->first();

        $this->assertIsInt($user->id);
        $this->assertInstanceOf(Time::class, $user->created_at);
    }

    public function testInsertArray(): void
    {
        $this->prepareOneRecord();

        $data = [
            'name'    => 'Joe Smith',
            'email'   => ['joe@example.com'],
            'country' => 'GB',
        ];
        $id = $this->model->insert($data, true);

        $user = $this->model->find($id);
        $this->assertSame(['joe@example.com'], $user['email']);
    }

    public function testInsertObject(): void
    {
        $this->prepareOneRecord();

        $data = (object) [
            'name'    => 'Joe Smith',
            'email'   => ['joe@example.com'],
            'country' => 'GB',
        ];
        $id = $this->model->insert($data, true);

        $user = $this->model->find($id);
        $this->assertSame(['joe@example.com'], $user['email']);
    }

    public function testUpdateArray(): void
    {
        $id   = $this->prepareOneRecord();
        $user = $this->model->find($id);

        $user['email'][] = 'private@example.org';
        $this->model->update($user['id'], $user);

        $user = $this->model->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user['email']);
    }

    public function testUpdateObject(): void
    {
        $id   = $this->prepareOneRecord();
        $user = $this->model->asObject()->find($id);

        $user->email[] = 'private@example.org';
        $this->model->update($user->id, $user);

        $user = $this->model->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user['email']);
    }

    public function testUpdateCustomObject(): void
    {
        $id = $this->prepareOneRecord();
        /** @var CustomUser $user */
        $user = $this->model->asObject(CustomUser::class)->find($id);

        $user->addEmail('private@example.org');
        $this->model->update($user->id, $user);

        $user = $this->model->asObject(CustomUser::class)->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user->email);
    }

    public function testUpdateEntity(): void
    {
        $id = $this->prepareOneRecord();
        /** @var User $user */
        $user = $this->model->asObject(User::class)->find($id);

        $email       = $user->email;
        $email[]     = 'private@example.org';
        $user->email = $email;
        $this->model->update($user->id, $user);

        $user = $this->model->asObject(User::class)->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user->email);
    }

    public function testSaveArray(): void
    {
        $id   = $this->prepareOneRecord();
        $user = $this->model->find($id);

        $user['email'][] = 'private@example.org';
        $this->model->save($user);

        $user = $this->model->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user['email']);
    }

    public function testSaveObject(): void
    {
        $id   = $this->prepareOneRecord();
        $user = $this->model->asObject()->find($id);

        $user->email[] = 'private@example.org';
        $this->model->save($user);

        $user = $this->model->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user['email']);
    }

    public function testSaveCustomObject(): void
    {
        $id = $this->prepareOneRecord();
        /** @var CustomUser $user */
        $user = $this->model->asObject(CustomUser::class)->find($id);

        $user->addEmail('private@example.org');
        $this->model->save($user);

        $user = $this->model->asObject(CustomUser::class)->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user->email);
    }

    public function testSaveEntity(): void
    {
        $id = $this->prepareOneRecord();
        /** @var User $user */
        $user = $this->model->asObject(User::class)->find($id);

        $email       = $user->email;
        $email[]     = 'private@example.org';
        $user->email = $email;
        $this->model->save($user);

        $user = $this->model->asObject(User::class)->find($id);

        $this->assertSame([
            'john@example.com',
            'private@example.org',
        ], $user->email);
    }
}
