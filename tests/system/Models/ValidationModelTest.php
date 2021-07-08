<?php

namespace CodeIgniter\Models;

use CodeIgniter\Config\Factories;
use Config\Validation;
use stdClass;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\ValidErrorsModel;
use Tests\Support\Models\ValidModel;

/**
 * @internal
 */
final class ValidationModelTest extends LiveModelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->createModel(ValidModel::class);
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

    public function testValidationWithGroupName(): void
    {
        $config = new Validation();

        $config->grouptest = [
            'name' => [
                'required',
                'min_length[3]',
            ],
            'token' => 'in_list[{id}]',
        ];

        $data = [
            'name'  => 'abc',
            'id'    => 13,
            'token' => 13,
        ];

        Factories::injectMock('config', 'Validation', $config);

        $this->createModel(ValidModel::class);
        $this->setPrivateProperty($this->model, 'validationRules', 'grouptest');
        $this->assertTrue($this->model->validate($data));
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

        $this->assertTrue($this->model->insert($data) !== false);
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
}
