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

namespace CodeIgniter\Database\Live;

use CodeIgniter\Database\Exceptions\UniqueConstraintViolationException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class UniqueConstraintViolationTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function tearDown(): void
    {
        $this->enableDBDebug();

        parent::tearDown();
    }

    public function testThrowsUniqueConstraintViolationExceptionWithDebugEnabled(): void
    {
        $this->enableDBDebug();

        $this->expectException(UniqueConstraintViolationException::class);

        // 'derek@world.com' is already seeded in the user table
        $this->db->table('user')->insert([
            'name'    => 'Duplicate',
            'email'   => 'derek@world.com',
            'country' => 'US',
        ]);
    }

    public function testReturnsFalseAndErrorIsPopulatedWithDebugDisabled(): void
    {
        $this->disableDBDebug();

        // 'derek@world.com' is already seeded in the user table
        $result = $this->db->table('user')->insert([
            'name'    => 'Duplicate',
            'email'   => 'derek@world.com',
            'country' => 'US',
        ]);

        $this->assertFalse($result);

        $error = $this->db->error();

        $expectedCode = match ($this->db->DBDriver) {
            'MySQLi'  => 1062,
            'Postgre' => '23505',
            'SQLite3' => 19,
            'SQLSRV'  => '23000/2627',
            'OCI8'    => 1,
            default   => $this->fail('No expected error code defined for DB driver: ' . $this->db->DBDriver),
        };

        $this->assertSame($expectedCode, $error['code']);
        $this->assertNotEmpty($error['message']);

        $exception = $this->db->getLastException();
        $this->assertInstanceOf(UniqueConstraintViolationException::class, $exception);
        $this->assertSame($expectedCode, $exception->getDatabaseCode());
    }
}
