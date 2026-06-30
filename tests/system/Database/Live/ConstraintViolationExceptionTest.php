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

use CodeIgniter\Database\Exceptions\NotNullConstraintViolationException;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ConstraintViolationExceptionTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $refresh = true;
    protected $seed    = CITestSeeder::class;

    protected function tearDown(): void
    {
        $this->enableDBDebug();

        parent::tearDown();
    }

    public function testThrowsNotNullConstraintViolationExceptionWithDebugEnabled(): void
    {
        $this->enableDBDebug();

        $this->expectException(NotNullConstraintViolationException::class);

        $this->db->table('user')->insert([
            'name'    => null,
            'email'   => 'not-null@example.com',
            'country' => 'US',
        ]);
    }

    public function testStoresNotNullConstraintViolationExceptionWithDebugDisabled(): void
    {
        $this->disableDBDebug();

        $result = $this->db->table('user')->insert([
            'name'    => null,
            'email'   => 'not-null@example.com',
            'country' => 'US',
        ]);

        $this->assertFalse($result);
        $this->assertInstanceOf(NotNullConstraintViolationException::class, $this->db->getLastException());
    }
}
