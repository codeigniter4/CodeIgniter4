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

use CodeIgniter\Config\Factories;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cookie as CookieConfig;
use Config\Session as SessionConfig;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * Mock setcookie function in CodeIgniter\Session\Handlers namespace.
 *
 * The mock is only active while BaseHandlerTest is running, so that calls
 * from other handler tests are delegated to the native function.
 *
 * @param array<string, mixed> $options
 */
function setcookie(string $name, string $value = '', array $options = []): bool
{
    if (! BaseHandlerTest::$interceptSetCookie) {
        return \setcookie($name, $value, $options);
    }

    BaseHandlerTest::$lastSetCookieArgs = [
        'name'    => $name,
        'value'   => $value,
        'options' => $options,
    ];

    return true;
}

/**
 * @internal
 */
#[Group('Others')]
final class BaseHandlerTest extends CIUnitTestCase
{
    /**
     * Whether the setcookie() mock in this namespace should intercept calls.
     */
    public static bool $interceptSetCookie = false;

    /**
     * @var array{name: string, value: string, options: array<string, mixed>}|null
     */
    public static ?array $lastSetCookieArgs = null;

    protected function setUp(): void
    {
        parent::setUp();

        self::$interceptSetCookie = true;
        self::$lastSetCookieArgs  = null;
        Factories::reset('config');
    }

    protected function tearDown(): void
    {
        // Reset before parent::tearDown() so a failure in the parent does not
        // leave the setcookie() mock intercepting calls in later tests.
        self::$interceptSetCookie = false;
        self::$lastSetCookieArgs  = null;

        parent::tearDown();

        Factories::reset('config');
    }

    private function createHandler(?CookieConfig $cookieConfig = null): BaseHandler
    {
        if ($cookieConfig instanceof CookieConfig) {
            Factories::injectMock('config', CookieConfig::class, $cookieConfig);
        }

        $sessionConfig             = new SessionConfig();
        $sessionConfig->cookieName = 'ci_session';

        return new class ($sessionConfig, '127.0.0.1') extends BaseHandler {
            public function open(string $path, string $name): bool
            {
                return true;
            }

            public function close(): bool
            {
                return true;
            }

            public function read(string $id): string
            {
                return '';
            }

            public function write(string $id, string $data): bool
            {
                return true;
            }

            public function destroy(string $id): bool
            {
                return $this->destroyCookie();
            }

            public function gc(int $maxLifetime): int
            {
                return 1;
            }
        };
    }

    public function testConstructorInitializesCookieSameSiteFromConfig(): void
    {
        $cookieConfig           = new CookieConfig();
        $cookieConfig->samesite = 'Strict';

        $handler = $this->createHandler($cookieConfig);

        $this->assertSame('Strict', $this->getPrivateProperty($handler, 'cookieSameSite'));
    }

    public function testConstructorDefaultsToCookieConfigSameSite(): void
    {
        $handler = $this->createHandler();

        $this->assertSame('Lax', $this->getPrivateProperty($handler, 'cookieSameSite'));
    }

    public function testDestroyCookiePassesSameSiteInOptions(): void
    {
        $cookieConfig           = new CookieConfig();
        $cookieConfig->domain   = 'example.com';
        $cookieConfig->path     = '/app';
        $cookieConfig->secure   = true;
        $cookieConfig->samesite = 'Strict';

        $handler = $this->createHandler($cookieConfig);
        $result  = $handler->destroy('ci_session');

        $this->assertTrue($result);
        $this->assertNotNull(self::$lastSetCookieArgs);
        $this->assertSame('ci_session', self::$lastSetCookieArgs['name']);
        $this->assertSame('', self::$lastSetCookieArgs['value']);

        $options = self::$lastSetCookieArgs['options'];
        $this->assertSame(1, $options['expires']);
        $this->assertSame('/app', $options['path']);
        $this->assertSame('example.com', $options['domain']);
        $this->assertTrue($options['secure']);
        $this->assertTrue($options['httponly']);
        $this->assertSame('Strict', $options['samesite']);
    }

    public function testSetCookieMockDelegatesWhenInterceptDisabled(): void
    {
        self::$interceptSetCookie = false;

        $handler = $this->createHandler();
        $handler->destroy('ci_session');

        $this->assertNull(self::$lastSetCookieArgs);
    }

    public function testDestroyCookieNormalizesEmptySameSiteToLax(): void
    {
        $cookieConfig           = new CookieConfig();
        $cookieConfig->samesite = '';

        $handler = $this->createHandler($cookieConfig);
        $handler->destroy('ci_session');

        $this->assertNotNull(self::$lastSetCookieArgs);
        $this->assertSame('Lax', self::$lastSetCookieArgs['options']['samesite']);
    }

    public function testDestroyCookieNormalizesLowercaseSameSite(): void
    {
        $handler = $this->createHandler();
        $this->setPrivateProperty($handler, 'cookieSameSite', 'none');
        $handler->destroy('ci_session');

        $this->assertNotNull(self::$lastSetCookieArgs);
        $this->assertSame('None', self::$lastSetCookieArgs['options']['samesite']);
    }

    #[DataProvider('provideDestroyCookieNormalizesSameSiteCase')]
    public function testDestroyCookieNormalizesSameSiteCase(string $input, string $expected): void
    {
        $handler = $this->createHandler();
        $this->setPrivateProperty($handler, 'cookieSameSite', $input);
        $handler->destroy('ci_session');

        $this->assertNotNull(self::$lastSetCookieArgs);
        $this->assertSame($expected, self::$lastSetCookieArgs['options']['samesite']);
    }

    /**
     * @return iterable<string, array{string, string}>
     */
    public static function provideDestroyCookieNormalizesSameSiteCase(): iterable
    {
        yield 'uppercase LAX' => ['LAX', 'Lax'];

        yield 'uppercase STRICT' => ['STRICT', 'Strict'];

        yield 'uppercase NONE' => ['NONE', 'None'];

        yield 'mixed case sTrIcT' => ['sTrIcT', 'Strict'];

        yield 'empty string' => ['', 'Lax'];
    }
}
