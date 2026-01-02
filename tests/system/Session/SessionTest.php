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

namespace CodeIgniter\Session;

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Session\Handlers\FileHandler;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSession;
use CodeIgniter\Test\TestLogger;
use Config\Cookie as CookieConfig;
use Config\Logger as LoggerConfig;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\Attributes\WithoutErrorHandler;

/**
 * @internal
 */
#[Group('SeparateProcess')]
#[PreserveGlobalState(false)]
#[RunTestsInSeparateProcesses]
final class SessionTest extends CIUnitTestCase
{
    #[WithoutErrorHandler]
    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];

        Services::injectMock('superglobals', new Superglobals(null, null, null, []));
    }

    /**
     * @param array<string, bool|int|string|null> $options Replace values for `Config\Session`.
     */
    protected function getInstance($options = []): MockSession
    {
        $defaults = [
            'driver'            => FileHandler::class,
            'cookieName'        => 'ci_session',
            'expiration'        => 7200,
            'savePath'          => '',
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $sessionConfig = new SessionConfig();
        $config        = array_merge($defaults, $options);

        foreach ($config as $key => $value) {
            $sessionConfig->{$key} = $value;
        }
        Factories::injectMock('config', 'Session', $sessionConfig);

        $session = new MockSession(new FileHandler($sessionConfig, '127.0.0.1'), $sessionConfig);
        $session->setLogger(new TestLogger(new LoggerConfig()));

        return $session;
    }

    public function testSessionSetsRegenerateTime(): void
    {
        $session = $this->getInstance();
        $session->start();

        $this->assertTrue(isset($_SESSION['__ci_last_regenerate']) && ! empty($_SESSION['__ci_last_regenerate']));
    }

    public function testWillRegenerateSessionAutomatically(): void
    {
        $session = $this->getInstance();

        $time                             = time() - 400;
        $_SESSION['__ci_last_regenerate'] = $time;
        $session->start();

        $this->assertTrue($session->didRegenerate);
        $this->assertGreaterThan($time + 90, $_SESSION['__ci_last_regenerate']);
    }

    public function testCanSetSingleValue(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testCanSetArray(): void
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
    public function testCanSerializeArray(): void
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

    public function testGetSimpleKey(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $session->get('foo'));
    }

    public function testGetReturnsNullWhenNotFound(): void
    {
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertNull($session->get('foo'));
    }

    public function testGetReturnsNullWhenNotFoundWithXmlHttpRequest(): void
    {
        service('superglobals')->setServer('HTTP_X_REQUESTED_WITH', 'xmlhttprequest');
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertNull($session->get('foo'));
    }

    public function testGetReturnsEmptyArrayWhenWithXmlHttpRequest(): void
    {
        service('superglobals')->setServer('HTTP_X_REQUESTED_WITH', 'xmlhttprequest');
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $this->assertSame([], $session->get());
    }

    public function testGetReturnsItemValueisZero(): void
    {
        $_SESSION = [];

        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 0);

        $this->assertSame(0, $session->get('foo'));
    }

    public function testGetReturnsAllWithNoKeys(): void
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

    public function testGetAsProperty(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $session->foo); // @phpstan-ignore property.notFound
    }

    public function testGetAsNormal(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set('foo', 'bar');

        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testHasReturnsTrueOnSuccess(): void
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertTrue($session->has('foo'));
    }

    public function testHasReturnsFalseOnNotFound(): void
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';

        $this->assertFalse($session->has('bar'));
    }

    public function testIssetReturnsTrueOnSuccess(): void
    {
        $session = $this->getInstance();
        $session->start();
        $_SESSION['foo'] = 'bar';

        $issetReturn = isset($session->foo); // @phpstan-ignore property.notFound

        $this->assertTrue($issetReturn);
    }

    public function testIssetReturnsFalseOnNotFound(): void
    {
        $session = $this->getInstance();
        $session->start();
        $_SESSION['foo'] = 'bar';

        $issetReturn = isset($session->bar); // @phpstan-ignore property.notFound

        $this->assertFalse($issetReturn);
    }

    public function testPushNewValueIntoArraySessionValue(): void
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
            $session->get('hobbies'),
        );
    }

    public function testRemoveActuallyRemoves(): void
    {
        $session = $this->getInstance();
        $session->start();

        $_SESSION['foo'] = 'bar';
        $session->remove('foo');

        $this->assertArrayNotHasKey('foo', $_SESSION);
        $this->assertFalse($session->has('foo'));
    }

    public function testHasReturnsCanRemoveArray(): void
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

    public function testSetMagicMethod(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->foo = 'bar'; // @phpstan-ignore property.notFound

        $this->assertArrayHasKey('foo', $_SESSION);
        $this->assertSame('bar', $_SESSION['foo']);
    }

    public function testCanFlashData(): void
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

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/9535#discussion_r2052022296
     */
    public function testMarkAsFlashdataFailsWhenAtLeastOneKeyIsNotInSession(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set(['foo1' => 'bar1', 'foo2' => 'bar2']);

        $this->assertFalse($session->markAsFlashdata(['foo1', 'foo2', 'foo3']));
        $this->assertArrayNotHasKey('__ci_vars', $_SESSION);
    }

    public function testCanFlashArray(): void
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

    public function testKeepFlashData(): void
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

    public function testUnmarkFlashDataRemovesData(): void
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

    public function testGetFlashKeysOnlyReturnsFlashKeys(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->setFlashdata('foo', 'bar');
        $session->set('bar', 'baz');

        $keys = $session->getFlashKeys();

        $this->assertContains('foo', $keys);
        $this->assertNotContains('bar', $keys);
    }

    public function testSetTempDataWorks(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->setTempdata('foo', 'bar', 300);
        $this->assertGreaterThanOrEqual($_SESSION['__ci_vars']['foo'], time() + 300);
    }

    public function testSetTempDataArrayMultiTTL(): void
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

    public function testSetTempDataArraySingleTTL(): void
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
     * @see https://github.com/codeigniter4/CodeIgniter4/issues/9534
     */
    public function testSetTempDataOnArrayData(): void
    {
        $session = $this->getInstance();
        $session->start();

        $time = time();

        $session->setTempdata(['foo1' => 'bar1'], null, 200);
        $session->setTempdata('foo2', 'bar2', 200);

        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo1'], $time + 200);
        $this->assertLessThanOrEqual($_SESSION['__ci_vars']['foo2'], $time + 200);
    }

    /**
     * @see https://github.com/codeigniter4/CodeIgniter4/pull/9536#discussion_r2051798869
     */
    public function testMarkAsTempdataFailsWhenAtLeastOneKeyIsNotInSession(): void
    {
        $session = $this->getInstance();
        $session->start();

        $session->set(['foo1' => 'bar1', 'foo2' => 'bar2']);

        $this->assertFalse($session->markAsTempdata(['foo1', 'foo2', 'foo3'], 200));
        $this->assertArrayNotHasKey('__ci_vars', $_SESSION);
    }

    public function testGetTestDataReturnsAll(): void
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

    public function testGetTestDataReturnsSingle(): void
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

    public function testRemoveTempDataActuallyDeletes(): void
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

    public function testUnMarkTempDataSingle(): void
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

    public function testUnMarkTempDataArray(): void
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

    public function testGetTempdataKeys(): void
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

    public function testGetDotKey(): void
    {
        $session = $this->getInstance();
        $session->start();
        $session->set('test.1', 'value');
        $this->assertSame('value', $session->get('test.1'));
    }

    public function testLaxSameSite(): void
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

    public function testNoneSameSite(): void
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

    public function testNoSameSiteReturnsDefault(): void
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

    public function testInvalidSameSite(): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidSameSite', ['Invalid']));

        $config           = new CookieConfig();
        $config->samesite = 'Invalid';

        Factories::injectMock('config', CookieConfig::class, $config);

        $session = $this->getInstance();
        $session->start();
    }

    public function testExpires(): void
    {
        $session = $this->getInstance(['expiration' => 8000]);
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertGreaterThan(8000, $cookies[0]->getExpiresTimestamp());
    }

    public function testExpiresOnClose(): void
    {
        $session = $this->getInstance(['expiration' => 0]);
        $session->start();

        $cookies = $session->cookies;
        $this->assertCount(1, $cookies);
        $this->assertSame(0, $cookies[0]->getExpiresTimestamp());
    }
}
