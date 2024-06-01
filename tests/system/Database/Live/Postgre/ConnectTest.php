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

namespace CodeIgniter\Database\Live\Postgre;

use CodeIgniter\Database\Exceptions\DatabaseException;
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

        if ($this->db->DBDriver !== 'Postgre') {
            $this->markTestSkipped('This test is only for Postgre.');
        }
    }

    public function testShowErrorMessageWhenSettingInvalidCharset(): void
    {
        $this->expectException(DatabaseException::class);
        $this->expectExceptionMessage(
            'Unable to connect to the database.
Main connection [Postgre]: ERROR:  invalid value for parameter "client_encoding": "utf8mb4"'
        );

        $config = config('Database');
        $group  = $config->tests;
        // Sets invalid charset.
        $group['charset'] = 'utf8mb4';
        $db               = Database::connect($group);

        // Actually connect to DB.
        $db->initialize();
    }
}
