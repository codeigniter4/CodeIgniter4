<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Cache;

use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App as AppConfig;
use Config\Cache as CacheConfig;
use ErrorException;
use Exception;

/**
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class ResponseCacheTest extends CIUnitTestCase
{
    private AppConfig $appConfig;

    protected function setUp(): void
    {
        parent::setUp();

        $this->appConfig = new AppConfig();
    }

    private function createIncomingRequest(
        string $uri = '',
        array $query = [],
        ?AppConfig $appConfig = null
    ): IncomingRequest {
        $_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];

        $_SERVER['REQUEST_URI'] = '/' . $uri . ($query ? '?' . http_build_query($query) : '');
        $_SERVER['SCRIPT_NAME'] = '/index.php';

        $appConfig ??= $this->appConfig;

        $siteUri = new URI($appConfig->baseURL . $uri);
        if ($query !== []) {
            $_GET = $_REQUEST = $query;
            $siteUri->setQueryArray($query);
        }

        return new IncomingRequest(
            $appConfig,
            $siteUri,
            null,
            new UserAgent()
        );
    }

    /**
     * @phpstan-param list<string> $params
     */
    private function createCLIRequest(array $params = [], ?AppConfig $appConfig = null): CLIRequest
    {
        $_POST = $_GET = $_SERVER = $_REQUEST = $_ENV = $_COOKIE = $_SESSION = [];

        $_SERVER['argv']        = ['public/index.php', ...$params];
        $_SERVER['SCRIPT_NAME'] = 'public/index.php';

        $appConfig ??= $this->appConfig;

        return new CLIRequest($appConfig);
    }

    private function createResponseCache(?CacheConfig $cacheConfig = null): ResponseCache
    {
        $cache = mock(CacheFactory::class);

        $cacheConfig ??= new CacheConfig();

        return (new ResponseCache($cacheConfig, $cache))->setTtl(300);
    }

    public function testCachePageIncomingRequest()
    {
        $pageCache = $this->createResponseCache();

        $request = $this->createIncomingRequest('foo/bar');

        $response = new Response($this->appConfig);
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $return = $pageCache->make($request, $response);

        $this->assertTrue($return);

        // Check cache with a request with the same URI path.
        $request        = $this->createIncomingRequest('foo/bar');
        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with a request with the same URI path and different query string.
        $request        = $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']);
        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with another request with the different URI path.
        $request = $this->createIncomingRequest('another');

        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertNull($cachedResponse);
    }

    public function testCachePageIncomingRequestWithCacheQueryString()
    {
        $cacheConfig                   = new CacheConfig();
        $cacheConfig->cacheQueryString = true;
        $pageCache                     = $this->createResponseCache($cacheConfig);

        $request = $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']);

        $response = new Response($this->appConfig);
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $return = $pageCache->make($request, $response);

        $this->assertTrue($return);

        // Check cache with a request with the same URI path and same query string.
        $this->createIncomingRequest('foo/bar', ['foo' => 'bar', 'bar' => 'baz']);
        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());
        $this->assertSame('abcd1234', $cachedResponse->getHeaderLine('ETag'));

        // Check cache with a request with the same URI path and different query string.
        $request        = $this->createIncomingRequest('foo/bar', ['xfoo' => 'bar', 'bar' => 'baz']);
        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertNull($cachedResponse);

        // Check cache with another request with the different URI path.
        $request = $this->createIncomingRequest('another');

        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertNull($cachedResponse);
    }

    public function testCachePageCLIRequest()
    {
        $pageCache = $this->createResponseCache();

        $request = $this->createCLIRequest(['foo', 'bar']);

        $response = new Response($this->appConfig);
        $response->setBody('The response body.');

        $return = $pageCache->make($request, $response);

        $this->assertTrue($return);

        // Check cache with a request with the same params.
        $request        = $this->createCLIRequest(['foo', 'bar']);
        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertInstanceOf(ResponseInterface::class, $cachedResponse);
        $this->assertSame('The response body.', $cachedResponse->getBody());

        // Check cache with another request with the different params.
        $request = $this->createCLIRequest(['baz']);

        $cachedResponse = $pageCache->get($request, new Response($this->appConfig));

        $this->assertNull($cachedResponse);
    }

    public function testUnserializeError()
    {
        $this->expectException(ErrorException::class);
        $this->expectExceptionMessage('unserialize(): Error at offset 0 of 12 bytes');

        $cache       = mock(CacheFactory::class);
        $cacheConfig = new CacheConfig();
        $pageCache   = new ResponseCache($cacheConfig, $cache);

        $request = $this->createIncomingRequest('foo/bar');

        $response = new Response($this->appConfig);
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $pageCache->make($request, $response);

        $cacheKey = $pageCache->generateCacheKey($request);

        // Save invalid data.
        $cache->save($cacheKey, 'Invalid data');

        // Check cache with a request with the same URI path.
        $pageCache->get($request, new Response($this->appConfig));
    }

    public function testInvalidCacheError()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Error unserializing page cache');

        $cache       = mock(CacheFactory::class);
        $cacheConfig = new CacheConfig();
        $pageCache   = new ResponseCache($cacheConfig, $cache);

        $request = $this->createIncomingRequest('foo/bar');

        $response = new Response($this->appConfig);
        $response->setHeader('ETag', 'abcd1234');
        $response->setBody('The response body.');

        $pageCache->make($request, $response);

        $cacheKey = $pageCache->generateCacheKey($request);

        // Save invalid data.
        $cache->save($cacheKey, serialize(['a' => '1']));

        // Check cache with a request with the same URI path.
        $pageCache->get($request, new Response($this->appConfig));
    }
}
