<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers\Database;

use CodeIgniter\Session\Handlers\DatabaseHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\ReflectionHelper;
use Config\Database as DatabaseConfig;
use Tests\Support\Database\Seeds\CITestSeeder;

/**
 * @internal
 */
abstract class AbstractHandlerTestCase extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ReflectionHelper;

    protected $refresh                = true;
    protected $seed                   = CITestSeeder::class;
    protected string $sessionDriver   = DatabaseHandler::class;
    protected string $sessionSavePath = 'ci_sessions';
    protected string $sessionName     = 'ci_session';
    protected string $userIpAddress   = '127.0.0.1';

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(config(DatabaseConfig::class)->tests['DBDriver'], ['MySQLi', 'Postgre'], true)) {
            $this->markTestSkipped('Database Session Handler requires database driver to be MySQLi or Postgre');
        }
    }

    abstract protected function getInstance($options = []);

    public function testOpen(): void
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_sessions', 'ci_session'));
    }

    public function testReadSuccess(): void
    {
        $handler  = $this->getInstance();
        $expected = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';
        $this->assertSame($expected, $handler->read('1f5o06b43phsnnf8if6bo33b635e4p2o'));

        $this->assertTrue($this->getPrivateProperty($handler, 'rowExists'));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testReadFailure(): void
    {
        $handler = $this->getInstance();
        $this->assertSame('', $handler->read('123456b43phsnnf8if6bo33b635e4321'));

        $this->assertFalse($this->getPrivateProperty($handler, 'rowExists'));
        $this->assertSame('d41d8cd98f00b204e9800998ecf8427e', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testWriteInsert(): void
    {
        $handler = $this->getInstance();

        $this->setPrivateProperty($handler, 'lock', true);

        $data = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';
        $this->assertTrue($handler->write('555556b43phsnnf8if6bo33b635e4444', $data));

        $this->setPrivateProperty($handler, 'lock', false);

        $row = $this->db->table('ci_sessions')
            ->getWhere(['id' => 'ci_session:555556b43phsnnf8if6bo33b635e4444'])
            ->getRow();

        $this->assertGreaterThan(time() - 100, strtotime($row->timestamp));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testWriteUpdate(): void
    {
        $handler = $this->getInstance();

        $this->setPrivateProperty($handler, 'sessionID', '1f5o06b43phsnnf8if6bo33b635e4p2o');
        $this->setPrivateProperty($handler, 'rowExists', true);

        $lockSession = $this->getPrivateMethodInvoker($handler, 'lockSession');
        $lockSession('1f5o06b43phsnnf8if6bo33b635e4p2o');

        $data = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';
        $this->assertTrue($handler->write('1f5o06b43phsnnf8if6bo33b635e4p2o', $data));

        $releaseLock = $this->getPrivateMethodInvoker($handler, 'releaseLock');
        $releaseLock();

        $row = $this->db->table('ci_sessions')
            ->getWhere(['id' => 'ci_session:1f5o06b43phsnnf8if6bo33b635e4p2o'])
            ->getRow();

        $this->assertGreaterThan(time() - 100, strtotime($row->timestamp));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testGC(): void
    {
        $handler = $this->getInstance();
        $this->assertSame(1, $handler->gc(3600));
    }
}
