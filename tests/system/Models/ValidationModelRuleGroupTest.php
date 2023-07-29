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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;
use Config\Services;
use stdClass;
use Tests\Support\Config\Validation;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\SimpleEntity;
use Tests\Support\Models\ValidErrorsModel;
use Tests\Support\Models\ValidModelRuleGroup;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class ValidationModelRuleGroupTest extends LiveModelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->createModel(ValidModelRuleGroup::class);
    }

    protected function createModel(string $modelName, ?BaseConnection $db = null): Model
    {
        $config     = new Validation();
        $validation = new \CodeIgniter\Validation\Validation($config, Services::renderer());

        $this->db    = $db ?? $this->db;
        $this->model = new $modelName($this->db, $validation);

        return $this->model;
    }

    public function testValid(): void
    {
        $data = [
            'name'        => 'some name',
            'description' => 'some great marketing stuff',
        ];

        $this->assertIsInt($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame([], $errors);
    }

    public function testValidationBasics(): void
    {
        $data = [
            'name'        => null,
            'description' => 'some great marketing stuff',
        ];

        $this->assertFalse($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame('You forgot to name the baby.', $errors['name']);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5859
     */
    public function testValidationTwice(): void
    {
        $data = [
            'name'        => null,
            'description' => 'some great marketing stuff',
        ];

        $this->assertFalse($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame('You forgot to name the baby.', $errors['name']);

        $data = [
            'name'        => 'some name',
            'description' => 'some great marketing stuff',
        ];

        $this->assertIsInt($this->model->insert($data));
    }

    public function testValidationWithSetValidationRule(): void
    {
        $data = [
            'name'        => 'some name',
            'description' => 'some great marketing stuff',
        ];

        $this->model->setValidationRule('description', [
            'rules'  => 'required|min_length[50]',
            'errors' => [
                'min_length' => 'Description is too short baby.',
            ],
        ]);
        $this->assertFalse($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame('Description is too short baby.', $errors['description']);
    }

    public function testValidationWithSetValidationRules(): void
    {
        $data = [
            'name'        => '',
            'description' => 'some great marketing stuff',
        ];

        $this->model->setValidationRules([
            'name' => [
                'rules'  => 'required',
                'errors' => [
                    'required' => 'Give me a name baby.',
                ],
            ],
            'description' => [
                'rules'  => 'required|min_length[50]',
                'errors' => [
                    'min_length' => 'Description is too short baby.',
                ],
            ],
        ]);
        $this->assertFalse($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame('Give me a name baby.', $errors['name']);
        $this->assertSame('Description is too short baby.', $errors['description']);
    }

    public function testValidationWithSetValidationMessage(): void
    {
        $data = [
            'name'        => null,
            'description' => 'some great marketing stuff',
        ];

        $this->model->setValidationMessage('name', [
            'required'   => 'Your baby name is missing.',
            'min_length' => 'Too short, man!',
        ]);
        $this->assertFalse($this->model->insert($data));

        $errors = $this->model->errors();
        $this->assertSame('Your baby name is missing.', $errors['name']);
    }

    public function testValidationPlaceholdersSuccess(): void
    {
        $data = [
            'name'  => 'abc',
            'id'    => 13,
            'token' => 13,
        ];

        $this->assertTrue($this->model->validate($data));
    }

    public function testValidationPlaceholdersFail(): void
    {
        $data = [
            'name'  => 'abc',
            'id'    => 13,
            'token' => 12,
        ];

        $this->assertFalse($this->model->validate($data));
    }

    public function testSkipValidation(): void
    {
        $data = [
            'name'        => '2',
            'description' => 'some great marketing stuff',
        ];

        $this->assertIsNumeric($this->model->skipValidation(true)->insert($data));
    }

    public function testCleanValidationRemovesAllWhenNoDataProvided(): void
    {
        $cleaner = $this->getPrivateMethodInvoker($this->model, 'cleanValidationRules');

        $rules = [
            'name' => 'required',
            'foo'  => 'bar',
        ];

        $rules = $cleaner($rules, null);
        $this->assertEmpty($rules);
    }

    public function testCleanValidationRemovesOnlyForFieldsNotProvided(): void
    {
        $cleaner = $this->getPrivateMethodInvoker($this->model, 'cleanValidationRules');

        $rules = [
            'name' => 'required',
            'foo'  => 'required',
        ];

        $data = [
            'foo' => 'bar',
        ];

        $rules = $cleaner($rules, $data);
        $this->assertArrayHasKey('foo', $rules);
        $this->assertArrayNotHasKey('name', $rules);
    }

    public function testCleanValidationReturnsAllWhenAllExist(): void
    {
        $cleaner = $this->getPrivateMethodInvoker($this->model, 'cleanValidationRules');

        $rules = [
            'name' => 'required',
            'foo'  => 'required',
        ];

        $data = [
            'foo'  => 'bar',
            'name' => null,
        ];

        $rules = $cleaner($rules, $data);
        $this->assertArrayHasKey('foo', $rules);
        $this->assertArrayHasKey('name', $rules);
    }

    public function testValidationPassesWithMissingFields(): void
    {
        $data = [
            'foo' => 'bar',
        ];

        $result = $this->model->validate($data);
        $this->assertTrue($result);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1584
     */
    public function testUpdateWithValidation(): void
    {
        $data = [
            'description' => 'This is a first test!',
            'name'        => 'valid',
            'id'          => 42,
            'token'       => 42,
        ];

        $id = $this->model->insert($data);
        $this->assertTrue((bool) $id);

        $data['description'] = 'This is a second test!';
        unset($data['name']);

        $result = $this->model->update($id, $data);
        $this->assertTrue($result);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
     */
    public function testRequiredWithValidationEmptyString(): void
    {
        $this->assertFalse($this->model->insert(['name' => '']));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
     */
    public function testRequiredWithValidationNull(): void
    {
        $this->assertFalse($this->model->insert(['name' => null]));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1717
     */
    public function testRequiredWithValidationTrue(): void
    {
        $data = [
            'name'        => 'foobar',
            'description' => 'just because we have to',
        ];

        $this->assertNotFalse($this->model->insert($data));
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1574
     */
    public function testValidationIncludingErrors(): void
    {
        $data = [
            'description' => 'This is a first test!',
            'name'        => 'valid',
            'id'          => 42,
            'token'       => 42,
        ];

        $this->createModel(ValidErrorsModel::class);

        $id = $this->model->insert($data);
        $this->assertFalse((bool) $id);
        $this->assertSame('Minimum Length Error', $this->model->errors()['name']);
    }

    public function testValidationByObject(): void
    {
        $data = new stdClass();

        $data->name  = 'abc';
        $data->id    = '13';
        $data->token = '13';

        $this->assertTrue($this->model->validate($data));
    }

    public function testGetValidationRules(): void
    {
        $this->createModel(JobModel::class);
        $this->setPrivateProperty($this->model, 'validationRules', ['description' => 'required']);

        $rules = $this->model->getValidationRules();
        $this->assertSame('required', $rules['description']);
    }

    public function testGetValidationMessages(): void
    {
        $jobData = [
            [
                'name'        => 'Comedian',
                'description' => null,
            ],
        ];

        $this->createModel(JobModel::class);
        $this->setPrivateProperty($this->model, 'validationRules', ['description' => 'required']);
        $this->setPrivateProperty($this->model, 'validationMessages', ['description' => 'Description field is required.']);

        $this->assertFalse($this->model->insertBatch($jobData));

        $error = $this->model->getValidationMessages();
        $this->assertSame('Description field is required.', $error['description']);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/6577
     */
    public function testUpdateEntityWithPropertyCleanValidationRulesTrueAndCallingCleanRulesFalse(): void
    {
        $model = new class () extends Model {
            protected $table           = 'test';
            protected $allowedFields   = ['field1', 'field2', 'field3', 'field4'];
            protected $returnType      = SimpleEntity::class;
            protected $validationRules = [
                'field1' => 'required_with[field2,field3,field4]',
                'field2' => 'permit_empty',
                'field3' => 'permit_empty',
                'field4' => 'permit_empty',
            ];
        };

        // Simulate to get the entity from the database.
        $entity = new SimpleEntity();
        $entity->setAttributes([
            'id'     => '1',
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => '',
            'field4' => '',
        ]);

        // Change field1 value.
        $entity->field1 = '';

        // Set $cleanValidationRules to false by cleanRules()
        $model->cleanRules(false)->save($entity);

        $errors = $model->errors();
        $this->assertCount(1, $errors);
        $this->assertSame(
            $errors['field1'],
            'The field1 field is required when field2,field3,field4 is present.'
        );
    }

    public function testUpdateEntityWithPropertyCleanValidationRulesFalse(): void
    {
        $model = new class () extends Model {
            protected $table           = 'test';
            protected $allowedFields   = ['field1', 'field2', 'field3', 'field4'];
            protected $returnType      = SimpleEntity::class;
            protected $validationRules = [
                'field1' => 'required_with[field2,field3,field4]',
                'field2' => 'permit_empty',
                'field3' => 'permit_empty',
                'field4' => 'permit_empty',
            ];

            // Set to false.
            protected $cleanValidationRules = false;
        };

        // Simulate to get the entity from the database.
        $entity = new SimpleEntity();
        $entity->setAttributes([
            'id'     => '1',
            'field1' => 'value1',
            'field2' => 'value2',
            'field3' => '',
            'field4' => '',
        ]);

        // Change field1 value.
        $entity->field1 = '';

        $model->save($entity);

        $errors = $model->errors();
        $this->assertCount(1, $errors);
        $this->assertSame(
            $errors['field1'],
            'The field1 field is required when field2,field3,field4 is present.'
        );
    }

    public function testInsertEntityValidateEntireRules(): void
    {
        $model = new class () extends Model {
            protected $table           = 'test';
            protected $allowedFields   = ['field1', 'field2', 'field3', 'field4'];
            protected $returnType      = SimpleEntity::class;
            protected $validationRules = [
                'field1' => 'required',
                'field2' => 'required',
                'field3' => 'permit_empty',
                'field4' => 'permit_empty',
            ];
        };

        $entity = new SimpleEntity();
        $entity->setAttributes([
            'field1' => 'value1',
            // field2 is missing
            'field3' => '',
            'field4' => '',
        ]);

        // Insert ignores $cleanValidationRules value.
        $model->insert($entity);

        $errors = $model->errors();
        $this->assertCount(1, $errors);
        $this->assertSame(
            $errors['field2'],
            'The field2 field is required.'
        );
    }
}
