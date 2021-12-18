<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Session;

use CodeIgniter\Config\Factories;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\App as AppConfig;
use Config\Cookie as CookieConfig;
use Config\Logger as LoggerConfig;

/**
 * @runTestsInSeparateProcesses
 *
 * @preserveGlobalState disabled
 *
 * @internal
 */
final class SessionTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $_COOKIE  = [];
        $_SESSION = [];
    }

    protected function getInstance($options = [])
    {
        $defaults = [
            'sessionDriver'            => 'CodeIgniter\Session\Handlers\FileHandler',
            'sessionCookieName'        => 'ci_session',
            'sessionExpiration'        => 7200,
            'sessionSavePath'          => null,
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

        $session = new MockSession(new FileHandler($appConfig, '127.0.0.1'), $appConfig);
        $session->setLogger(new TestLogger(new LoggerConfig()));

        return $session;
    }

    public function testSessionSetsRegenerateTime()
    {
        $session = $this->getInstance();
        $session->start();

        $this->assertTrue(isset($_SESSION['__ci_last_regenerate']) && ! empty($_SESSION['__ci_last_regenerate']));
    }

    public function testWillRegenerateSessionAutomatically()
    {
        $session = $this->getInstance();

        $time                             = time() - 400;
        $_SESSION['__ci_last_regenerate'] = $time;
        $session->start();

        $this->assertTrue($session->didRegenerate);
        $this->assertGreaterThan($time + 90, $_SESSION['__ci_last_regenerate']);
    }

    public function testCanSetSingleValue()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testCanSetArray()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertSame('bar', $_SESSION['foo']);
        $this->assertSame('baz', $_SESSION['bar']);
        $this->assertArrayNotHasKey('__ci_vars', $_SESSION);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/1492
     */
    public function testCanSerializeArray()
    {
        $session = $this->getInstance();
        $session->start();

        $locations = [
            'AB' => 'Alberta',
            'BC' => 'British Columbia',
            'SK' => 'Saskatchewan',
        ];
        $session->set(['_ci_old_input' => ['location' => $locations]]);

        $this->assertSame($locations, $session->get('_ci_old_input')['location']);
    }

    public function testGetSimpleKey()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $session->get('foo'));
    }

    public function testGetReturnsNullWhenNotFound()
    {
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertNull($session->get('foo'));
    }

    public function testGetReturnsNullWhenNotFoundWithXmlHttpRequest()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $_SESSION                         = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertNull($session->get('foo'));
    }

    public function testGetReturnsEmptyArrayWhenWithXmlHttpRequest()
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'xmlhttprequest';
        $_SESSION                         = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertSame([], $session->get());
    }

    public function testGetReturnsItemValueisZero()
    {
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 0);

        $this->assertSame(0, $session->get('foo'));
    }

    public function testGetReturnsAllWithNoKeys()
    {
        $_SESSION = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session = $this->getInstance();
        $session->start();

        $result = $session->get();

        $this->assertArrayHasKey('foo', $result);
        $this->assertArrayHasKey('bar', $result);
    }

    public function testGetAsProperty()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $session->foo);
    }

    public function testGetAsNormal()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testHasReturnsTrueOnSuccess()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertTrue($session->has('foo'));
    }

    public function testHasReturnsFalseOnNotFound()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertFalse($session->has('bar'));
    }

    public function testIssetReturnsTrueOnSuccess()
    {
        $session = $this->getInstance();
        $session->start();
        $_SESSION['foo'] = 'bar';

        $issetReturn = isset($session->foo);

        $this->assertTrue($issetReturn);
    }

    public function testIssetReturnsFalseOnNotFound()
    {
        $session = $this->getInstance();
        $session->start();
        $_SESSION['foo'] = 'bar';

        $issetReturn = isset($session->bar);

        $this->assertFalse($issetReturn);
    }

    public function testPushNewValueIntoArraySessionValue()
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('hobbies', ['cooking' => 'baking']);
        $session->push('hobbies', ['sport' => 'tennis']);

        $this->assertSame(
            [
                'cooking' => 'baking',
                'sport'   => 'tennis',
            ],
            $session->get('hobbies')
        );
    }

    public function testRemoveActuallyRemoves()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';
        $session->remove('foo');

        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertFalse($session->has('foo'));
    }

    public function testHasReturnsCanRemoveArray()
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $this->assertTrue($session->has('foo'));

        $session->remove(['foo', 'bar']);

        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertArrayNotHasKey('bar', $_SESSION);
    }

    public function testSetMagicMethod()
    {
        $session = $this->getInstance();
        $session->start();

        $session->foo = 'bar';

        $this->assertArrayHasKey('foo', $_SESSION);
        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testCanFlashData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');

        $this->assertTrue($session->has('foo'));
        $this->assertSame('new', $_SESSION['__ci_vars']['foo']);

        // Should reset the 'new' to 'old'
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertSame('old', $_SESSION['__ci_vars']['foo']);

        // Should no longer be available
        $session->start();

        $this->assertFalse($session->has('foo'));
    }

    public function testCanFlashArray()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata([
            'foo' => 'bar',
            'bar' => 'baz',
        ]);

        $this->assertTrue($session->has('foo'));
        $this->assertSame('new', $_SESSION['__ci_vars']['foo']);
        $this->assertTrue($session->has('bar'));
        $this->assertSame('new', $_SESSION['__ci_vars']['bar']);
    }

    public function testKeepFlashData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');

        $this->assertTrue($session->has('foo'));
        $this->assertSame('new', $_SESSION['__ci_vars']['foo']);

        // Should reset the 'new' to 'old'
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertSame('old', $_SESSION['__ci_vars']['foo']);

        $session->keepFlashdata('foo');

        $this->assertSame('new', $_SESSION['__ci_vars']['foo']);

        // Should no longer be available
        $session->start();

        $this->assertTrue($session->has('foo'));
        $this->assertSame('old', $_SESSION['__ci_vars']['foo']);
    }

    public function testUnmarkFlashDataRemovesData()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');
        $session->set('bar', 'baz');

        $this->assertTrue($session->has('foo'));
        $this->assertArrayHasKey('foo', $_SESSION['__ci_vars']);

        $session->unmarkFlashdata('foo');

        // Should still be here
        $this->assertTrue($session->has('foo'));
        // but no longer marked as flash
        $issetReturn = isset($_SESSION['__ci_vars']['foo']);
        $this->assertFalse($issetReturn);
    }

    public function testGetFlashKeysOnlyReturnsFlashKeys()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');
        $session->set('bar', 'baz');

        $keys = $session->getFlashKeys();

        $this->assertContains('foo', $keys);
        $this->assertNotContains('bar', $keys);
    }

    public function testSetTempDataWorks()
    {
        $session = $this->getInstance();
        $session->start();

        $session->setTempdata('foo', 'bar', 300);
        $this->assertGreaterThanOrEqual($_SESSION['__ci_vars']['foo'], time() + 300);
    }

    public function testSetTempDataArrayMultiTTL()
    {
        $session = $this->getInstance();
        $session->start();

        $time = time();

        $session->setTempdata([
            'foo' => 300,
            'bar' => 400,
            'baz' => 100,
        ]);

        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo'], $time + 300);
        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['bar'], $time + 400);
        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['baz'], $time + 100);
    }

    public function testSetTempDataArraySingleTTL()
    {
        $session = $this->getInstance();
        $session->start();

        $time = time();

        $session->setTempdata(['foo', 'bar', 'baz'], null, 200);

        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo'], $time + 200);
        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['bar'], $time + 200);
        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['baz'], $time + 200);
    }

    /**
     * @group single
     */
    public function testGetTestDataReturnsAll()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);
        $session->set('baz', 'ballywhoo');

        $this->assertSame($data, $session->getTempdata());
    }

    public function testGetTestDataReturnsSingle()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);

        $this->assertSame('bar', $session->getTempdata('foo'));
    }

    public function testRemoveTempDataActuallyDeletes()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);
        $session->removeTempdata('foo');

        $this->assertSame(['bar' => 'baz'], $session->getTempdata());
    }

    public function testUnMarkTempDataSingle()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);
        $session->unmarkTempdata('foo');

        $this->assertSame(['bar' => 'baz'], $session->getTempdata());
    }

    public function testUnMarkTempDataArray()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);
        $session->unmarkTempdata(['foo', 'bar']);

        $this->assertSame([], $session->getTempdata());
    }

    public function testGetTempdataKeys()
    {
        $session = $this->getInstance();
        $session->start();

        $data = [
            'foo' => 'bar',
            'bar' => 'baz',
        ];

        $session->setTempdata($data);
        $session->set('baz', 'ballywhoo');

        $this->assertSame(['foo', 'bar'], $session->getTempKeys());
    }

    public function testGetDotKey()
    {
        $session = $this->getInstance();
        $session->start();
        $session->set('test.1', 'value');
        $this->assertSame('value', $session->get('test.1'));
    }

    public function testLaxSameSite()
    {
        $config           = new CookieConfig();
        $config->samesite = 'Lax';
        Factories::injectMock('config', CookieConfig::class, $config);

        $session = $this->getInstance();
        $session->start();
        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertSame('Lax', $cookies[0]->getSameSite());
    }

    public function testNoneSameSite()
    {
        $config           = new CookieConfig();
        $config->secure   = true;
        $config->samesite = 'None';

        Factories::injectMock('config', CookieConfig::class, $config);

        $session = $this->getInstance();
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertSame('None', $cookies[0]->getSameSite());
    }

    public function testNoSameSiteReturnsDefault()
    {
        $config           = new CookieConfig();
        $config->samesite = '';

        Factories::injectMock('config', CookieConfig::class, $config);

        $session = $this->getInstance();
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertSame('Lax', $cookies[0]->getSameSite());
    }

    public function testInvalidSameSite()
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidSameSite', ['Invalid']));

        $config           = new CookieConfig();
        $config->samesite = 'Invalid';

        Factories::injectMock('config', CookieConfig::class, $config);

        $session = $this->getInstance();
        $session->start();
    }

    public function testExpires()
    {
        $session = $this->getInstance(['sessionExpiration' => 8000]);
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertGreaterThan(8000, $cookies[0]->getExpiresTimestamp());
    }

    public function testExpiresOnClose()
    {
        $session = $this->getInstance(['sessionExpiration' => 0]);
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertSame(0, $cookies[0]->getExpiresTimestamp());
    }
}
