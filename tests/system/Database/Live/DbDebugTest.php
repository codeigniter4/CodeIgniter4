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

/**
 * @group DatabaseLive
 *
 * @internal
 */
final class DbDebugTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;

    public function testDBDebugTrue(): void
    {
        $this->enableDBDebug();

        $this->expectException(DatabaseException::class);

        $this->db->simpleQuery('SELECT * FROM db_error');
    }

    public function testDBDebugFalse(): void
    {
        // WARNING this value will persist! take care to roll it back.
        $this->disableDBDebug();

        $result = $this->db->simpleQuery('SELECT * FROM db_error');

        $this->assertFalse($result);
    }

    protected function tearDown(): void
    {
        $this->enableDBDebug();

        parent::tearDown();
    }
}
