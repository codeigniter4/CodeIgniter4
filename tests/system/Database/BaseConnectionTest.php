<?php

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
use CodeIgniter\Test\Mock\MockConnection;
use Throwable;

/**
 * @internal
 */
final class BaseConnectionTest extends CIUnitTestCase
{
    private array $options = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'first',
        'password' => 'last',
        'database' => 'dbname',
        'DBDriver' => 'MockDriver',
        'DBPrefix' => 'test_',
        'pConnect' => true,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
    ];
    private array $failoverOptions = [
        'DSN'      => '',
        'hostname' => 'localhost',
        'username' => 'failover',
        'password' => 'one',
        'database' => 'failover',
        'DBDriver' => 'MockDriver',
        'DBPrefix' => 'test_',
        'pConnect' => true,
        'DBDebug'  => (ENVIRONMENT !== 'production'),
        'charset'  => 'utf8',
        'DBCollat' => 'utf8_general_ci',
        'swapPre'  => '',
        'encrypt'  => false,
        'compress' => false,
        'strictOn' => true,
        'failover' => [],
    ];

    public function testSavesConfigOptions()
    {
        $db = new MockConnection($this->options);

        $this->assertSame('localhost', $db->hostname);
        $this->assertSame('first', $db->username);
        $this->assertSame('last', $db->password);
        $this->assertSame('dbname', $db->database);
        $this->assertSame('MockDriver', $db->DBDriver);
        $this->assertTrue($db->pConnect);
        $this->assertTrue($db->DBDebug);
        $this->assertSame('utf8', $db->charset);
        $this->assertSame('utf8_general_ci', $db->DBCollat);
        $this->assertSame('', $db->swapPre);
        $this->assertFalse($db->encrypt);
        $this->assertFalse($db->compress);
        $this->assertTrue($db->strictOn);
        $this->assertSame([], $db->failover);
    }

    public function testConnectionThrowExceptionWhenCannotConnect()
    {
        try {
            $db = new MockConnection($this->options);
            $db->shouldReturn('connect', false)->initialize();
        } catch (Throwable $e) {
            $this->assertInstanceOf(DatabaseException::class, $e);
            $this->assertStringContainsString('Unable to connect to the database.', $e->getMessage());
        }
    }

    public function testCanConnectAndStoreConnection()
    {
        $db = new MockConnection($this->options);
        $db->shouldReturn('connect', 123)->initialize();

        $this->assertSame(123, $db->getConnection());
    }

    /**
     * @group single
     */
    public function testCanConnectToFailoverWhenNoConnectionAvailable()
    {
        $options             = $this->options;
        $options['failover'] = [$this->failoverOptions];

        $db                         = new class ($options) extends MockConnection {
            protected $returnValues = [
                'connect' => [false, 345],
            ];
        };

        $this->assertSame(345, $db->getConnection());
        $this->assertSame('failover', $db->username);
    }

    public function testStoresConnectionTimings()
    {
        $start = microtime(true);

        $db = new MockConnection($this->options);
        $db->initialize();

        $this->assertGreaterThan($start, $db->getConnectStart());
        $this->assertGreaterThan(0.0, $db->getConnectDuration());
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/5535
     */
    public function testStoresConnectionTimingsNotConnected()
    {
        $db = new MockConnection($this->options);

        $this->assertSame('0.000000', $db->getConnectDuration());
    }

    public function testMagicIssetTrue()
    {
        $db = new MockConnection($this->options);

        $this->assertTrue(isset($db->charset));
    }

    public function testMagicIssetFalse()
    {
        $db = new MockConnection($this->options);

        $this->assertFalse(isset($db->foobar));
    }

    public function testMagicGet()
    {
        $db = new MockConnection($this->options);

        $this->assertSame('utf8', $db->charset);
    }

    public function testMagicGetMissing()
    {
        $db = new MockConnection($this->options);

        $this->assertNull($db->foobar);
    }
}
