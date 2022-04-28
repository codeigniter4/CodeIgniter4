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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\ReflectionHelper;
use Tests\Support\Database\Seeds\CITestSeeder;

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

    protected $seed = CITestSeeder::class;

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
     */
    protected function createModel(string $modelName, ?BaseConnection $db = null): Model
    {
        $this->db    = $db ?? $this->db;
        $this->model = new $modelName($this->db);

        return $this->model;
    }
}
