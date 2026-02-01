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

namespace CodeIgniter\Router\Attributes;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\I18n\Time;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CacheTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear cache before each test
        cache()->clean();

        Time::setTestNow('2026-01-10 12:00:00');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        Time::setTestNow();
    }

    public function testConstructorDefaults(): void
    {
        $cache = new Cache();

        $this->assertSame(3600, $cache->for);
        $this->assertNull($cache->key);
    }

    public function testConstructorCustomValues(): void
    {
        $cache = new Cache(for: 300, key: 'custom_key');

        $this->assertSame(300, $cache->for);
        $this->assertSame('custom_key', $cache->key);
    }

    public function testBeforeReturnsNullForNonGetRequest(): void
    {
        $cache   = new Cache();
        $request = $this->createMockRequest('POST', '/test');

        $result = $cache->before($request);

        $this->assertNull($result);
    }

    public function testBeforeReturnsCachedResponseWhenFound(): void
    {
        $cache   = new Cache();
        $request = $this->createMockRequest('GET', '/test');

        // Manually cache a response
        $cacheKey   = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request);
        $cachedData = [
            'body'      => 'Cached content',
            'status'    => 200,
            'headers'   => ['Content-Type' => 'text/html'],
            'timestamp' => Time::now()->getTimestamp() - 10,
        ];
        cache()->save($cacheKey, $cachedData, 3600);

        $result = $cache->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame('Cached content', $result->getBody());
        $this->assertSame(200, $result->getStatusCode());
        $this->assertSame('text/html', $result->getHeaderLine('Content-Type'));
        $this->assertSame('10', $result->getHeaderLine('Age'));
    }

    public function testBeforeReturnsNullForInvalidCacheData(): void
    {
        $cache   = new Cache();
        $request = $this->createMockRequest('GET', '/test');

        // Cache invalid data
        $cacheKey = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request);
        cache()->save($cacheKey, 'invalid data', 3600);

        $result = $cache->before($request);

        $this->assertNull($result);
    }

    public function testBeforeReturnsNullForIncompleteCacheData(): void
    {
        $cache   = new Cache();
        $request = $this->createMockRequest('GET', '/test');

        // Cache incomplete data (missing 'headers' key)
        $cacheKey = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request);
        cache()->save($cacheKey, ['body' => 'test', 'status' => 200], 3600);

        $result = $cache->before($request);

        $this->assertNull($result);
    }

    public function testBeforeUsesCustomCacheKey(): void
    {
        $cache   = new Cache(key: 'my_custom_key');
        $request = $this->createMockRequest('GET', '/test');

        // Cache with custom key
        $cachedData = [
            'body'      => 'Custom cached content',
            'status'    => 200,
            'headers'   => [],
            'timestamp' => Time::now()->getTimestamp(),
        ];
        cache()->save('my_custom_key', $cachedData, 3600);

        $result = $cache->before($request);

        $this->assertInstanceOf(ResponseInterface::class, $result);
        $this->assertSame('Custom cached content', $result->getBody());
    }

    public function testAfterReturnsNullForNonGetRequest(): void
    {
        $cache    = new Cache();
        $request  = $this->createMockRequest('POST', '/test');
        $response = Services::response();

        $result = $cache->after($request, $response);

        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testAfterCachesGetRequestResponse(): void
    {
        $cache    = new Cache(for: 300);
        $request  = $this->createMockRequest('GET', '/test');
        $response = Services::response();
        $response->setBody('Test content');
        $response->setStatusCode(200);
        $response->setHeader('Content-Type', 'text/plain');

        $result = $cache->after($request, $response);

        $this->assertSame($response, $result);

        // Verify cache was saved
        $cacheKey = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request);
        $cached   = cache($cacheKey);

        $this->assertIsArray($cached);
        $this->assertSame('Test content', $cached['body']);
        $this->assertSame(200, $cached['status']);
        $this->assertArrayHasKey('timestamp', $cached);
    }

    public function testAfterUsesCustomCacheKey(): void
    {
        $cache    = new Cache(key: 'another_custom_key');
        $request  = $this->createMockRequest('GET', '/test');
        $response = Services::response();
        $response->setBody('Custom key content');

        $cache->after($request, $response);

        // Verify cache was saved with custom key
        $cached = cache('another_custom_key');

        $this->assertIsArray($cached);
        $this->assertSame('Custom key content', $cached['body']);
    }

    public function testGenerateCacheKeyIncludesMethodPathAndQuery(): void
    {
        $cache    = new Cache();
        $request1 = $this->createMockRequest('GET', '/test', 'foo=bar');
        $request2 = $this->createMockRequest('GET', '/test', 'foo=baz');

        $key1 = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request1);
        $key2 = $this->getPrivateMethodInvoker($cache, 'generateCacheKey')($request2);

        $this->assertNotSame($key1, $key2);
        $this->assertStringStartsWith('route_cache_', $key1);
    }

    private function createMockRequest(string $method, string $path, string $query = ''): IncomingRequest
    {
        $config    = new MockAppConfig();
        $uri       = new SiteURI($config, 'http://example.com' . $path . ($query !== '' ? '?' . $query : ''));
        $userAgent = new UserAgent();

        $request = $this->getMockBuilder(IncomingRequest::class)
            ->setConstructorArgs([$config, $uri, null, $userAgent])
            ->onlyMethods(['isCLI'])
            ->getMock();
        $request->method('isCLI')->willReturn(false);
        $request->setMethod($method);

        return $request;
    }
}
