<?php

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

    /**
     * @TODO remove this when Seeder::faker() is removed
     */
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
