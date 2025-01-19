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

namespace CodeIgniter\Database\Live\SQLite3;

use CodeIgniter\Exceptions\InvalidArgumentException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ConnectTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->db = Database::connect($this->DBGroup);

        if ($this->db->DBDriver !== 'SQLite3') {
            $this->markTestSkipped('This test is only for SQLite3.');
        }
    }

    public function testShowErrorMessageWhenSettingInvalidSynchronous(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid synchronous value.');

        $config = config('Database');
        $group  = $config->tests;
        // Sets invalid synchronous.
        $group['synchronous'] = 123;
        $db                   = Database::connect($group);

        // Actually connect to DB.
        $db->initialize();
    }
}
