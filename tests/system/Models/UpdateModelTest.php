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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Database\RawSql;
use CodeIgniter\Entity\Entity;
use CodeIgniter\Exceptions\InvalidArgumentException;
use Config\Database;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use stdClass;
use Tests\Support\Entity\User;
use Tests\Support\Entity\UUID;
use Tests\Support\Models\EventModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\UserCastsTimestampModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\UserTimestampModel;
use Tests\Support\Models\UUIDPkeyModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @internal
 */
#[Group('DatabaseLive')]
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

    public function testUpdateWithRawSql(): void
    {
        $this->createModel(UserModel::class);

        // RawSql objects should be allowed as primary key values
        $result = $this->model->update(new RawSql('1'), ['name' => 'RawSql User']);
        $this->assertTrue($result);
        $this->seeInDatabase('user', ['id' => 1, 'name' => 'RawSql User']);
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

    public function testUpdateBatchInvalidIndex(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage(
            'The index ("not_exist") for updateBatch() is missing in the data: {"name":"Derek Jones","country":"Greece"}',
        );

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

        $this->createModel(UserModel::class)->updateBatch($data, 'not_exist');
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

        $entity1->id      = 1;
        $entity1->name    = 'Jones Martin';
        $entity1->country = 'India';
        $entity1->deleted = 0;
        $entity1->syncOriginal();
        // Update the entity.
        $entity1->country = 'China';

        // This entity is not updated.
        $entity2->id      = 4;
        $entity2->name    = 'Jones Martin';
        $entity2->country = 'India';
        $entity2->deleted = 0;
        $entity2->syncOriginal();

        $model = $this->createModel(UserModel::class);
        $this->setPrivateProperty($model, 'updateOnlyChanged', false);
        $this->assertSame(2, $model->updateBatch([$entity1, $entity2], 'id'));
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

        $entity->id      = 1;
        $entity->name    = 'Jones Martin';
        $entity->email   = 'jones@example.org';
        $entity->country = 'India';
        $entity->deleted = 0;

        $id = $this->model->insert($entity);

        $entity->syncOriginal();

        $entity->fill(['thisKeyIsNotAllowed' => 'Bar']);

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to update.');
        $this->model->update($id, $entity);
    }

    public function testUpdateSetObject(): void
    {
        $this->createModel(UserModel::class);

        $object          = new stdClass();
        $object->name    = 'Jones Martin';
        $object->email   = 'jones@example.org';
        $object->country = 'India';

        /** @var int|string $id */
        $id = $this->model->insert($object);

        /** @var stdClass $object */
        $object       = $this->model->find($id);
        $object->name = 'John Smith';

        $return = $this->model->where('id', $id)->set($object)->update();

        $this->assertTrue($return);
    }

    public function testUpdateSetEntity(): void
    {
        $this->createModel(UserModel::class);

        $object          = new stdClass();
        $object->id      = 1;
        $object->name    = 'Jones Martin';
        $object->email   = 'jones@example.org';
        $object->country = 'India';

        $id = $this->model->insert($object);

        $entity = new Entity([
            'id'      => 1,
            'name'    => 'John Smith',
            'email'   => 'john@example.org',
            'country' => 'India',
        ]);

        $return = $this->model->where('id', $id)->set($entity)->update();

        $this->assertTrue($return);
    }

    public function testUpdateEntityWithPrimaryKeyCast(): void
    {
        if (
            in_array($this->db->DBDriver, ['OCI8', 'Postgre', 'SQLSRV', 'SQLite3'], true)
        ) {
            $this->markTestSkipped($this->db->DBDriver . ' does not work with binary data as string data.');
        }

        $this->createUuidTable();

        $this->createModel(UUIDPkeyModel::class);

        $entity        = new UUID();
        $entity->id    = '550e8400-e29b-41d4-a716-446655440000';
        $entity->value = 'test1';

        $id     = $this->model->insert($entity);
        $entity = $this->model->find($id);

        $entity->value = 'id';
        $result        = $this->model->save($entity);

        $this->assertTrue($result);

        $entity = $this->model->find($id);

        $this->assertSame('id', $entity->value);
    }

    public function testUpdateBatchEntityWithPrimaryKeyCast(): void
    {
        if (
            in_array($this->db->DBDriver, ['OCI8', 'Postgre', 'SQLSRV', 'SQLite3'], true)
        ) {
            $this->markTestSkipped($this->db->DBDriver . ' does not work with binary data as string data.');
        }

        // See https://github.com/codeigniter4/CodeIgniter4/pull/8282#issuecomment-1836974182
        $this->markTestSkipped(
            'batchUpdate() is currently not working due to data type issues in the generated SQL statement.',
        );

        $this->createUuidTable();

        $this->createModel(UUIDPkeyModel::class);

        $entity1        = new UUID();
        $entity1->id    = '550e8400-e29b-41d4-a716-446655440000';
        $entity1->value = 'test1';
        $id1            = $this->model->insert($entity1);

        $entity2        = new UUID();
        $entity2->id    = 'bd59cff1-7a24-dde5-ac10-7b929db6da8c';
        $entity2->value = 'test2';
        $id2            = $this->model->insert($entity2);

        $entity1 = $this->model->find($id1);
        $entity2 = $this->model->find($id2);

        $entity1->value = 'update1';
        $entity2->value = 'update2';

        $data = [
            $entity1,
            $entity2,
        ];
        $this->model->updateBatch($data, 'id');

        $this->seeInDatabase('uuid', [
            'value' => 'update1',
        ]);
        $this->seeInDatabase('uuid', [
            'value' => 'update2',
        ]);
    }

    private function createUuidTable(): void
    {
        $forge = Database::forge($this->DBGroup);
        $forge->dropTable('uuid', true);
        $forge->addField([
            'id'    => ['type' => 'BINARY', 'constraint' => 16],
            'value' => ['type' => 'VARCHAR', 'constraint' => 400, 'null' => true],
        ])->addKey('id', true)->createTable('uuid', true);
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
     * @param bool|int|string|null $id
     * @param class-string         $exception
     */
    #[DataProvider('provideUpdateThrowDatabaseExceptionWithoutWhereClause')]
    public function testUpdateThrowDatabaseExceptionWithoutWhereClause($id, string $exception, string $exceptionMessage): void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage($exceptionMessage);

        // $useSoftDeletes = false
        $this->createModel(JobModel::class);

        $this->model->update($id, ['name' => 'Foo Bar']);
    }

    /**
     * @return iterable<string, array{mixed, class-string, string}>
     */
    public static function provideUpdateThrowDatabaseExceptionWithoutWhereClause(): iterable
    {
        yield from [
            'null' => [
                null,
                DatabaseException::class,
                'Updates are not allowed unless they contain a "where" or "like" clause.',
            ],
            'false' => [
                false,
                InvalidArgumentException::class,
                'Invalid primary key: boolean false is not allowed.',
            ],
            'true' => [
                true,
                InvalidArgumentException::class,
                'Invalid primary key: boolean true is not allowed.',
            ],
            '0 integer' => [
                0,
                InvalidArgumentException::class,
                'Invalid primary key: 0 is not allowed.',
            ],
            "'0' string" => [
                '0',
                InvalidArgumentException::class,
                "Invalid primary key: '0' is not allowed.",
            ],
            'empty string' => [
                '',
                InvalidArgumentException::class,
                "Invalid primary key: '' is not allowed.",
            ],
            'empty array' => [
                [],
                InvalidArgumentException::class,
                'Invalid primary key: cannot be an empty array.',
            ],
            'nested array' => [
                [[1, 2]],
                InvalidArgumentException::class,
                'Invalid primary key at index 0: nested arrays are not allowed.',
            ],
            'array with null' => [
                [1, null, 3],
                InvalidArgumentException::class,
                'Invalid primary key: NULL is not allowed.',
            ],
            'array with 0' => [
                [1, 0, 3],
                InvalidArgumentException::class,
                'Invalid primary key: 0 is not allowed.',
            ],
            "array with '0'" => [
                [1, '0', 3],
                InvalidArgumentException::class,
                "Invalid primary key: '0' is not allowed.",
            ],
            'array with empty string' => [
                [1, '', 3],
                InvalidArgumentException::class,
                "Invalid primary key: '' is not allowed.",
            ],
            'array with boolean' => [
                [1, false, 3],
                InvalidArgumentException::class,
                'Invalid primary key: boolean false is not allowed.',
            ],
        ];
    }

    public function testUpdateEntityUpdateOnlyChangedFalse(): void
    {
        $model = new class () extends UserTimestampModel {
            protected $returnType             = User::class;
            protected bool $updateOnlyChanged = false;
        };

        $user           = $model->find(1);
        $updateAtBefore = $user->updated_at;

        // updates the Entity without changes.
        $result = $model->update(1, $user);

        $user          = $model->find(1);
        $updateAtAfter = $user->updated_at;

        $this->assertTrue($result);
        $this->assertNotSame($updateAtBefore, $updateAtAfter);
    }

    public function testUpdateBatchWithCasts(): void
    {
        $this->createModel(UserCastsTimestampModel::class);

        $rows = $this->db->table('user')->limit(2)->orderBy('id', 'asc')->get()->getResultArray();

        $this->assertNotEmpty($rows);
        $this->assertCount(2, $rows);

        $row1 = $rows[0];
        $row2 = $rows[1];

        $this->assertNotNull($row1);
        $this->assertNotNull($row2);

        $updateData = [
            [
                'id'    => $row1['id'],
                'email' => [
                    'personal' => 'email1.new@country.com',
                    'work'     => 'email1.new@company.com',
                ],
            ],
            [
                'id'    => $row2['id'],
                'email' => [
                    'personal' => 'email2.new@country.com',
                    'work'     => 'email2.new@company.com',
                ],
            ],
        ];

        $numRows = $this->model->updateBatch($updateData, 'id'); // @phpstan-ignore argument.type
        $this->assertSame(2, $numRows);

        $this->seeInDatabase('user', ['email' => json_encode($updateData[0]['email'])]);
        $this->seeInDatabase('user', ['email' => json_encode($updateData[1]['email'])]);
    }
}
