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

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\AnotherSeeder;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class DatabaseTestCaseTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = [
        CITestSeeder::class,
        AnotherSeeder::class,
    ];
    protected $namespace = [
        'Tests\Support',
        'Tests\Support\MigrationTestMigrations',
    ];

    protected function setUp(): void
    {
        $forge = Database::forge();
        $forge->dropTable('foo', true);

        $this->setUpMethods[] = 'setUpAddNamespace';

        parent::setUp();
    }

    protected function setUpAddNamespace(): void
    {
        service('autoloader')->addNamespace(
            'Tests\Support\MigrationTestMigrations',
            SUPPORTPATH . 'MigrationTestMigrations',
        );
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->regressDatabase();
    }

    public function testMultipleSeeders(): void
    {
        $this->seeInDatabase('user', ['name' => 'Jerome Lohan']);
    }

    public function testMultipleMigrationNamespaces(): void
    {
        $this->seeInDatabase('foo', ['key' => 'foobar']);
    }
}
