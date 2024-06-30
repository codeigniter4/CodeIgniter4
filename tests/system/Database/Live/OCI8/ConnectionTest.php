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

namespace CodeIgniter\Database\Live\OCI8;

use CodeIgniter\Database\OCI8\Connection;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Database as DbConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('DatabaseLive')]
final class ConnectionTest extends CIUnitTestCase
{
    /**
     * @var array<string, mixed> Database connection settings
     */
    private array $settings = [];

    protected function setUp(): void
    {
        $dbConfig       = config(DbConfig::class);
        $this->settings = $dbConfig->{$this->DBGroup};

        if ($this->settings['DBDriver'] !== 'OCI8') {
            $this->markTestSkipped('This test is only for OCI8.');
        }
    }

    #[DataProvider('provideIsValidDSN')]
    public function testIsValidDSN(string $dsn): void
    {
        $this->settings['DSN'] = $dsn;

        $db = new Connection($this->settings);

        $isValidDSN = $this->getPrivateMethodInvoker($db, 'isValidDSN');

        $this->assertTrue($isValidDSN());
    }

    /**
     * @return array<string, list<string>>
     */
    public static function provideIsValidDSN(): iterable
    {
        yield from [
            // Easy Connect string
            // See https://docs.oracle.com/en/database/oracle/oracle-database/23/netag/configuring-naming-methods.html#GUID-36F3A17D-843C-490A-8A23-FB0FE005F8E8
            'HostOnly'                                 => ['sales-server'],
            'Host:Port'                                => ['sales-server:3456'],
            'Host/ServiceName'                         => ['sales-server/sales'],
            'IPv6Address:Port/ServiceName'             => ['[2001:0db8:0:0::200C:417A]:80/sales'],
            'Host:Port/ServiceName'                    => ['sales-server:80/sales'],
            'Host/ServiceName:ServerType/InstanceName' => ['sales-server/sales:dedicated/inst1'],
            'Host:InstanceName'                        => ['sales-server//inst1'],
            'Host/ServiceNameWithDots'                 => ['myhost/my.service.name'],
            'Host:Port/ServiceNameWithDots'            => ['myhost:1521/my.service.name'],
        ];
    }
}
