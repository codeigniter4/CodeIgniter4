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

namespace CodeIgniter\Session\Handlers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\TestLogger;
use Config\Cookie as CookieConfig;
use Config\Logger as LoggerConfig;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class BaseHandlerTest extends CIUnitTestCase
{
    private string $sessionName     = 'ci_session';
    private string $sessionSavePath = '/tmp';
    private string $userIpAddress   = '127.0.0.1';

    /**
     * @param array<string, bool|int|string|null> $options
     */
    private function createHandler(array $options = []): ArrayHandler
    {
        $defaults = [
            'driver'            => ArrayHandler::class,
            'cookieName'        => $this->sessionName,
            'expiration'        => 7200,
            'savePath'          => $this->sessionSavePath,
            'matchIP'           => false,
            'timeToUpdate'      => 300,
            'regenerateDestroy' => false,
        ];
        $sessionConfig = new SessionConfig();
        $config        = array_merge($defaults, $options);

        foreach ($config as $key => $value) {
            $sessionConfig->{$key} = $value;
        }

        $handler = new ArrayHandler($sessionConfig, $this->userIpAddress);
        $handler->setLogger(new TestLogger(new LoggerConfig()));

        return $handler;
    }

    // ========== Constructor ==========

    public function testConstructorSetsCookieName(): void
    {
        $handler = $this->createHandler(['cookieName' => 'my_cookie']);

        $this->assertSame('my_cookie', $this->getPrivateProperty($handler, 'cookieName'));
    }

    public function testConstructorSetsMatchIP(): void
    {
        $handler = $this->createHandler(['matchIP' => true]);

        $this->assertTrue($this->getPrivateProperty($handler, 'matchIP'));
    }

    public function testConstructorSetsMatchIPDefaultFalse(): void
    {
        $handler = $this->createHandler();

        $this->assertFalse($this->getPrivateProperty($handler, 'matchIP'));
    }

    public function testConstructorSetsSavePath(): void
    {
        $handler = $this->createHandler(['savePath' => '/custom/path']);

        $this->assertSame('/custom/path', $this->getPrivateProperty($handler, 'savePath'));
    }

    public function testConstructorSetsIpAddress(): void
    {
        $handler = $this->createHandler();

        $this->assertSame('127.0.0.1', $this->getPrivateProperty($handler, 'ipAddress'));
    }

    public function testConstructorSetsCookieDomainFromConfig(): void
    {
        $config  = new CookieConfig();
        $handler = $this->createHandler();

        $this->assertSame($config->domain, $this->getPrivateProperty($handler, 'cookieDomain'));
    }

    public function testConstructorSetsCookiePathFromConfig(): void
    {
        $config  = new CookieConfig();
        $handler = $this->createHandler();

        $this->assertSame($config->path, $this->getPrivateProperty($handler, 'cookiePath'));
    }

    public function testConstructorSetsCookieSecureFromConfig(): void
    {
        $config  = new CookieConfig();
        $handler = $this->createHandler();

        $this->assertSame($config->secure, $this->getPrivateProperty($handler, 'cookieSecure'));
    }

    public function testConstructorSetsCookiePrefixEmpty(): void
    {
        $handler = $this->createHandler();

        $this->assertSame('', $this->getPrivateProperty($handler, 'cookiePrefix'));
    }

    // ========== lockSession ==========

    public function testLockSessionSetsLockTrue(): void
    {
        $handler = $this->createHandler();
        $this->setPrivateProperty($handler, 'lock', false);

        $lockSession = $this->getPrivateMethodInvoker($handler, 'lockSession');
        $result      = $lockSession('test-session-id');

        $this->assertTrue($result);
        $this->assertTrue($this->getPrivateProperty($handler, 'lock'));
    }

    public function testLockSessionCalledMultipleTimes(): void
    {
        $handler = $this->createHandler();

        $lockSession = $this->getPrivateMethodInvoker($handler, 'lockSession');

        $this->assertTrue($lockSession('id-1'));
        $this->assertTrue($this->getPrivateProperty($handler, 'lock'));
        $this->assertTrue($lockSession('id-2'));
        $this->assertTrue($this->getPrivateProperty($handler, 'lock'));
    }

    // ========== releaseLock ==========

    public function testReleaseLockSetsLockFalse(): void
    {
        $handler = $this->createHandler();
        $this->setPrivateProperty($handler, 'lock', true);

        $releaseLock = $this->getPrivateMethodInvoker($handler, 'releaseLock');
        $result      = $releaseLock();

        $this->assertTrue($result);
        $this->assertFalse($this->getPrivateProperty($handler, 'lock'));
    }

    public function testReleaseLockWhenAlreadyUnlocked(): void
    {
        $handler = $this->createHandler();
        $this->setPrivateProperty($handler, 'lock', false);

        $releaseLock = $this->getPrivateMethodInvoker($handler, 'releaseLock');
        $result      = $releaseLock();

        $this->assertTrue($result);
        $this->assertFalse($this->getPrivateProperty($handler, 'lock'));
    }

    // ========== fail ==========

    public function testFailReturnsFalse(): void
    {
        $handler = $this->createHandler();
        $fail    = $this->getPrivateMethodInvoker($handler, 'fail');

        $this->assertFalse($fail());
    }

    public function testFailSetsIniSavePath(): void
    {
        $handler = $this->createHandler(['savePath' => '/test/path']);
        $fail    = $this->getPrivateMethodInvoker($handler, 'fail');

        $fail();

        $this->assertSame('/test/path', ini_get('session.save_path'));
    }

    // ========== destroyCookie ==========

    public function testDestroyCookieReturnsBool(): void
    {
        $handler       = $this->createHandler(['cookieName' => 'test_cookie']);
        $destroyCookie = $this->getPrivateMethodInvoker($handler, 'destroyCookie');

        $this->assertIsBool($destroyCookie());
    }

    public function testDestroyCookieUsesConfiguredName(): void
    {
        $handler       = $this->createHandler(['cookieName' => 'custom_session_name']);
        $destroyCookie = $this->getPrivateMethodInvoker($handler, 'destroyCookie');

        $destroyCookie();

        $this->assertHeaderEmitted('Set-Cookie: custom_session_name=');
    }

    public function testDestroyCookieSetsEmptyCookie(): void
    {
        $handler       = $this->createHandler(['cookieName' => 'empty_test']);
        $destroyCookie = $this->getPrivateMethodInvoker($handler, 'destroyCookie');

        $destroyCookie();

        $this->assertHeaderEmitted('Set-Cookie: empty_test=');
    }

    // ========== Logger (LoggerAwareTrait) ==========

    public function testLoggerIsAccessible(): void
    {
        $handler = $this->createHandler();

        $this->assertInstanceOf(TestLogger::class, $this->getPrivateProperty($handler, 'logger'));
    }
}
