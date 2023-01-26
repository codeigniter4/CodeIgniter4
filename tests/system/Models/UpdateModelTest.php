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
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Entity\Entity;
use Generator;
use InvalidArgumentException;
use stdClass;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @group DatabaseLive
 *
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
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();
        $this->createModel(UserModel::class);

        $data = [
            'name'    => 'Foo',
            'email'   => 'foo@example.com',
            'country' => 'US',
            'deleted' => 0,
        ];
        $this->model->insert($data);

        $this->setPrivateProperty($this->model, 'allowedFields', ['name123']);

        $result = $this->model->update(1, ['name123' => 'Foo Bar 1']);

        $this->assertFalse($result);
        $this->dontSeeInDatabase('user', ['id' => 1, 'name' => 'Foo Bar 1']);

        $this->enableDBDebug();
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
        $this->assertArrayHasKey('country', $error);
    }

    public function testUpdateBatchWithEntity(): void
    {
        $entity1 = new class () extends Entity {
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

        $entity2 = new class () extends Entity {
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

        $data = ['id' => 1, 'name' => 'Jones Martin', 'country' => 'India', 'deleted' => 0];
        $entity1->fill($data);

        $data = ['id' => 4, 'name' => 'Jones Martin', 'country' => 'India', 'deleted' => 0];
        $entity2->fill($data);

        $this->assertSame(2, $this->createModel(UserModel::class)->updateBatch([$entity1, $entity2], 'id'));
    }

    public function testUpdateBatchWithBuilderMethods(): void
    {
        $data = [
            [
                'id'      => 3,
                'name'    => 'Should Not Change',
                'email'   => 'wontchange@test.com', // won't change because not in $updateFields
                'country' => 'Greece', // will not update
            ],
            [
                'id'      => 4,
                'name'    => 'Should Change',
                'email'   => 'wontchange2@test.com',
                'country' => 'UK', // will update
            ],
        ];

        $model = $this->createModel(UserModel::class);
        $this->setPrivateProperty($model, 'useTimestamps', true);

        $updateFields = ['name', 'deleted_at' => new RawSql('CURRENT_TIMESTAMP')];

        $esc = $this->db->escapeChar;

        $model
            ->updateFields($updateFields)
            ->onConstraint(['id', new RawSql("{$esc}db_user{$esc}.{$esc}country{$esc} = {$esc}_update{$esc}.{$esc}country{$esc}")])
            ->setData($data, null, '_update')
            ->updateBatch();

        $result = $this->db->table('user')->where('id IN (3,4)')->get()->getResultArray();

        foreach ($result as $row) {
            if ((int) $row['id'] === 3) {
                $this->assertSame('Richard A Causey', $row['name']);
                $this->assertSame('US', $row['country']);
                $this->assertNull($row['updated_at']);
                $this->assertNull($row['deleted_at']);
            } else {
                $this->assertSame('Should Change', $row['name']);
                $this->assertSame('UK', $row['country']);
                $this->assertNotNull($row['updated_at']);
                $this->assertNotNull($row['deleted_at']);
            }
        }
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

        $data = [
            'id'      => 4,
            'name'    => 'Jones Martin',
            'email'   => 'jones@example.org',
            'country' => 'India',
            'deleted' => 0,
        ];
        $entity->fill($data);

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

    /**
     * @dataProvider provideInvalidIds
     *
     * @param false|int|null $id
     */
    public function testUpdateThrowDatabaseExceptionWithoutWhereClause($id, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        // $useSoftDeletes = false
        $this->createModel(JobModel::class);

        $this->model->update($id, ['name' => 'Foo Bar']);
    }

    public function provideInvalidIds(): Generator
    {
        yield from [
            [
                null,
                DatabaseException::class,
                'Updates are not allowed unless they contain a "where" or "like" clause.',
            ],
            [
                false,
                InvalidArgumentException::class,
                'update(): argument #1 ($id) should not be boolean.',
            ],
        ];
    }
}
