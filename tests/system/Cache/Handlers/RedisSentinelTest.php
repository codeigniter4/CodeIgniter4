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

namespace CodeIgniter\Cache\Handlers;

use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RequiresPhpExtension;
use PHPUnit\Framework\TestCase;
use RuntimeException;

/**
 * @internal
 */
#[Group('CacheLive')]
final class RedisSentinelTest extends TestCase
{
    public function testDiscoverMasterThrowsWhenNoNodes(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No Redis Sentinel nodes configured.');

        RedisSentinel::discoverMaster([], 'mymaster');
    }

    #[RequiresPhpExtension('redis')]
    public function testDiscoverMasterThrowsWhenAllNodesUnreachable(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Redis Sentinel unable to discover master "mymaster".');

        // A port that nothing listens on and a short timeout to keep it fast.
        RedisSentinel::discoverMaster(
            [['host' => '127.0.0.1', 'port' => 1]],
            'mymaster',
            0.2,
        );
    }

    /**
     * Live test that runs when the redis extension is loaded (see the method-level
     * RequiresPhpExtension attribute). It assumes a local Redis Sentinel is
     * reachable at 127.0.0.1:26379 monitoring the "mymaster" service, mirroring
     * how the other live Redis tests assume a server on 127.0.0.1:6379.
     */
    #[RequiresPhpExtension('redis')]
    public function testDiscoverMasterLive(): void
    {
        $address = RedisSentinel::discoverMaster(
            [['host' => '127.0.0.1', 'port' => 26379]],
            'mymaster',
            0.5,
        );

        $this->assertCount(2, $address);
        $this->assertIsString($address[0]);
        $this->assertIsInt($address[1]);
        $this->assertGreaterThan(0, $address[1]);
    }
}
