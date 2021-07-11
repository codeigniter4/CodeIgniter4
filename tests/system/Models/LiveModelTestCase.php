<?php

namespace CodeIgniter\Models;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Model;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\ReflectionHelper;

/**
 * LiveModelTestCase should be in testing Model's features that
 * requires a database connection.
 */
abstract class LiveModelTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ReflectionHelper;

    /**
     * Current instance of the Model.
     *
     * @var Model
     */
    protected $model;

    protected $seed = 'Tests\Support\Database\Seeds\CITestSeeder';

    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->resetServices();
        $this->resetFactories();
    }

    /**
     * Create an instance of Model for use in testing.
     *
     * @param string              $modelName
     * @param BaseConnection|null $db
     *
     * @return Model
     */
    protected function createModel(string $modelName, ?BaseConnection $db = null): Model
    {
        $this->db    = $db ?? $this->db;
        $this->model = new $modelName($this->db);

        return $this->model;
    }
}
