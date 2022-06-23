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
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;

/**
 * @internal
 */
final class UpsertModelTest extends LiveModelTestCase
{
    public function testSetWorksWithUpsert(): void
    {
        $this->dontSeeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);

        $this->createModel(UserModel::class)->set([
            'email'   => 'foo@example.com',
            'name'    => 'Foo Bar',
            'country' => 'US',
        ])->upsert();

        $this->seeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);
    }

    public function testUpsertBatchSuccess(): void
    {
        $jobData = [
            [
                'name'        => 'Comedian',
                'description' => 'There\'s something in your teeth',
            ],
            [
                'name'        => 'Cab Driver',
                'description' => 'I am yellow',
            ],
        ];

        $this->createModel(JobModel::class)->upsertBatch($jobData);
        $this->seeInDatabase('job', ['name' => 'Comedian']);
        $this->seeInDatabase('job', ['name' => 'Cab Driver']);
    }

    public function testUpsertBatchValidationFail(): void
    {
        $jobData = [
            [
                'name'        => 'Comedian',
                'description' => null,
            ],
        ];

        $this->createModel(JobModel::class);

        $this->setPrivateProperty($this->model, 'validationRules', ['description' => 'required']);
        $this->assertFalse($this->model->upsertBatch($jobData));

        $error = $this->model->errors();
        $this->assertArrayHasKey('description', $error);
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
