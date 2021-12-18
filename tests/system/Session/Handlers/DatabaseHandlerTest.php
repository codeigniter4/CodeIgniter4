<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\ReflectionHelper;
use Config\App as AppConfig;
use Config\Database as DatabaseConfig;

/**
 * @internal
 */
final class DatabaseHandlerTest extends CIUnitTestCase
{
    use DatabaseTestTrait;
    use ReflectionHelper;

    protected $refresh = true;
    protected $seed    = 'Tests\Support\Database\Seeds\CITestSeeder';

    protected function setUp(): void
    {
        parent::setUp();

        if (! in_array(config(DatabaseConfig::class)->tests['DBDriver'], ['MySQLi', 'Postgre'], true)) {
            $this->markTestSkipped('Database Session Handler requires database driver to be MySQLi or Postgre');
        }
    }

    protected function getInstance($options = [])
    {
        $defaults = [
            'sessionDriver'            => 'CodeIgniter\Session\Handlers\DatabaseHandler',
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => 'ci_sessions',
            'sessionMatchIP'           => false,
            'sessionTimeToUpdate'      => 300,
            'sessionRegenerateDestroy' => false,
            'cookieDomain'             => '',
            'cookiePrefix'             => '',
            'cookiePath'               => '/',
            'cookieSecure'             => false,
            'cookieSameSite'           => 'Lax',
        ];

        $config    = array_merge($defaults, $options);
        $appConfig = new AppConfig();

        foreach ($config as $key => $c) {
            $appConfig->{$key} = $c;
        }

        return new DatabaseHandler($appConfig, '127.0.0.1');
    }

    public function testOpen()
    {
        $handler = $this->getInstance();
        $this->assertTrue($handler->open('ci_sessions', 'ci_session'));
    }

    public function testReadSuccess()
    {
        $handler  = $this->getInstance();
        $expected = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';
        $this->assertSame($expected, $handler->read('1f5o06b43phsnnf8if6bo33b635e4p2o'));

        $this->assertTrue($this->getPrivateProperty($handler, 'rowExists'));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testReadFailure()
    {
        $handler = $this->getInstance();
        $this->assertSame('', $handler->read('123456b43phsnnf8if6bo33b635e4321'));

        $this->assertFalse($this->getPrivateProperty($handler, 'rowExists'));
        $this->assertSame('d41d8cd98f00b204e9800998ecf8427e', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testWriteInsert()
    {
        $handler = $this->getInstance();

        $this->setPrivateProperty($handler, 'lock', true);

        $data = '__ci_last_regenerate|i:1624650854;_ci_previous_url|s:40:\"http://localhost/index.php/home/index\";';
        $this->assertTrue($handler->write('555556b43phsnnf8if6bo33b635e4444', $data));

        $this->setPrivateProperty($handler, 'lock', false);

        $row = $this->db->table('ci_sessions')
            ->getWhere(['id' => '555556b43phsnnf8if6bo33b635e4444'])
            ->getRow();

        $this->assertGreaterThan(time() - 100, strtotime($row->timestamp));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testWriteUpdate()
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
            ->getWhere(['id' => '1f5o06b43phsnnf8if6bo33b635e4p2o'])
            ->getRow();

        $this->assertGreaterThan(time() - 100, strtotime($row->timestamp));
        $this->assertSame('1483201a66afd2bd671e4a67dc6ecf24', $this->getPrivateProperty($handler, 'fingerprint'));
    }

    public function testGC()
    {
        $handler = $this->getInstance();
        $this->assertSame(1, $handler->gc(3600));
    }
}
