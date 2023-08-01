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

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use Config\Database;
use stdClass;
use Tests\Support\Entity\User;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\UserObjModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class InsertModelTest extends LiveModelTestCase
{
    public function testSetWorksWithInsert(): void
    {
        $this->dontSeeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);

        $this->createModel(UserModel::class)->set([
            'email'   => 'foo@example.com',
            'name'    => 'Foo Bar',
            'country' => 'US',
        ])->insert();

        $this->seeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);
    }

    public function testInsertBatchSuccess(): void
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

        $this->createModel(JobModel::class)->insertBatch($jobData);
        $this->seeInDatabase('job', ['name' => 'Comedian']);
        $this->seeInDatabase('job', ['name' => 'Cab Driver']);
    }

    public function testInsertBatchUseAutoIncrementSetToFalse(): void
    {
        $insertData = [
            [
                'key'   => 'key1',
                'value' => 'value1',
            ],
            [
                'key'   => 'key2',
                'value' => 'value2',
            ],
        ];

        $this->createModel(WithoutAutoIncrementModel::class);
        $this->model->insertBatch($insertData);

        $this->seeInDatabase('without_auto_increment', ['key' => 'key1']);
        $this->seeInDatabase('without_auto_increment', ['key' => 'key2']);
    }

    public function testInsertBatchValidationFail(): void
    {
        $jobData = [
            [
                'name'        => 'Comedian',
                'description' => null,
            ],
        ];

        $this->createModel(JobModel::class);

        $this->setPrivateProperty($this->model, 'validationRules', ['description' => 'required']);
        $this->assertFalse($this->model->insertBatch($jobData));

        $error = $this->model->errors();
        $this->assertArrayHasKey('description', $error);
    }

    public function testInsertBatchSetsIntTimestamps(): void
    {
        $jobData = [
            [
                'name' => 'Philosopher',
            ],
            [
                'name' => 'Laborer',
            ],
        ];

        $this->createModel(JobModel::class);

        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->assertSame(2, $this->model->insertBatch($jobData));

        $result = $this->model->where('name', 'Philosopher')->first();
        $this->assertCloseEnough(time(), $result->created_at);
    }

    public function testInsertBatchSetsDatetimeTimestamps(): void
    {
        $userData = [
            [
                'name'    => 'Lou',
                'email'   => 'lou@example.com',
                'country' => 'Ireland',
            ],
            [
                'name'    => 'Sue',
                'email'   => 'sue@example.com',
                'country' => 'Ireland',
            ],
        ];

        $this->createModel(UserModel::class);

        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->assertSame(2, $this->model->insertBatch($userData));

        $result = $this->model->where('name', 'Lou')->first();
        $this->assertCloseEnough(time(), strtotime($result->created_at));
    }

    public function testInsertResult(): void
    {
        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $this->createModel(JobModel::class);

        $result = $this->model->protect(false)->insert($data, false);
        $this->assertTrue($result);

        $lastInsertId = $this->model->getInsertID();
        $this->seeInDatabase('job', ['id' => $lastInsertId]);
    }

    public function testInsertResultFail(): void
    {
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        $data = [
            'name123'     => 'Apprentice',
            'description' => 'That thing you do.',
        ];
        $this->createModel(JobModel::class);

        $result = $this->model->protect(false)->insert($data, false);

        $this->assertFalse($result);

        $lastInsertId = $this->model->getInsertID();

        $this->assertSame(0, $lastInsertId);
        $this->dontSeeInDatabase('job', ['id' => $lastInsertId]);

        $this->enableDBDebug();
    }

    public function testInsertBatchNewEntityWithDateTime(): void
    {
        $entity = new class () extends Entity {
            protected $id;
            protected $name;
            protected $email;
            protected $country;
            protected $deleted;
            protected $created_at;
            protected $updated_at;
            protected $_options = [
                'datamap' => [],
                'dates'   => [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'casts' => [],
            ];
        };

        $entityTwo = clone $entity;

        $this->createModel(UserModel::class);

        $entity->name       = 'Mark One';
        $entity->email      = 'markone@example.com';
        $entity->country    = 'India';
        $entity->deleted    = 0;
        $entity->created_at = new Time('now');

        $entityTwo->name       = 'Mark Two';
        $entityTwo->email      = 'marktwo@example.com';
        $entityTwo->country    = 'India';
        $entityTwo->deleted    = 0;
        $entityTwo->created_at = $entity->created_at;

        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->assertSame(2, $this->model->insertBatch([$entity, $entityTwo]));
    }

    public function testInsertArrayWithNoDataException(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');
        $this->createModel(UserModel::class)->insert([]);
    }

    public function testInsertPermitInsertNoData(): void
    {
        $forge = Database::forge();
        $forge->addField([
            'id'         => ['type' => 'INTEGER', 'constraint' => 11, 'auto_increment' => true],
            'created_at' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
            'updated_at' => ['type' => 'INTEGER', 'constraint' => 11, 'null' => true],
        ])->addKey('id', true)->createTable('insert_no_data', true);

        $model = new class () extends Model {
            protected $table         = 'insert_no_data';
            protected $allowedFields = [
                'updated_at',
            ];
        };

        $model->allowEmptyInserts()->insert([]);

        $this->seeInDatabase('insert_no_data', ['id' => $model->getInsertID()]);

        $forge->dropTable('insert_no_data');

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');

        $model->allowEmptyInserts(false);
        $model->insert([]);
    }

    public function testInsertObjectWithNoDataException(): void
    {
        $data = new stdClass();
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');
        $this->createModel(UserModel::class)->insert($data);
    }

    public function testInsertArrayWithNoDataExceptionNoAllowedData(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');
        $this->createModel(UserModel::class)->insert(['thisKeyIsNotAllowed' => 'Bar']);
    }

    public function testInsertEntityWithNoDataExceptionNoAllowedData(): void
    {
        $this->createModel(UserModel::class);

        $entity = new class () extends Entity {
            protected $id;
            protected $name;
            protected $email;
            protected $country;
            protected $deleted;
            protected $created_at;
            protected $updated_at;
            protected $_options = [
                'datamap' => [],
                'dates'   => [
                    'created_at',
                    'updated_at',
                    'deleted_at',
                ],
                'casts' => [],
            ];
        };

        $entity->fill(['thisKeyIsNotAllowed' => 'Bar']);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');
        $this->model->insert($entity);
    }

    public function testUseAutoIncrementSetToFalseInsertException(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no primary key defined when trying to make insert');

        $insert = [
            'value' => 'some different value',
        ];

        $this->createModel(WithoutAutoIncrementModel::class)->insert($insert);
    }

    public function testUseAutoIncrementSetToFalseInsert(): void
    {
        $insert = [
            'key'   => 'some_random_key',
            'value' => 'some different value',
        ];

        $this->createModel(WithoutAutoIncrementModel::class);
        $this->model->insert($insert);

        $this->assertSame($insert['key'], $this->model->getInsertID());
        $this->seeInDatabase('without_auto_increment', $insert);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4087
     */
    public function testInsertWithSetAndEscape(): void
    {
        $userData = [
            'name' => 'Scott',
        ];

        $this->createModel(UserModel::class);

        $this->setPrivateProperty($this->model, 'useTimestamps', true);

        $this->model->set('country', '1+1', false)->set('email', '2+2')->insert($userData);

        $this->assertGreaterThan(0, $this->model->getInsertID());
        $result = $this->model->where('name', 'Scott')->where('country', '2')->where('email', '2+2')->first();

        $this->assertNotNull($result->created_at);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4247
     */
    public function testInsertWithDefaultValue(): void
    {
        $this->createModel(UserObjModel::class);

        $entity             = new User();
        $entity->name       = 'Mark';
        $entity->email      = 'mark@example.com';
        $entity->country    = 'India';  // same as the default
        $entity->deleted    = 0;
        $entity->created_at = new Time('now');

        $this->model->insert($entity);

        $id = $this->model->getInsertID();
        $this->assertSame($entity->country, $this->model->find($id)->country);
    }
}
