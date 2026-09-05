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

namespace CodeIgniter\Cookie;

use CodeIgniter\Cookie\Exceptions\CookieException;
use CodeIgniter\Exceptions\LogicException;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Cookie as CookieConfig;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CookieTest extends CIUnitTestCase
{
    /**
     * @var array{
     *   prefix: string,
     *   expires: int,
     *   path: string,
     *   domain: string,
     *   secure: bool,
     *   httponly: bool,
     *   samesite: string,
     *   raw: bool
     * }
     */
    private array $defaults;

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

    #[DataProvider('provideConfigPrefix')]
    public function testConfigPrefix(string $configPrefix, string $optionPrefix, string $expected): void
    {
        $config         = new CookieConfig();
        $config->prefix = $configPrefix;
        Cookie::setDefaults($config);

        $cookie = new Cookie(
            'test',
            'value',
            [
                'prefix' => $optionPrefix,
            ],
        );

        $this->assertSame($expected, $cookie->getPrefixedName());
    }

    /**
     * @return iterable<int, array{string, string, string}>
     */
    public static function provideConfigPrefix(): iterable
    {
        yield from [
            ['prefix_', '', 'prefix_test'],
            ['prefix_', '0', '0test'],
            ['prefix_', 'new_', 'new_test'],
            ['', '', 'test'],
            ['', '0', '0test'],
            ['', 'new_', 'new_test'],
        ];
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
        $this->assertSame('Thu, 01 Jan 1970 00:00:00 GMT', $cookie->getExpiresString());
        $this->assertTrue($cookie->isExpired());
        $this->assertSame(0, $cookie->getMaxAge());

        $date   = new DateTimeImmutable('2021-01-10 00:00:00 GMT', new DateTimeZone('UTC'));
        $cookie = new Cookie('test', 'value', ['expires' => $date]);
        $this->assertSame((int) $date->format('U'), $cookie->getExpiresTimestamp());
        $this->assertSame('Sun, 10 Jan 2021 00:00:00 GMT', $cookie->getExpiresString());
    }

    /**
     * @param bool|float|string $expires
     */
    #[DataProvider('provideInvalidExpires')]
    public function testInvalidExpires($expires): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', 'value', ['expires' => $expires]);
    }

    /**
     * @return iterable<string, array{bool|float|string}>
     */
    public static function provideInvalidExpires(): iterable
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
     * @param array<string, bool|string> $changed
     */
    #[DataProvider('provideSetCookieHeaderCreation')]
    public function testSetCookieHeaderCreation(string $header, array $changed): void
    {
        $cookie = Cookie::fromHeaderString($header);
        $cookie = $cookie->toArray();
        $this->assertSame(array_merge($cookie, $changed), $cookie);
    }

    /**
     * @return iterable<string, array{string, array<string, bool|string>}>
     */
    public static function provideSetCookieHeaderCreation(): iterable
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
            $a->toHeaderString(),
        );
        $this->assertSame(
            "cookie=monster; Expires=Sun, 14 Feb 2021 00:00:00 GMT; Max-Age={$max}; Path=/web; Domain=localhost; HttpOnly; SameSite=Lax",
            (string) $b,
        );
        $this->assertSame(
            'cookie=lover; Path=/; Secure; SameSite=Strict',
            (string) $c,
        );
        $this->assertSame(
            'cookie=deleted; Expires=Thu, 01 Jan 1970 00:00:00 GMT; Max-Age=0; Path=/; HttpOnly; SameSite=Lax',
            (string) $d,
        );

        Cookie::setDefaults($old);
    }

    public function testArrayAccessOfCookie(): void
    {
        $cookie = new Cookie('cookie', 'monster');

        $this->assertArrayHasKey('expire', $cookie);
        $this->assertSame($cookie['expire'], $cookie->getExpiresTimestamp());
        $this->assertArrayHasKey('httponly', $cookie);
        $this->assertSame($cookie['httponly'], $cookie->isHTTPOnly());
        $this->assertArrayHasKey('samesite', $cookie);
        $this->assertSame($cookie['samesite'], $cookie->getSameSite());
        $this->assertArrayHasKey('path', $cookie);
        $this->assertSame($cookie['path'], $cookie->getPath());

        $this->expectException('InvalidArgumentException');
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

    #[DataProvider('provideValidationOfRawCookieValue')]
    public function testValidationOfRawCookieValue(string $value): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', $value, ['raw' => true]);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideValidationOfRawCookieValue(): iterable
    {
        yield 'comma' => ['value,comma'];

        yield 'semicolon' => ['value;semicolon'];

        yield 'space' => ['value with space'];

        yield 'tab' => ["value\twith_tab"];

        yield 'carriage return' => ["value\rcarriage"];

        yield 'newline' => ["value\nnewline"];

        yield 'vertical tab' => ["value\vvertical_tab"];

        yield 'form feed' => ["value\fform_feed"];

        yield 'null byte' => ["value\0null_byte"];

        yield 'CRLF' => ["value\r\nwith_crlf"];
    }

    #[DataProvider('provideFromHeaderStringValidationOfRawCookieValue')]
    public function testFromHeaderStringValidationOfRawCookieValue(string $value): void
    {
        $this->expectException(CookieException::class);
        Cookie::fromHeaderString("test={$value}; Path=/", true);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFromHeaderStringValidationOfRawCookieValue(): iterable
    {
        foreach (self::provideValidationOfRawCookieValue() as $name => $case) {
            if ($name === 'semicolon') {
                continue;
            }

            yield $name => $case;
        }
    }

    public function testFromHeaderStringWithRawTrue(): void
    {
        $cookie = Cookie::fromHeaderString('test=valid_raw_value=123; Path=/', true);

        $this->assertTrue($cookie->isRaw());
        $this->assertSame('valid_raw_value=123', $cookie->getValue());
    }

    public function testFromHeaderStringWithRawFalseDecodesValue(): void
    {
        $cookie = Cookie::fromHeaderString('test=value%20with%20space; Path=/', false);

        $this->assertFalse($cookie->isRaw());
        $this->assertSame('value with space', $cookie->getValue());
    }

    public function testValidationOfRawCookieValueInWithValue(): void
    {
        $this->expectException(CookieException::class);
        $cookie = new Cookie('test', 'valid_value', ['raw' => true]);
        $cookie->withValue("injected\r\nvalue");
    }

    public function testValidationOfRawCookieValueInWithRaw(): void
    {
        $this->expectException(CookieException::class);
        $cookie = new Cookie('test', "injected\r\nvalue", ['raw' => false]);
        $cookie->withRaw(true);
    }

    public function testValidRawCookieRetainsValueWithoutEncoding(): void
    {
        $cookie = new Cookie('test', 'valid_raw_value=123', ['raw' => true]);

        $this->assertSame('valid_raw_value=123', $cookie->getValue());
        $this->assertStringContainsString('test=valid_raw_value=123', (string) $cookie);
    }

    public function testNonRawCookieSafelyEncodesCRLF(): void
    {
        $cookie = new Cookie('test', "value\r\nwith_crlf", ['raw' => false]);
        $result = (string) $cookie;

        $this->assertStringContainsString('%0D%0A', $result);
        $this->assertStringNotContainsString("\r", $result);
        $this->assertStringNotContainsString("\n", $result);
    }

    #[DataProvider('provideValidationOfCookiePath')]
    public function testValidationOfCookiePath(string $path): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookiePath'));
        new Cookie('test', 'value', ['path' => $path]);
    }

    #[DataProvider('provideValidationOfCookiePath')]
    public function testValidationOfCookiePathInWithPath(string $path): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookiePath'));
        $cookie = new Cookie('test', 'value');
        $cookie->withPath($path);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideValidationOfCookiePath(): iterable
    {
        yield 'comma' => ['/path,comma'];

        yield 'semicolon' => ['/path;semicolon'];

        yield 'space' => ['/path with space'];

        yield 'tab' => ["/path\twith_tab"];

        yield 'carriage return' => ["/path\rcarriage"];

        yield 'newline' => ["/path\nnewline"];

        yield 'vertical tab' => ["/path\vvertical_tab"];

        yield 'form feed' => ["/path\fform_feed"];

        yield 'null byte' => ["/path\0null_byte"];

        yield 'CRLF' => ["/path\r\nwith_crlf"];
    }

    #[DataProvider('provideFromHeaderStringValidationOfCookiePath')]
    public function testFromHeaderStringValidationOfCookiePath(string $path): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookiePath'));
        Cookie::fromHeaderString("test=value; Path={$path}");
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFromHeaderStringValidationOfCookiePath(): iterable
    {
        foreach (self::provideValidationOfCookiePath() as $name => $case) {
            if ($name === 'semicolon') {
                continue;
            }

            yield $name => $case;
        }
    }

    #[DataProvider('provideValidationOfCookieDomain')]
    public function testValidationOfCookieDomain(string $domain): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookieDomain'));
        new Cookie('test', 'value', ['domain' => $domain]);
    }

    #[DataProvider('provideValidationOfCookieDomain')]
    public function testValidationOfCookieDomainInWithDomain(string $domain): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookieDomain'));
        $cookie = new Cookie('test', 'value');
        $cookie->withDomain($domain);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideValidationOfCookieDomain(): iterable
    {
        yield 'comma' => ['domain,comma.com'];

        yield 'semicolon' => ['domain;semicolon.com'];

        yield 'space' => ['domain with space.com'];

        yield 'tab' => ["domain\twith_tab.com"];

        yield 'carriage return' => ["domain\rcarriage.com"];

        yield 'newline' => ["domain\nnewline.com"];

        yield 'vertical tab' => ["domain\vvertical_tab.com"];

        yield 'form feed' => ["domain\fform_feed.com"];

        yield 'null byte' => ["domain\0null_byte.com"];

        yield 'CRLF' => ["domain\r\nwith_crlf.com"];
    }

    #[DataProvider('provideFromHeaderStringValidationOfCookieDomain')]
    public function testFromHeaderStringValidationOfCookieDomain(string $domain): void
    {
        $this->expectException(CookieException::class);
        $this->expectExceptionMessage(lang('Cookie.invalidCookieDomain'));
        Cookie::fromHeaderString("test=value; Domain={$domain}");
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function provideFromHeaderStringValidationOfCookieDomain(): iterable
    {
        foreach (self::provideValidationOfCookieDomain() as $name => $case) {
            if ($name === 'semicolon') {
                continue;
            }

            yield $name => $case;
        }
    }

    public function testNullPathAndDomainDefaultProperly(): void
    {
        $cookie = new Cookie('test', 'val', ['path' => null, 'domain' => null, 'prefix' => null]);

        $this->assertSame('/', $cookie->getPath());
        $this->assertSame('', $cookie->getDomain());
        $this->assertSame('', $cookie->getPrefix());

        $cookie2 = $cookie->withPath(null)->withDomain(null)->withPrefix('');
        $this->assertSame('/', $cookie2->getPath());
        $this->assertSame('', $cookie2->getDomain());
        $this->assertSame('', $cookie2->getPrefix());
    }

    public function testValidCookiePathAndDomain(): void
    {
        $cookie = new Cookie('test', 'val', ['path' => '/sub/dir/', 'domain' => 'example.com']);
        $this->assertSame('/sub/dir/', $cookie->getPath());
        $this->assertSame('example.com', $cookie->getDomain());

        $cookie2 = $cookie->withPath('/another/path')->withDomain('.example.com');
        $this->assertSame('/another/path', $cookie2->getPath());
        $this->assertSame('.example.com', $cookie2->getDomain());

        $cookie3 = new Cookie('test', 'val', ['path' => '/', 'domain' => '']);
        $this->assertSame('/', $cookie3->getPath());
        $this->assertSame('', $cookie3->getDomain());
    }

    public function testValidationOfRawCookiePrefix(): void
    {
        $this->expectException(CookieException::class);
        new Cookie('test', 'val', ['prefix' => "bad\r\n", 'raw' => true]);
    }

    public function testValidationOfRawCookiePrefixInWithPrefix(): void
    {
        $this->expectException(CookieException::class);
        $cookie = new Cookie('test', 'val', ['raw' => true]);
        $cookie->withPrefix("bad\r\n");
    }

    public function testValidationOfRawCookiePrefixInWithRaw(): void
    {
        $this->expectException(CookieException::class);
        $cookie = new Cookie('test', 'val', ['prefix' => "bad\r\n", 'raw' => false]);
        $cookie->withRaw(true);
    }
}
