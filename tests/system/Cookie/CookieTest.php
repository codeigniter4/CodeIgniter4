<?php

namespace CodeIgniter\Cookie;

use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cookie as CookieConfig;
use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use LogicException;

/**
 * @internal
 */
final class CookieTest extends CIUnitTestCase
{
    /**
     * @var array
     */
    private $defaults;

    protected function setUp(): void
    {
        parent::setUp();
        $this->defaults = Cookie::setDefaults();
    }

    protected function tearDown(): void
    {
        Cookie::setDefaults($this->defaults);
    }

    public function testCookieInitializationWithDefaults(): void
    {
        $cookie  = new Cookie('test', 'value');
        $options = Cookie::setDefaults();

        $this->assertSame($options['prefix'] . 'test', $cookie->getPrefixedName());
        $this->assertSame('test', $cookie->getName());
        $this->assertSame('value', $cookie->getValue());
        $this->assertSame($options['prefix'], $cookie->getPrefix());
        $this->assertSame($options['expires'], $cookie->getExpiresTimestamp());
        $this->assertSame($options['path'], $cookie->getPath());
        $this->assertSame($options['domain'], $cookie->getDomain());
        $this->assertSame($options['secure'], $cookie->isSecure());
        $this->assertSame($options['httponly'], $cookie->isHTTPOnly());
        $this->assertSame($options['samesite'], $cookie->getSameSite());
        $this->assertSame($options['raw'], $cookie->isRaw());
    }

    public function testConfigInjectionForDefaults(): void
    {
        /**
         * @var CookieConfig $config
         */
        $config = new CookieConfig();

        $old = Cookie::setDefaults($config);

        $cookie = new Cookie('test', 'value');
        $this->assertSame($config->prefix . 'test', $cookie->getPrefixedName());
        $this->assertSame('test', $cookie->getName());
        $this->assertSame('value', $cookie->getValue());
        $this->assertSame($config->prefix, $cookie->getPrefix());
        $this->assertSame($config->expires, $cookie->getExpiresTimestamp());
        $this->assertSame($config->path, $cookie->getPath());
        $this->assertSame($config->domain, $cookie->getDomain());
        $this->assertSame($config->secure, $cookie->isSecure());
        $this->assertSame($config->httponly, $cookie->isHTTPOnly());
        $this->assertSame($config->samesite, $cookie->getSameSite());
        $this->assertSame($config->raw, $cookie->isRaw());

        Cookie::setDefaults($old);
    }

    public function testValidationOfRawCookieName(): void
    {
        $this->expectException(CookieException::class);
        new Cookie("test;\n", '', ['raw' => true]);
    }

    public function testValidationOfEmptyCookieName(): void
    {
        $this->expectException(CookieException::class);
        new Cookie('', 'value');
    }

    public function testValidationOfSecurePrefix(): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', 'value', ['prefix' => '__Secure-', 'secure' => false]);
    }

    public function testValidationOfHostPrefix(): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', 'value', ['prefix' => '__Host-', 'domain' => 'localhost']);
    }

    public function testValidationOfSameSite(): void
    {
        Cookie::setDefaults(['samesite' => '']);
        $this->assertInstanceOf(Cookie::class, new Cookie('test'));

        $this->expectException(CookieException::class);
        new Cookie('test', '', ['samesite' => 'Yes']);
    }

    public function testValidationOfSameSiteNone(): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', '', ['samesite' => Cookie::SAMESITE_NONE, 'secure' => false]);
    }

    public function testExpirationTime(): void
    {
        // expires => 0
        $cookie = new Cookie('test', 'value');
        $this->assertSame(0, $cookie->getExpiresTimestamp());
        $this->assertSame('Thu, 01-Jan-1970 00:00:00 GMT', $cookie->getExpiresString());
        $this->assertTrue($cookie->isExpired());
        $this->assertSame(0, $cookie->getMaxAge());

        $date   = new DateTimeImmutable('2021-01-10 00:00:00 GMT', new DateTimeZone('UTC'));
        $cookie = new Cookie('test', 'value', ['expires' => $date]);
        $this->assertSame((int) $date->format('U'), $cookie->getExpiresTimestamp());
        $this->assertSame('Sun, 10-Jan-2021 00:00:00 GMT', $cookie->getExpiresString());
    }

    /**
     * @dataProvider invalidExpiresProvider
     *
     * @param mixed $expires
     *
     * @return void
     */
    public function testInvalidExpires($expires): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', 'value', ['expires' => $expires]);
    }

    public static function invalidExpiresProvider(): iterable
    {
        $cases = [
            'non-numeric-string' => ['yes'],
            'boolean'            => [true],
            'float'              => [10.0],
        ];

        foreach ($cases as $type => $case) {
            yield $type => $case;
        }
    }

    /**
     * @dataProvider setCookieHeaderProvider
     *
     * @param string $header
     * @param array  $changed
     *
     * @return void
     */
    public function testSetCookieHeaderCreation(string $header, array $changed): void
    {
        $cookie = Cookie::fromHeaderString($header);
        $cookie = $cookie->toArray();
        $this->assertSame(array_merge($cookie, $changed), $cookie);
    }

    public static function setCookieHeaderProvider(): iterable
    {
        yield 'basic' => [
            'test=value',
            ['name' => 'test', 'value' => 'value'],
        ];

        yield 'empty-value' => [
            'test',
            ['name' => 'test', 'value' => ''],
        ];

        yield 'with-other-attrs' => [
            'test=value; Max-Age=3600; Path=/web',
            ['name' => 'test', 'value' => 'value', 'path' => '/web'],
        ];

        yield 'with-flags' => [
            'test=value; Secure; HttpOnly; SameSite=Lax',
            ['name' => 'test', 'value' => 'value', 'secure' => true, 'httponly' => true, 'samesite' => 'Lax'],
        ];
    }

    public function testValidNamePerRfcYieldsSameNameRegardlessOfRawParam(): void
    {
        $cookie1 = new Cookie('testing', '', ['raw' => false]);
        $cookie2 = new Cookie('testing', '', ['raw' => true]);
        $this->assertSame($cookie1->getPrefixedName(), $cookie2->getPrefixedName());
    }

    public function testCloningCookies(): void
    {
        $a = new Cookie('dev', 'cookie');
        $b = $a->withRaw();
        $c = $a->withPrefix('my_');
        $d = $a->withName('prod');
        $e = $a->withValue('muffin');
        $f = $a->withExpires('+30 days');
        $g = $a->withExpired();
        $h = $a->withNeverExpiring();
        $i = $a->withDomain('localhost');
        $j = $a->withPath('/web');
        $k = $a->withSecure();
        $l = $a->withHTTPOnly();
        $m = $a->withSameSite(Cookie::SAMESITE_STRICT);

        $this->assertNotSame($a, $b);
        $this->assertNotSame($a, $c);
        $this->assertNotSame($a, $d);
        $this->assertNotSame($a, $e);
        $this->assertNotSame($a, $f);
        $this->assertNotSame($a, $g);
        $this->assertNotSame($a, $h);
        $this->assertNotSame($a, $i);
        $this->assertNotSame($a, $j);
        $this->assertNotSame($a, $k);
        $this->assertNotSame($a, $l);
        $this->assertNotSame($a, $m);
    }

    public function testStringCastingOfCookies(): void
    {
        $date = new DateTimeImmutable('2021-02-14 00:00:00 GMT', new DateTimeZone('UTC'));

        $a = new Cookie('cookie', 'lover');
        $b = $a->withValue('monster')->withPath('/web')->withDomain('localhost')->withExpires($date);
        $c = $a->withSecure()->withHTTPOnly(false)->withSameSite(Cookie::SAMESITE_STRICT);

        $max = (string) $b->getMaxAge();
        $old = Cookie::setDefaults(['samesite' => '']);

        $d = $a->withValue('')->withSameSite('');

        $this->assertSame(
            'cookie=lover; Path=/; HttpOnly; SameSite=Lax',
            $a->toHeaderString()
        );
        $this->assertSame(
            "cookie=monster; Expires=Sun, 14-Feb-2021 00:00:00 GMT; Max-Age={$max}; Path=/web; Domain=localhost; HttpOnly; SameSite=Lax",
            (string) $b
        );
        $this->assertSame(
            'cookie=lover; Path=/; Secure; SameSite=Strict',
            (string) $c
        );
        $this->assertSame(
            'cookie=deleted; Expires=Thu, 01-Jan-1970 00:00:00 GMT; Max-Age=0; Path=/; HttpOnly; SameSite=Lax',
            (string) $d
        );

        Cookie::setDefaults($old);
    }

    public function testArrayAccessOfCookie(): void
    {
        $cookie = new Cookie('cookie', 'monster');

        $this->assertTrue(isset($cookie['expire']));
        $this->assertSame($cookie['expire'], $cookie->getExpiresTimestamp());
        $this->assertTrue(isset($cookie['httponly']));
        $this->assertSame($cookie['httponly'], $cookie->isHTTPOnly());
        $this->assertTrue(isset($cookie['samesite']));
        $this->assertSame($cookie['samesite'], $cookie->getSameSite());
        $this->assertTrue(isset($cookie['path']));
        $this->assertSame($cookie['path'], $cookie->getPath());

        $this->expectException(InvalidArgumentException::class);
        $cookie['expiry'];
    }

    public function testCannotSetPropertyViaArrayAccess(): void
    {
        $this->expectException(LogicException::class);
        $cookie            = new Cookie('cookie', 'monster');
        $cookie['expires'] = 7200;
    }

    public function testCannotUnsetPropertyViaArrayAccess(): void
    {
        $this->expectException(LogicException::class);
        $cookie = new Cookie('cookie', 'monster');
        unset($cookie['path']);
    }
}
