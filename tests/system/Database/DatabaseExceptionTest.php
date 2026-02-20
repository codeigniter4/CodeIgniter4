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

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class DatabaseExceptionTest extends CIUnitTestCase
{
    public function testIntCodeIsAvailableViaGetCodeAndGetDatabaseCode(): void
    {
        $exception = new DatabaseException('error', 1062);

        $this->assertSame(1062, $exception->getCode());
        $this->assertSame(1062, $exception->getDatabaseCode());
    }

    public function testStringCodeIsAvailableViaGetDatabaseCodeWithoutAffectingGetCode(): void
    {
        $exception = new DatabaseException('error', '23505');

        $this->assertSame(0, $exception->getCode());
        $this->assertSame('23505', $exception->getDatabaseCode());
    }

    public function testStringCodeWithSlashIsAvailableViaGetDatabaseCode(): void
    {
        $exception = new DatabaseException('error', '23000/2601');

        $this->assertSame(0, $exception->getCode());
        $this->assertSame('23000/2601', $exception->getDatabaseCode());
    }
}
