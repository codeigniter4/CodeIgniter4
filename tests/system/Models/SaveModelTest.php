<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\Exceptions\DataException;
use CodeIgniter\Entity\Entity;
use CodeIgniter\I18n\Time;
use CodeIgniter\Model;
use stdClass;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SecondaryModel;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;
use Tests\Support\Models\WithoutAutoIncrementModel;

/**
 * @internal
 */
final class SaveModelTest extends LiveModelTestCase
{
    public function testSaveNewRecordObject(): void
    {
        $this->createModel(JobModel::class);

        $data              = new stdClass();
        $data->name        = 'Magician';
        $data->description = 'Makes peoples things dissappear.';

        $this->model->protect(false)->save($data);
        $this->seeInDatabase('job', ['name' => 'Magician']);
    }

    public function testSaveNewRecordArray(): void
    {
        $this->createModel(JobModel::class);

        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $this->model->protect(false)->save($data);
        $this->seeInDatabase('job', ['name' => 'Apprentice']);
    }

    public function testSaveNewRecordArrayFail(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        $this->createModel(JobModel::class);

        $data = [
            'name123'     => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $result = $this->model->protect(false)->save($data);
        $this->assertFalse($result);
        $this->dontSeeInDatabase('job', ['name' => 'Apprentice']);
    }

    public function testSaveUpdateRecordArray(): void
    {
        $this->createModel(JobModel::class);

        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $result = $this->model->protect(false)->save($data);
        $this->seeInDatabase('job', ['name' => 'Apprentice']);
        $this->assertTrue($result);
    }

    public function testSaveUpdateRecordArrayFail(): void
    {
        $this->setPrivateProperty($this->db, 'DBDebug', false);
        $this->createModel(JobModel::class);

        $data = [
            'id'          => 1,
            'name123'     => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $result = $this->model->protect(false)->save($data);
        $this->assertFalse($result);
        $this->dontSeeInDatabase('job', ['name' => 'Apprentice']);
    }

    public function testSaveUpdateRecordObject(): void
    {
        $this->createModel(JobModel::class);
        $data = new stdClass();

        // Sqlsrv does not allow forcing an ID into an autoincrement field.
        if ($this->db->DBDriver !== 'SQLSRV') {
            $data->id = 1;
        }

        $data->name        = 'Engineer';
        $data->description = 'A fancier term for Developer.';

        $result = $this->model->protect(false)->save($data);
        $this->seeInDatabase('job', ['name' => 'Engineer']);
        $this->assertTrue($result);
    }

    public function testSaveProtected(): void
    {
        $this->createModel(JobModel::class);

        $data               = new stdClass();
        $data->id           = 1;
        $data->name         = 'Engineer';
        $data->description  = 'A fancier term for Developer.';
        $data->random_thing = 'Something wicked'; // If not protected, this would kill the script.

        $result = $this->model->protect(true)->save($data);
        $this->assertTrue($result);
    }

    public function testSelectAndEntitiesSaveOnlyChangedValues(): void
    {
        $this->hasInDatabase('job', [
            'name'        => 'Rocket Scientist',
            'description' => 'Plays guitar for Queen',
            'created_at'  => time(),
        ]);

        $this->createModel(EntityModel::class);

        $job = $this->model->select('id, name')->where('name', 'Rocket Scientist')->first();
        $this->assertNull($job->description);
        $this->assertSame('Rocket Scientist', $job->name);

        $job->description = 'Some guitar description';
        $this->model->save($job);
        $this->seeInDatabase('job', [
            'id'   => $job->id,
            'name' => 'Rocket Scientist',
        ]);

        $job = $this->model->select('id, name, description')->where('name', 'Rocket Scientist')->first();
        $this->assertSame('Some guitar description', $job->description);
    }

    public function testInsertID(): void
    {
        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $this->createModel(JobModel::class);
        $this->model->protect(false)->save($data);

        $lastInsertId = $this->model->getInsertID();
        $this->seeInDatabase('job', ['id' => $lastInsertId]);
    }

    public function testSetTable(): void
    {
        $this->createModel(SecondaryModel::class);

        $this->model->setTable('job');

        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $this->model->protect(false)->save($data);
        $lastInsertId = $this->model->getInsertID();
        $this->seeInDatabase('job', ['id' => $lastInsertId]);
    }

    public function testSaveObject(): void
    {
        $this->createModel(ValidModel::class);

        $testModel = new JobModel();

        $testModel->name        = 'my name';
        $testModel->description = 'some description';

        $this->setPrivateProperty($this->model, 'useTimestamps', true);

        $this->model->insert($testModel);
        $lastInsertId = $this->model->getInsertID();
        $this->seeInDatabase('job', ['id' => $lastInsertId]);
    }

    public function testEmptySaveData(): void
    {
        $this->assertTrue($this->createModel(JobModel::class)->protect(false)->save([]));
    }

    public function testSaveNewEntityWithDateTime(): void
    {
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

        $this->createModel(UserModel::class);

        $entity->name       = 'Mark';
        $entity->email      = 'mark@example.com';
        $entity->country    = 'India';
        $entity->deleted    = 0;
        $entity->created_at = new Time('now');

        $this->setPrivateProperty($this->model, 'useTimestamps', true);
        $this->assertTrue($this->model->save($entity));
    }

    public function testSaveNewEntityWithDate(): void
    {
        $entity = new class() extends Entity {
            protected $id;
            protected $name;
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

        $testModel                   = new class() extends Model {
            protected $table         = 'empty';
            protected $allowedFields = [
                'name',
            ];
            protected $returnType     = 'object';
            protected $useSoftDeletes = true;
            protected $dateFormat     = 'date';
            public $name              = '';
        };

        $entity->name       = 'Mark';
        $entity->created_at = new Time('now');

        $this->setPrivateProperty($testModel, 'useTimestamps', true);
        $this->assertTrue($testModel->save($entity));
        $testModel->truncate();
    }

    public function testInvalidAllowedFieldException(): void
    {
        $this->createModel(JobModel::class);
        $this->model->setAllowedFields([]);

        $data = [
            'name'        => 'Apprentice',
            'description' => 'That thing you do.',
        ];

        $this->expectException(DataException::class);
        $this->expectExceptionMessage('Allowed fields must be specified for model: Tests\Support\Models\JobModel');

        $this->model->save($data);
    }

    public function testUseAutoIncrementSetToFalseSave(): void
    {
        $insert = [
            'key'   => 'some_random_key',
            'value' => 'some value',
        ];

        $this->createModel(WithoutAutoIncrementModel::class);
        $this->model->save($insert);

        $this->assertSame($insert['key'], $this->model->getInsertID());
        $this->seeInDatabase('without_auto_increment', $insert);

        $update = [
            'key'   => 'some_random_key',
            'value' => 'some different value',
        ];

        $this->model->save($update);
        $this->assertSame($insert['key'], $this->model->getInsertID());
        $this->seeInDatabase('without_auto_increment', $update);
    }
}
