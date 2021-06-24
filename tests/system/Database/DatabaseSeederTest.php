<?php

namespace CodeIgniter\Database;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use Faker\Generator;

/**
 * @internal
 */
final class DatabaseSeederTest extends CIUnitTestCase
{
    public function testInstantiateNoSeedPath()
    {
        $this->expectException('InvalidArgumentException');

        $config            = new Database();
        $config->filesPath = '';
        new Seeder($config);
    }

    public function testInstantiateNotDirSeedPath()
    {
        $this->expectException('InvalidArgumentException');

        $config            = new Database();
        $config->filesPath = APPPATH . 'Foo';
        new Seeder($config);
    }

    public function testFakerGet()
    {
        $this->assertInstanceOf(Generator::class, Seeder::faker());
    }

    public function testCallOnEmptySeeder()
    {
        $this->expectException('InvalidArgumentException');

        $seeder = new Seeder(new Database());
        $seeder->call('');
    }
}
