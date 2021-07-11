<?php

namespace CodeIgniter\Models;

use BadMethodCallException;
use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;
use Tests\Support\Models\JobModel;
use Tests\Support\Models\UserModel;

/**
 * GeneralModelTest should be used when only testing Model's
 * features without requiring a database connection.
 *
 * @internal
 */
final class GeneralModelTest extends CIUnitTestCase
{
    /**
     * Current instance of the Model.
     *
     * @var Model
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetServices();
    }

    /**
     * Create an instance of Model for use in testing.
     *
     * @param string              $modelName
     * @param BaseConnection|null $db
     *
     * @return Model
     */
    private function createModel(string $modelName, ?BaseConnection $db = null): Model
    {
        $this->model = new $modelName($db);

        return $this->model;
    }

    public function testGetModelDetails(): void
    {
        $this->createModel(JobModel::class);

        $this->assertSame('job', $this->model->table);
        $this->assertSame('id', $this->model->primaryKey);
        $this->assertSame('object', $this->model->returnType);
        $this->assertNull($this->model->DBGroup);
    }

    public function testMagicGetters(): void
    {
        $this->createModel(UserModel::class);

        $this->assertTrue(isset($this->model->table));
        $this->assertSame('user', $this->model->table);
        $this->assertFalse(isset($this->model->foobar));
        $this->assertNull($this->model->foobar);

        $this->model->flavor = 'chocolate';
        $this->assertTrue(isset($this->model->flavor));
        $this->assertSame('chocolate', $this->model->flavor);

        // from DB
        $this->assertTrue(isset($this->model->DBPrefix));
        $this->assertSame('utf8', $this->model->charset);

        // from Builder
        $this->assertTrue(isset($this->model->QBNoEscape));
        $this->assertIsArray($this->model->QBNoEscape);
    }

    public function testUndefinedModelMethod(): void
    {
        $this->expectException(BadMethodCallException::class);
        $this->expectExceptionMessage('Call to undefined method Tests\Support\Models\UserModel::undefinedMethodCall');
        $this->createModel(UserModel::class)->undefinedMethodCall();
    }

    public function testSetAllowedFields(): void
    {
        $allowed1 = [
            'id',
            'created_at',
        ];
        $allowed2 = [
            'id',
            'updated_at',
        ];

        $model                       = new class() extends Model {
            protected $allowedFields = [
                'id',
                'created_at',
            ];
        };

        $this->assertSame($allowed1, $this->getPrivateProperty($model, 'allowedFields'));

        $model->setAllowedFields($allowed2);
        $this->assertSame($allowed2, $this->getPrivateProperty($model, 'allowedFields'));
    }

    public function testBuilderUsesModelTable(): void
    {
        $builder = $this->createModel(UserModel::class)->builder();
        $this->assertSame('user', $builder->getTable());
    }

    public function testBuilderRespectsTableParameter(): void
    {
        $this->createModel(UserModel::class);
        $builder1 = $this->model->builder('jobs');
        $builder2 = $this->model->builder();

        $this->assertSame('jobs', $builder1->getTable());
        $this->assertSame('user', $builder2->getTable());
    }

    public function testBuilderWithParameterIgnoresShared(): void
    {
        $this->createModel(UserModel::class);
        $builder1 = $this->model->builder();
        $builder2 = $this->model->builder('jobs');
        $builder3 = $this->model->builder();

        $this->assertSame('user', $builder1->getTable());
        $this->assertSame('jobs', $builder2->getTable());
        $this->assertSame('user', $builder3->getTable());
    }

    public function testInitialize(): void
    {
        $model = new class() extends Model {
            /**
             * @var bool
             */
            public $initialized = false;

            /**
             * Marks the model as initialized.
             *
             * @return void
             */
            protected function initialize(): void
            {
                $this->initialized = true;
            }
        };

        $this->assertTrue($model->initialized);
    }
}
