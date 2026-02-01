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

namespace CodeIgniter\Cache;

use CodeIgniter\Config\Services;
use CodeIgniter\Exceptions\RuntimeException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCache;
use Config\App;
use Config\Cache;
use ErrorException;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class ResponseCacheTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Services::injectMock('superglobals', new Superglobals());
    }

    /**
     * @param array<string, string> $query
     */
    private function createIncomingRequest(string $uri = '', array $query = [], App $app = new App()): IncomingRequest
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_URI', sprintf('/%s%s', $uri, $query !== [] ? '?' . http_build_query($query) : ''));
        $superglobals->setServer('SCRIPT_NAME', '/index.php');

        $siteUri = new SiteURI($app, $uri);

        if ($query !== []) {
            $siteUri->setQueryArray($query);
        }

        return new IncomingRequest($app, $siteUri, null, new UserAgent());
    }

    /**
     * @param list<string> $params
     */
    private function createCLIRequest(array $params = [], App $app = new App()): CLIRequest
    {
        service('superglobals')->setServer('argv', ['public/index.php', ...$params]);

        $superglobals = service('superglobals');
        $superglobals->setServer('SCRIPT_NAME', 'public/index.php');

        return new CLIRequest($app);
    }

    private function createResponseCache(Cache $cache = new Cache()): ResponseCache
    {
        /** @var MockCache $mockCache */
        $mockCache = mock(CacheFactory::class);

        return (new ResponseCache($cache, $mockCache))->setTtl(300);
    }

    public function testCachePageIncomingRequest(): void
    {
        $pageCache = $this->createResponseCache();

        $response = new Response(new App());
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $this->assertTrue($pageCache->make(
            $this->createIncomingRequest('foo/bar'),
            $response,
        ));

        // Check cache with a request with the same URI path.
        $cachedResponse = $pageCache->get($this->createIncomingRequest('foo/bar'), new Response(new App()));
        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with a request with the same URI path and different query string.
        $cachedResponse = $pageCache->get(
            $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']),
            new Response(new App()),
        );
        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with another request with the different URI path.
        $cachedResponse = $pageCache->get($this->createIncomingRequest('another'), new Response(new App()));
        $this->assertNotInstanceOf(ResponseInterface::class, $cachedResponse);
    }

    public function testCachePageIncomingRequestWithStatus(): void
    {
        $pageCache = $this->createResponseCache();

        $response = new Response(new App());
        $response->setStatusCode(432, 'Foo Bar');
        $response->setBody('The response body.');

        $this->assertTrue($pageCache->make($this->createIncomingRequest('foo/bar'), $response));

        // Check cached response status
        $cachedResponse = $pageCache->get($this->createIncomingRequest('foo/bar'), new Response(new App()));
        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame(432, $cachedResponse->getStatusCode());
        $this->assertSame('Foo Bar', $cachedResponse->getReasonPhrase());
    }

    public function testCachePageIncomingRequestWithCacheQueryString(): void
    {
        $cache = new Cache();

        $cache->cacheQueryString = true;

        $pageCache = $this->createResponseCache($cache);

        $request = $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']);

        $response = new Response(new App());
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $this->assertTrue($pageCache->make($request, $response));

        // Check cache with a request with the same URI path and same query string.
        $cachedResponse = $pageCache->get(
            $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']),
            new Response(new App()),
        );
        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with a request with the same URI path and different query string.
        $cachedResponse = $pageCache->get(
            $this->createIncomingRequest('foo/bar', ['xfoo' => 'bar', 'bar' => 'baz']),
            new Response(new App()),
        );
        $this->assertNotInstanceOf(ResponseInterface::class, $cachedResponse);

        // Check cache with another request with the different URI path.
        $cachedResponse = $pageCache->get($this->createIncomingRequest('another'), new Response(new App()));
        $this->assertNotInstanceOf(ResponseInterface::class, $cachedResponse);
    }

    public function testCachePageIncomingRequestWithHttpMethods(): void
    {
        $pageCache = $this->createResponseCache();

        $response = new Response(new App());
        $response->setBody('The response body.');

        $this->assertTrue($pageCache->make($this->createIncomingRequest('foo/bar'), $response));

        // Check cache with a request with the same URI path and different HTTP method
        $cachedResponse = $pageCache->get(
            $this->createIncomingRequest('foo/bar')->withMethod('POST'),
            new Response(new App()),
        );
        $this->assertNotInstanceOf(ResponseInterface::class, $cachedResponse);
    }

    public function testCachePageCLIRequest(): void
    {
        $pageCache = $this->createResponseCache();

        $response = new Response(new App());
        $response->setBody('The response body.');

        $this->assertTrue($pageCache->make($this->createCLIRequest(['foo', 'bar']), $response));

        // Check cache with a request with the same params.
        $cachedResponse = $pageCache->get($this->createCLIRequest(['foo', 'bar']), new Response(new App()));
        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());

        // Check cache with another request with the different params.
        $cachedResponse = $pageCache->get($this->createCLIRequest(['baz']), new Response(new App()));
        $this->assertNotInstanceOf(ResponseInterface::class, $cachedResponse);
    }

    public function testUnserializeError(): void
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('unserialize(): Error at offset 0 of 12 bytes');

        /** @var MockCache $mockCache */
        $mockCache = mock(CacheFactory::class);
        $pageCache = new ResponseCache(new Cache(), $mockCache);

        $request = $this->createIncomingRequest('foo/bar');

        $response = new Response(new App());
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $pageCache->make($request, $response);

        $cacheKey = $pageCache->generateCacheKey($request);

        // Save invalid data.
        $mockCache->save($cacheKey, 'Invalid data');

        // Check cache with a request with the same URI path.
        $pageCache->get($request, new Response(new App()));
    }

    public function testInvalidCacheError(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Error unserializing page cache');

        /** @var MockCache $mockCache */
        $mockCache = mock(CacheFactory::class);
        $pageCache = new ResponseCache(new Cache(), $mockCache);

        $request = $this->createIncomingRequest('foo/bar');

        $response = new Response(new App());
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $pageCache->make($request, $response);

        $cacheKey = $pageCache->generateCacheKey($request);

        // Save invalid data.
        $mockCache->save($cacheKey, serialize(['a' => '1']));

        // Check cache with a request with the same URI path.
        $pageCache->get($request, new Response(new App()));
    }
}
