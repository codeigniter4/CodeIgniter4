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

namespace CodeIgniter\Database\Builder;

use CodeIgniter\Database\SQLSRV\Builder;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockConnection;
use PHPUnit\Framework\Attributes\Group;
use Throwable;

/**
 * @internal
 */
#[Group('Others')]
final class SQLSRVBuilderTest extends CIUnitTestCase
{
    public function testUpsertBatchEscapesTableNameInObjectID(): void
    {
        $db      = new MockConnection(['DBDriver' => 'SQLSRV']);
        $builder = new Builder("user's", $db);

        $method = self::getPrivateMethodInvoker($builder, '_upsertBatch');

        try {
            $method("user's", ['id', 'name'], [['1', 'Alice']]);
        } catch (Throwable) {
        }

        $this->assertSame(
            "SELECT name from syscolumns where id = Object_ID('user''s') and colstat = 1",
            (string) $db->getLastQuery(),
        );
    }
}
