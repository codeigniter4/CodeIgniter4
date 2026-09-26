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

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Database\Config;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class WorkerModeTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->setPrivateProperty(Config::class, 'instances', []);
    }

    public function testCleanupForWorkerMode(): void
    {
        $conn = Config::connect();
        $this->assertInstanceOf(BaseConnection::class, $conn);

        $conn->transStart();
        $this->assertGreaterThan(0, $conn->transDepth);

        Config::cleanupForWorkerMode();

        $this->assertSame(0, $conn->transDepth);
        $this->assertNotFalse($this->getPrivateProperty($conn, 'connID'));
    }

    public function testReconnectForWorkerMode(): void
    {
        $conn = Config::connect();
        $this->assertInstanceOf(BaseConnection::class, $conn);
        $this->assertNotFalse($this->getPrivateProperty($conn, 'connID'));

        Config::reconnectForWorkerMode();

        $this->assertNotFalse($this->getPrivateProperty($conn, 'connID'));
    }
}
