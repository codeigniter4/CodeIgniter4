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

namespace CodeIgniter\Filters;

use CodeIgniter\HTTP\DownloadResponse;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use Config\App;
use Config\Cache;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class PageCacheTest extends CIUnitTestCase
{
    private function createRequest(): IncomingRequest
    {
        $superglobals = service('superglobals');
        $superglobals->setServer('REQUEST_URI', '/');

        $siteUri = new SiteURI(new App());

        return new IncomingRequest(new App(), $siteUri, null, new UserAgent());
    }

    public function testDefaultConfigCachesAllStatusCodes(): void
    {
        $config = new Cache();
        $filter = new PageCache($config);

        $request = $this->createRequest();

        $response200 = new Response(new App());
        $response200->setStatusCode(200);
        $response200->setBody('Success');

        $result = $filter->after($request, $response200);
        $this->assertInstanceOf(Response::class, $result);

        $response404 = new Response(new App());
        $response404->setStatusCode(404);
        $response404->setBody('Not Found');

        $result = $filter->after($request, $response404);
        $this->assertInstanceOf(Response::class, $result);

        $response500 = new Response(new App());
        $response500->setStatusCode(500);
        $response500->setBody('Server Error');

        $result = $filter->after($request, $response500);
        $this->assertInstanceOf(Response::class, $result);
    }

    public function testRestrictedConfigOnlyCaches200Responses(): void
    {
        $config                   = new Cache();
        $config->cacheStatusCodes = [200];
        $filter                   = new PageCache($config);

        $request = $this->createRequest();

        // Test 200 response - should be cached
        $response200 = new Response(new App());
        $response200->setStatusCode(200);
        $response200->setBody('Success');

        $result = $filter->after($request, $response200);
        $this->assertInstanceOf(Response::class, $result);

        // Test 404 response - should NOT be cached
        $response404 = new Response(new App());
        $response404->setStatusCode(404);
        $response404->setBody('Not Found');

        $result = $filter->after($request, $response404);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);

        // Test 500 response - should NOT be cached
        $response500 = new Response(new App());
        $response500->setStatusCode(500);
        $response500->setBody('Server Error');

        $result = $filter->after($request, $response500);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testCustomCacheStatusCodes(): void
    {
        $config                   = new Cache();
        $config->cacheStatusCodes = [200, 404, 410];
        $filter                   = new PageCache($config);

        $request = $this->createRequest();

        $response200 = new Response(new App());
        $response200->setStatusCode(200);
        $response200->setBody('Success');

        $result = $filter->after($request, $response200);
        $this->assertInstanceOf(Response::class, $result);

        $response404 = new Response(new App());
        $response404->setStatusCode(404);
        $response404->setBody('Not Found');

        $result = $filter->after($request, $response404);
        $this->assertInstanceOf(Response::class, $result);

        $response410 = new Response(new App());
        $response410->setStatusCode(410);
        $response410->setBody('Gone');

        $result = $filter->after($request, $response410);
        $this->assertInstanceOf(Response::class, $result);

        // Test 500 response - should NOT be cached (not in whitelist)
        $response500 = new Response(new App());
        $response500->setStatusCode(500);
        $response500->setBody('Server Error');

        $result = $filter->after($request, $response500);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testDownloadResponseNotCached(): void
    {
        $config                   = new Cache();
        $config->cacheStatusCodes = [200];
        $filter                   = new PageCache($config);

        $request = $this->createRequest();

        $response = new DownloadResponse('test.txt', true);

        $result = $filter->after($request, $response);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testRedirectResponseNotCached(): void
    {
        $config                   = new Cache();
        $config->cacheStatusCodes = [200, 301, 302];
        $filter                   = new PageCache($config);

        $request = $this->createRequest();

        $response = new RedirectResponse(new App());
        $response->redirect('/new-url');

        $result = $filter->after($request, $response);
        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }
}
