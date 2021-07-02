<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Entity\Entity;
use stdClass;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @internal
 */
final class UpdateModelTest extends LiveModelTestCase
{
    public function testSetWorksWithUpdate(): void
    {
        $this->dontSeeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);

        $this->createModel(UserModel::class);

        $userId = $this->model->insert([
            'email'   => 'foo@example.com',
            'name'    => 'Foo Bar',
            'country' => 'US',
        ]);

        $this->model->set([
            'name' => 'Fred Flintstone',
        ])->update($userId);

        $this->seeInDatabase('user', [
            'id'    => $userId,
            'email' => 'foo@example.com',
            'name'  => 'Fred Flintstone',
        ]);
    }

    public function testSetWorksWithUpdateNoId(): void
    {
        $this->dontSeeInDatabase('user', [
            'email' => 'foo@example.com',
        ]);

        $this->createModel(UserModel::class);

        $userId = $this->model->insert([
            'email'   => 'foo@example.com',
            'name'    => 'Foo Bar',
            'country' => 'US',
        ]);

        $this->model->where('id', $userId)->set([
            'name' => 'Fred Flintstone',
        ])->update();

        $this->seeInDatabase('user', [
            'id'    => $userId,
            'email' => 'foo@example.com',
            'name'  => 'Fred Flintstone',
        ]);
    }

    public function testUpdateArray(): void
    {
        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $this->createModel(UserModel::class);
        $this->model->insert($data);

        $result = $this->model->update([1, 2], ['name' => 'Foo Bar']);
        $this->assertTrue($result);
        $this->seeInDatabase('user', ['id' => 1, 'name' => 'Foo Bar']);
        $this->seeInDatabase('user', ['id' => 2, 'name' => 'Foo Bar']);
    }

    public function testUpdateResultFail(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);

        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $this->createModel(UserModel::class);
        $this->model->insert($data);

        $this->setPrivateProperty($this->model, 'allowedFields', ['name123']);
        $result = $this->model->update(1, ['name123' => 'Foo Bar 1']);
        $this->assertFalse($result);
        $this->dontSeeInDatabase('user', ['id' => 1, 'name' => 'Foo Bar 1']);
    }

    public function testUpdateBatchSuccess(): void
    {
        $data = [
            [
                'name'    => 'Derek Jones',
                'country' => 'Greece',
            ],
            [
                'name'    => 'Ahmadinejad',
                'country' => 'Greece',
            ],
        ];

        $this->createModel(UserModel::class)->updateBatch($data, 'name');

        $this->seeInDatabase('user', [
            'name'    => 'Derek Jones',
            'country' => 'Greece',
        ]);
        $this->seeInDatabase('user', [
            'name'    => 'Ahmadinejad',
            'country' => 'Greece',
        ]);
    }

    public function testUpdateBatchValidationFail(): void
    {
        $data = [
            [
                'name'    => 'Derek Jones',
                'country' => null,
            ],
        ];

        $this->createModel(UserModel::class);
        $this->setPrivateProperty($this->model, 'validationRules', ['country' => 'required']);
        $this->assertFalse($this->model->updateBatch($data, 'name'));

        $error = $this->model->errors();
        $this->assertTrue(isset($error['country']));
    }

    public function testUpdateBatchWithEntity(): void
    {
        $entity1 = new class() extends Entity {
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

        $entity2 = new class() extends Entity {
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

        $entity1->id      = 1;
        $entity1->name    = 'Jones Martin';
        $entity1->country = 'India';
        $entity1->deleted = 0;

        $entity2->id      = 4;
        $entity2->name    = 'Jones Martin';
        $entity2->country = 'India';
        $entity2->deleted = 0;

        $this->assertSame(2, $this->createModel(UserModel::class)->updateBatch([$entity1, $entity2], 'id'));
    }

    public function testUpdateNoPrimaryKey(): void
    {
        $this->db->table('secondary')->insert([
            'key'   => 'foo',
            'value' => 'bar',
        ]);

        $this->dontSeeInDatabase('secondary', [
            'key'   => 'bar',
            'value' => 'baz',
        ]);

        $this->createModel(SecondaryModel::class)
            ->where('key', 'foo')
            ->update(null, ['key' => 'bar', 'value' => 'baz']);

        $this->seeInDatabase('secondary', [
            'key'   => 'bar',
            'value' => 'baz',
        ]);
    }

    public function testUpdateObject(): void
    {
        $this->createModel(ValidModel::class);

        $testModel = new JobModel();

        $testModel->name        = 'my name';
        $testModel->description = 'some description';

        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->model->update(1, $testModel);
        $this->seeInDatabase('job', ['id' => 1]);
    }

    public function testUpdateArrayWithDataException(): void
    {
        $this->createModel(EventModel::class);

        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $id = $this->model->insert($data);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to update.');
        $this->model->update($id, []);
    }

    public function testUpdateObjectWithDataException(): void
    {
        $this->createModel(EventModel::class);

        $data = (object) [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $id = $this->model->insert($data);

        $data = new stdClass();

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to update.');
        $this->model->update($id, $data);
    }

    public function testUpdateArrayWithDataExceptionNoAllowedFields(): void
    {
        $this->createModel(EventModel::class);

        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];

        $id = $this->model->insert($data);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to update.');
        $this->model->update($id, ['thisKeyIsNotAllowed' => 'Bar']);
    }

    public function testUpdateWithEntityNoAllowedFields(): void
    {
        $this->createModel(UserModel::class);

        $entity = new class() extends Entity {
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

        $entity->id      = 1;
        $entity->name    = 'Jones Martin';
        $entity->country = 'India';
        $entity->deleted = 0;

        $id = $this->model->insert($entity);

        $entity->syncOriginal();

        $entity->fill(['thisKeyIsNotAllowed' => 'Bar']);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to update.');
        $this->model->update($id, $entity);
    }

    public function testUseAutoIncrementSetToFalseUpdate(): void
    {
        $key = 'key';

        $update = [
            'value' => 'some different value',
        ];

        $this->createModel(WithoutAutoIncrementModel::class)->update($key, $update);
        $this->seeInDatabase('without_auto_increment', ['key' => $key, 'value' => $update['value']]);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/4087
     */
    public function testUpdateWithSetAndEscape(): void
    {
        $userData = [
            'name' => 'Scott',
        ];

        $this->createModel(UserModel::class);

        $this->assertTrue($this->model->set('country', '2+2', false)->set('email', '1+1')->update(1, $userData));

        $this->seeInDatabase('user', [
            'name'    => 'Scott',
            'country' => '4',
            'email'   => '1+1',
        ]);
    }
}
