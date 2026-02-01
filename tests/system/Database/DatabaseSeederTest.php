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
use Config\Database;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\SeederWithDBGroup;
use Tests\Support\Database\Seeds\SeederWithoutDBGroup;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class DatabaseSeederTest extends CIUnitTestCase
{
    protected function tearDown(): void
    {
        parent::tearDown();

        SeederWithDBGroup::reset();
        SeederWithoutDBGroup::reset();
    }

    public function testInstantiateNoSeedPath(): void
    {
        $this->expectException('InvalidArgumentException');

        $config            = new Database();
        $config->filesPath = '';
        new Seeder($config);
    }

    public function testInstantiateNotDirSeedPath(): void
    {
        $this->expectException('InvalidArgumentException');

        $config            = new Database();
        $config->filesPath = APPPATH . 'Foo';
        new Seeder($config);
    }

    /**
     * @TODO remove this when Seeder::faker() is removed
     */
    public function testFakerGet(): void
    {
        $this->assertInstanceOf(Generator::class, Seeder::faker());
    }

    public function testCallOnEmptySeeder(): void
    {
        $this->expectException('InvalidArgumentException');

        $seeder = new Seeder(new Database());
        $seeder->call('');
    }

    public function testSeederWithDBGroupUsesOwnConnection(): void
    {
        $config = new Database();
        $db     = Database::connect('tests', false);

        $seeder = new SeederWithDBGroup($config, $db);

        $testsDb = Database::connect('tests');
        $this->assertSame($testsDb, $seeder->getDatabase());
        $this->assertNotSame($db, $seeder->getDatabase());
    }

    public function testSeederWithoutDBGroupUsesPassedConnection(): void
    {
        $config = new Database();
        $db     = Database::connect('tests');

        $seeder = new SeederWithoutDBGroup($config, $db);

        $this->assertSame($db, $seeder->getDatabase());
    }

    public function testSeederWithoutDBGroupAndNoConnectionUsesDefault(): void
    {
        $config = new Database();

        $seeder = new SeederWithoutDBGroup($config);

        $defaultDb = Database::connect($config->defaultGroup);
        $this->assertSame($defaultDb, $seeder->getDatabase());
    }

    public function testCallPassesConnectionToChildSeeder(): void
    {
        $config = new Database();
        $db     = Database::connect('tests');

        $seeder = new Seeder($config, $db);
        $seeder->setSilent(true)->call(SeederWithoutDBGroup::class);

        $this->assertSame($db, SeederWithoutDBGroup::$lastConnection);
    }

    public function testCallChildWithDBGroupUsesOwnConnection(): void
    {
        $config = new Database();
        $db     = Database::connect('tests', false);

        $seeder = new Seeder($config, $db);
        $seeder->setSilent(true)->call(SeederWithDBGroup::class);

        $testsDb = Database::connect('tests');
        $this->assertSame($testsDb, SeederWithDBGroup::$lastConnection);
        $this->assertNotSame($db, SeederWithDBGroup::$lastConnection);
    }
}
