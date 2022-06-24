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

use CodeIgniter\Database\Exceptions\DatabaseException;
use stdClass;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
final class UpsertModelTest extends LiveModelTestCase
{
    public function testSetWorksWithUpsert(): void
    {
        $this->dontSeeInDatabase('user', [
            'email' => 'foo@something.com',
        ]);

        $this->createModel(UserModel::class)->set([
            'email'   => 'foo@something.com',
            'name'    => 'Foo Bar',
            'country' => 'US',
        ])->upsert();

        $this->seeInDatabase('user', [
            'email' => 'foo@something.com',
        ]);
    }

    public function testUpsertBatchSuccess(): void
    {
        $userData = [
            [
                'email'   => 'userone@something.com',
                'name'    => 'User One',
                'country' => 'US',
            ],
            [
                'email'   => 'usertwo@something.com',
                'name'    => 'User Two',
                'country' => 'US',
            ],
        ];

        // set batch size of one
        $this->createModel(UserModel::class)->upsertBatch($userData, true, 1);
        $this->seeInDatabase('user', ['email' => 'userone@something.com']);
        $this->seeInDatabase('user', ['email' => 'usertwo@something.com']);
    }

    public function testUpsertBatchValidationFail(): void
    {
        $userData = [
            [
                'email'   => 'userthree@something.com',
                'name'    => 'User Three',
                'country' => null,
            ],
        ];

        $this->createModel(UserModel::class);

        $this->setPrivateProperty($this->model, 'validationRules', ['country' => 'required']);
        $this->assertFalse($this->model->upsertBatch($userData));

        $error = $this->model->errors();
        $this->assertArrayHasKey('country', $error);
    }

    public function testUpsertArrayWithNoDataException(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to insert an entry.');
        $this->createModel(UserModel::class)->upsert([]);
    }

    public function testUpsertObjectWithNoDataException(): void
    {
        $data = new stdClass();
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage('You must use the "set" method to insert an entry.');
        $this->createModel(UserModel::class)->upsert($data);
    }
}
