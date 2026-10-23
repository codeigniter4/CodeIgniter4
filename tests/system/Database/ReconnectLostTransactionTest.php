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
use CodeIgniter\Database\MySQLi\Connection as MySQLiConnection;
use CodeIgniter\Database\OCI8\Connection as OCI8Connection;
use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class ReconnectLostTransactionTest extends CIUnitTestCase
{
    public function testReconnectClearsTransactionStateWhenInitializeFails(): void
    {
        $db = new class (['DBDriver' => 'MySQLi']) extends MySQLiConnection {
            public function initialize(): never
            {
                throw new DatabaseException('database unavailable');
            }
        };

        $this->setPrivateProperty($db, 'transDepth', 1);

        try {
            $db->reconnect();
            $this->fail('initialize() should have thrown.');
        } catch (DatabaseException $exception) {
            $this->assertSame('database unavailable', $exception->getMessage());
        }

        $this->assertSame(0, $this->getPrivateProperty($db, 'transDepth'));
        $this->assertFalse($db->transStatus());
    }

    public function testReconnectRestoresOci8CommitModeWhenTransactionIsLost(): void
    {
        if (! defined('OCI_COMMIT_ON_SUCCESS')) {
            define('OCI_COMMIT_ON_SUCCESS', 32);
        }

        if (! defined('OCI_NO_AUTO_COMMIT')) {
            define('OCI_NO_AUTO_COMMIT', 0);
        }

        $db = new class (['DBDriver' => 'OCI8']) extends OCI8Connection {
            public function initialize(): void
            {
            }
        };

        $db->commitMode = OCI_NO_AUTO_COMMIT;
        $this->setPrivateProperty($db, 'transDepth', 1);

        $db->reconnect();

        $this->assertSame(OCI_COMMIT_ON_SUCCESS, $db->commitMode);
        $this->assertSame(0, $this->getPrivateProperty($db, 'transDepth'));
        $this->assertFalse($db->transStatus());
    }
}
