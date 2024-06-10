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
use Tests\Support\Entity\User;
use Tests\Support\Models\UserTimestampModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class TimestampModelTest extends LiveModelTestCase
{
    protected $migrate     = true;
    protected $migrateOnce = true;
    protected $refresh     = false;
    protected $seed        = '';

    protected function tearDown(): void
    {
        parent::tearDown();

        // Reset current time.
        Time::setTestNow();
    }

    /**
     * @return int|string Insert ID
     */
    private function allowDatesPrepareOneRecord(array $data)
    {
        $this->createModel(UserTimestampModel::class);
        $this->db->table('user')->truncate();

        $this->model->setAllowedFields([
            'name',
            'email',
            'country',
            'created_at',
            'updated_at',
            'deleted_at',
        ]);

        return $this->model->insert($data, true);
    }

    /**
     * @return int|string Insert ID
     */
    private function doNotAllowDatesPrepareOneRecord(array $data)
    {
        $this->createModel(UserTimestampModel::class);
        $this->db->table('user')->truncate();

        $this->model->setAllowedFields([
            'name',
            'email',
            'country',
            // no 'created_at',
            // no 'updated_at',
            'deleted_at',
        ]);

        return $this->model->insert($data, true);
    }

    public function testDoNotAllowDatesInsertArrayWithoutDatesSetsTimestamp(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'    => 'John Smith',
            'email'   => 'john@example.com',
            'country' => 'US',
            // no created_at
            // no updated_at
        ];
        $id = $this->doNotAllowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $expected = '2023-11-25 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    public function testDoNotAllowDatesInsertArrayWithDatesSetsTimestamp(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->doNotAllowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $expected = '2023-11-25 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    public function testDoNotAllowDatesUpdateArrayUpdatesUpdatedAt(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->doNotAllowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $user['country'] = 'CA';
        $this->model->update($user['id'], $user);

        $user = $this->model->find($id);

        $expected = '2023-11-25 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    public function testDoNotAllowDatesUpdateEntityUpdatesUpdatedAt(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->doNotAllowDatesPrepareOneRecord($data);
        $this->setPrivateProperty($this->model, 'returnType', User::class);
        $this->setPrivateProperty($this->model, 'tempReturnType', User::class);

        $user = $this->model->find($id);

        $user->country = 'CA';
        $this->model->update($user->id, $user);

        $user = $this->model->find($id);

        $this->assertSame('2023-11-25 12:00:00', (string) $user->created_at);
        $this->assertSame('2023-11-25 12:00:00', (string) $user->updated_at);
    }

    /**
     * We do not recommend to add timestamp fields to $allowedFields.
     * If you want to add old data to these fields, use Query Builder.
     */
    public function testAllowDatesInsertArrayWithoutDatesSetsTimestamp(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'    => 'John Smith',
            'email'   => 'john@example.com',
            'country' => 'US',
            // no created_at
            // no updated_at
        ];
        $id = $this->allowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $expected = '2023-11-25 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    /**
     * We do not recommend to add timestamp fields to $allowedFields.
     * If you want to add old data to these fields, use Query Builder.
     */
    public function testAllowDatesInsertArrayWithDatesSetsTimestamp(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->allowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $expected = '2000-01-01 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    /**
     * We do not recommend to add timestamp fields to $allowedFields.
     * If you want to add old data to these fields, use Query Builder.
     */
    public function testAllowDatesUpdateArrayUpdatesUpdatedAt(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->allowDatesPrepareOneRecord($data);

        $user = $this->model->find($id);

        $user['country'] = 'CA';
        $this->model->update($user['id'], $user);

        $user = $this->model->find($id);

        $expected = '2000-01-01 12:00:00';
        if ($this->db->DBDriver === 'SQLSRV') {
            $expected .= '.000';
        }
        $this->assertSame($expected, $user['created_at']);
        $this->assertSame($expected, $user['updated_at']);
    }

    /**
     * We do not recommend to add timestamp fields to $allowedFields.
     * If you want to add old data to these fields, use Query Builder.
     */
    public function testAllowDatesUpdateEntityUpdatesUpdatedAt(): void
    {
        Time::setTestNow('2023-11-25 12:00:00');

        $data = [
            'name'       => 'John Smith',
            'email'      => 'john@example.com',
            'country'    => 'US',
            'created_at' => '2000-01-01 12:00:00',
            'updated_at' => '2000-01-01 12:00:00',
        ];
        $id = $this->allowDatesPrepareOneRecord($data);
        $this->setPrivateProperty($this->model, 'returnType', User::class);
        $this->setPrivateProperty($this->model, 'tempReturnType', User::class);

        $user = $this->model->find($id);

        $user->country = 'CA';
        $this->model->update($user->id, $user);

        $user = $this->model->find($id);

        $this->assertSame('2000-01-01 12:00:00', (string) $user->created_at);
        // The Entity has `updated_at` value, but it will be discarded because of onlyChanged.
        $this->assertSame('2023-11-25 12:00:00', (string) $user->updated_at);
    }
}
