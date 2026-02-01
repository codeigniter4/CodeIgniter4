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

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class PingTest extends CIUnitTestCase
{
    use DatabaseTestTrait;

    protected $migrate = false;

    public function testPingReturnsTrueWhenConnected(): void
    {
        $this->db->initialize();

        $result = $this->db->ping();

        $this->assertTrue($result);
    }

    public function testPingReturnsFalseWhenNotConnected(): void
    {
        $this->db->close();

        $this->setPrivateProperty($this->db, 'connID', false);

        $result = $this->db->ping();

        $this->assertFalse($result);
    }

    public function testPingAfterReconnect(): void
    {
        $this->db->close();
        $this->db->reconnect();

        $result = $this->db->ping();

        $this->assertTrue($result);
    }

    public function testPingCanBeUsedToCheckConnectionBeforeQuery(): void
    {
        if ($this->db->ping()) {
            $sql    = $this->db->DBDriver === 'OCI8' ? 'SELECT 1 FROM DUAL' : 'SELECT 1';
            $result = $this->db->query($sql);
            $this->assertNotFalse($result);
        } else {
            $this->fail('Connection should be alive');
        }
    }
}
