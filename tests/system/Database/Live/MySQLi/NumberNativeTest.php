<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live\MySQLi;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Config\Database;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class NumberNativeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    private $tests;
    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();

        $config = config('Database');

        $this->tests = $config->tests;
    }

    public function testEnableNumberNative()
    {
        $this->tests['numberNative'] = true;

        $db1 = Database::connect($this->tests);

        if ($db1->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        $this->assertTrue($db1->numberNative);
    }

    public function testDisableNumberNative()
    {
        $this->tests['numberNative'] = false;

        $db1 = Database::connect($this->tests);

        if ($db1->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        $this->assertFalse($db1->numberNative);
    }

    public function testQueryDataAfterEnableNumberNative()
    {
        $this->tests['numberNative'] = true;

        $db1 = Database::connect($this->tests);

        if ($db1->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        $data = $db1->table('db_type_test')
            ->get()
            ->getRow();

        $this->assertIsFloat($data->type_float);
        $this->assertIsInt($data->type_integer);
    }

    public function testQueryDataAfterDisableNumberNative()
    {
        $this->tests['numberNative'] = false;

        $db1 = Database::connect($this->tests);

        if ($db1->DBDriver !== 'MySQLi') {
            $this->markTestSkipped('Only MySQLi can complete this test.');
        }

        $data = $db1->table('db_type_test')
            ->get()
            ->getRow();

        $this->assertIsString($data->type_float);
        $this->assertIsString($data->type_integer);
    }
}
