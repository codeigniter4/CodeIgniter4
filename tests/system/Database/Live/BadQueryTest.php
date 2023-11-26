<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class BadQueryTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    public function testBadQueryDebugTrue(): void
    {
        $this->enableDBDebug();

        $this->expectException(DatabaseException::class);

        $this->db->query('SELECT * FROM table_does_not_exist');

        // this code is never executed
    }

    public function testBadQueryDebugFalse(): void
    {
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        // this throws an exception when DBDebug is true, but it'll return FALSE when DBDebug is false
        $query = $this->db->query('SELECT * FROM table_does_not_exist');

        $this->assertFalse($query);

        $this->enableDBDebug();
    }
}
