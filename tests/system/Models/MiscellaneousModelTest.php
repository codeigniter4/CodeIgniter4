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
use CodeIgniter\I18n\Time;
use Tests\Support\Models\EntityModel;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SimpleEntity;
use Tests\Support\Models\UserModel;
use Tests\Support\Models\ValidModel;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class MiscellaneousModelTest extends LiveModelTestCase
{
    public function testChunk(): void
    {
        $rowCount = 0;

        $this->createModel(UserModel::class)->chunk(2, static function ($row) use (&$rowCount): void {
            $rowCount++;
        });

        $this->assertSame(4, $rowCount);
    }

    public function testCanCreateAndSaveEntityClasses(): void
    {
        $entity = $this->createModel(EntityModel::class)->where('name', 'Developer')->first();

        $this->assertInstanceOf(SimpleEntity::class, $entity);
        $this->assertSame('Developer', $entity->name);
        $this->assertSame('Awesome job, but sometimes makes you bored', $entity->description);

        $time = time();

        $entity->name       = 'Senior Developer';
        $entity->created_at = $time;

        $this->assertTrue($this->model->save($entity));

        $result = $this->model->where('name', 'Senior Developer')->first();
        $this->assertSame(
            Time::createFromTimestamp($time)->toDateTimeString(),
            $result->created_at->toDateTimeString()
        );
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/580
     */
    public function testPasswordsStoreCorrectly(): void
    {
        $data = [
            'name'    => password_hash('secret123', PASSWORD_BCRYPT),
            'email'   => 'foo@example.com',
            'country' => 'US',
        ];

        $this->createModel(UserModel::class)->insert($data);
        $this->seeInDatabase('user', $data);
    }

    public function testReplaceObject(): void
    {
        $data = [
            'id'          => 1,
            'name'        => 'my name',
            'description' => 'some description',
        ];

        $this->createModel(ValidModel::class)->replace($data);
        $this->seeInDatabase('job', ['id' => 1, 'name' => 'my name']);
    }

    public function testGetValidationMessagesForReplace(): void
    {
        $jobData = [
            'name'        => 'Comedian',
            'description' => null,
        ];

        $this->createModel(JobModel::class);
        $this->setPrivateProperty($this->model, 'validationRules', ['description' => 'required']);
        $this->assertFalse($this->model->replace($jobData));

        $error = $this->model->errors();
        $this->assertArrayHasKey('description', $error);
    }

    public function testUndefinedTypeInTransformDataToArray(): void
    {
        $this->expectException('InvalidArgumentException');
        $this->expectExceptionMessage('Invalid type "whatever" used upon transforming data to array.');

        $this->createModel(JobModel::class);
        $method = $this->getPrivateMethodInvoker($this->model, 'transformDataToArray');
        $method([], 'whatever');
    }

    public function testEmptyDataInTransformDataToArray(): void
    {
        $this->expectException(DataException::class);
        $this->expectExceptionMessage('There is no data to insert.');

        $this->createModel(JobModel::class);
        $method = $this->getPrivateMethodInvoker($this->model, 'transformDataToArray');
        $method([], 'insert');
    }
}
