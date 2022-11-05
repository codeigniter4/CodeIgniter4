<?php

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
use CodeIgniter\Test\CIUnitTestCase;
use DateTimeImmutable;

/**
 * @internal
 *
 * @group Others
 */
final class CookieStoreTest extends CIUnitTestCase
{
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

    public function testCookieStoreInitialization(): void
    {
        $cookies = [
            new Cookie('dev', 'cookie'),
            new Cookie('prod', 'cookie', ['raw' => true]),
        ];

        $store = new CookieStore($cookies);

        $this->assertCount(2, $store);
        $this->assertTrue($store->has('dev'));
        $this->assertSame($cookies[0], $store->get('dev'));
        $this->assertTrue($store->has('prod'));
        $this->assertTrue($store->has('prod', '', 'cookie'));
        $this->assertSame($cookies[1], $store->get('prod'));
        $this->assertFalse($store->has('test'));
        $this->assertSame($cookies, array_values($store->display()));
        $this->assertSame($cookies, array_values(iterator_to_array($store->getIterator())));

        $this->expectException(CookieException::class);
        $store->get('test');
    }

    public function testCookieStoreInitViaHeaders(): void
    {
        $cookies = [
            'dev=cookie; Max-Age=3600',
            'prod=cookie; Path=/web',
            'test,def=cookie; SameSite=Lax',
        ];

        $store = CookieStore::fromCookieHeaders($cookies, true);
        $this->assertCount(2, $store);
        $this->assertTrue($store->has('dev'));
        $this->assertTrue($store->has('prod'));
        $this->assertFalse($store->has('test,def'));
    }

    public function testInvalidCookieStored(): void
    {
        $this->expectException(CookieException::class);
        new CookieStore([new DateTimeImmutable('now')]);
    }

    public function testPutRemoveCookiesInStore(): void
    {
        $cookies = [
            new Cookie('dev', 'cookie'),
            new Cookie('prod', 'cookie', ['raw' => true]),
        ];

        $store  = new CookieStore($cookies);
        $bottle = $store->put(new Cookie('test', 'cookie'));
        $jar    = $store->remove('dev');

        $this->assertNotSame($store->display(), $bottle->display());
        $this->assertFalse($store->has('test'));
        $this->assertTrue($bottle->has('test'));
        $this->assertTrue($store->has('dev'));
        $this->assertFalse($jar->has('dev'));
    }

    public function testCookiesFunction(): void
    {
        $a = cookies();
        $b = cookies([], false);

        $this->assertInstanceOf(CookieStore::class, $a);
        $this->assertInstanceOf(CookieStore::class, $b);
        $this->assertNotSame($a, $b);
    }
}
