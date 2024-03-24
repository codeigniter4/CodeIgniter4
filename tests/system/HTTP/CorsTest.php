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

namespace CodeIgniter\HTTP;

use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class CorsTest extends CIUnitTestCase
{
    private function createCors(array $config = []): Cors
    {
        return new Cors($config);
    }

    private function createRequest(): IncomingRequest
    {
        return Services::incomingrequest(null, false);
    }

    private function getDefaultConfig(): array
    {
        return [
            'allowedOrigins'         => [],
            'allowedOriginsPatterns' => [],
            'supportsCredentials'    => false,
            'allowedHeaders'         => ['X-API-KEY', 'X-Requested-With', 'Content-Type', 'Accept'],
            'allowedMethods'         => ['PUT'],
            'maxAge'                 => 3600,
        ];
    }

    private function assertHeader(ResponseInterface $response, string $name, string $value): void
    {
        $this->assertSame($value, $response->getHeaderLine($name));
    }

    public function testInstantiate()
    {
        $cors = $this->createCors();

        $this->assertInstanceOf(Cors::class, $cors);
    }

    public function testIsPreflightRequestTrue()
    {
        $cors = $this->createCors();

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT');

        $this->assertTrue($cors->isPreflightRequest($request));
    }

    public function testIsPreflightRequestFalse()
    {
        $cors = $this->createCors();

        $request = $this->createRequest()
            ->withMethod('OPTIONS');

        $this->assertFalse($cors->isPreflightRequest($request));
    }

    public function testHandlePreflightRequestSingleAllowedOrigin()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['http://localhost:8080'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Headers',
            'X-API-KEY, X-Requested-With, Content-Type, Accept'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Methods',
            'PUT'
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Credentials')
        );
    }

    public function testHandlePreflightRequestMultipleAllowedOriginsAllowed()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'https://api.example.com');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'https://api.example.com'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Headers',
            'X-API-KEY, X-Requested-With, Content-Type, Accept'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Methods',
            'PUT'
        );
        $this->assertHeader(
            $response,
            'Vary',
            'Origin'
        );
    }

    public function testHandlePreflightRequestMultipleAllowedOriginsAllowedAlreadyVary()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'https://api.example.com');

        $response = Services::response(null, false)
            ->setHeader('Vary', 'Accept-Language');

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'https://api.example.com'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Headers',
            'X-API-KEY, X-Requested-With, Content-Type, Accept'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Methods',
            'PUT'
        );
        $this->assertHeader(
            $response,
            'Vary',
            'Accept-Language, Origin'
        );
    }

    public function testHandlePreflightRequestMultipleAllowedOriginsNotAllowed()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'https://bad.site.com');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Origin')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Headers')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Methods')
        );
    }

    public function testHandlePreflightRequestAllowedOriginsPatternsAllowed()
    {
        $config                           = $this->getDefaultConfig();
        $config['allowedOriginsPatterns'] = ['https://\w+\.example\.com'];
        $cors                             = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'https://api.example.com');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'https://api.example.com'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Headers',
            'X-API-KEY, X-Requested-With, Content-Type, Accept'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Methods',
            'PUT'
        );
        $this->assertHeader(
            $response,
            'Vary',
            'Origin'
        );
    }

    public function testHandlePreflightRequestAllowedOriginsPatternsNotAllowed()
    {
        $config                           = $this->getDefaultConfig();
        $config['allowedOriginsPatterns'] = ['https://\w+\.example\.com'];
        $cors                             = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'https://bad.site.com');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Origin')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Headers')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Methods')
        );
    }

    public function testHandlePreflightRequestSingleAllowedOriginWithCredentials()
    {
        $config                        = $this->getDefaultConfig();
        $config['allowedOrigins']      = ['http://localhost:8080'];
        $config['supportsCredentials'] = true;
        $cors                          = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('OPTIONS')
            ->setHeader('Access-Control-Request-Method', 'PUT')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->handlePreflightRequest($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Headers',
            'X-API-KEY, X-Requested-With, Content-Type, Accept'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Methods',
            'PUT'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Credentials',
            'true'
        );
    }

    public function testAddResponseHeadersSingleAllowedOriginSimpleRequest()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['http://localhost:8080'];
        $config['allowedMethods'] = ['GET', 'POST', 'PUT'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('GET')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Headers')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Methods')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Credentials')
        );
    }

    public function testAddResponseHeadersSingleAllowedOriginRealRequest()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['http://localhost:8080'];
        $config['allowedMethods'] = ['GET', 'POST', 'PUT'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('POST')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
    }

    public function testAddResponseHeadersSingleAllowedOriginWithCredentials()
    {
        $config                        = $this->getDefaultConfig();
        $config['allowedOrigins']      = ['http://localhost:8080'];
        $config['supportsCredentials'] = true;
        $config['allowedMethods']      = ['GET'];
        $cors                          = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('GET')
            ->setHeader('Cookie', 'pageAccess=2')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Allow-Credentials',
            'true'
        );
    }

    public function testAddResponseHeadersSingleAllowedOriginWithExposeHeaders()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['http://localhost:8080'];
        $config['allowedMethods'] = ['GET'];
        $config['exposedHeaders'] = ['Content-Length', 'X-Kuma-Revision'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('GET')
            ->setHeader('Origin', 'http://localhost:8080');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'http://localhost:8080'
        );
        $this->assertHeader(
            $response,
            'Access-Control-Expose-Headers',
            'Content-Length, X-Kuma-Revision'
        );
    }

    public function testAddResponseHeadersMultipleAllowedOriginsAllowed()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('PUT')
            ->setHeader('Origin', 'https://api.example.com');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'https://api.example.com'
        );
        $this->assertHeader(
            $response,
            'Vary',
            'Origin'
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Headers')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Methods')
        );
    }

    public function testAddResponseHeadersMultipleAllowedOriginsAllowedAlreadyVary()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('PUT')
            ->setHeader('Origin', 'https://api.example.com');

        $response = Services::response(null, false)
            ->setHeader('Vary', 'Accept-Language');

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertHeader(
            $response,
            'Access-Control-Allow-Origin',
            'https://api.example.com'
        );
        $this->assertHeader(
            $response,
            'Vary',
            'Accept-Language, Origin'
        );
    }

    public function testAddResponseHeadersMultipleAllowedOriginsNotAllowed()
    {
        $config                   = $this->getDefaultConfig();
        $config['allowedOrigins'] = ['https://example.com', 'https://api.example.com'];
        $cors                     = $this->createCors($config);

        $request = $this->createRequest()
            ->withMethod('PUT')
            ->setHeader('Origin', 'https://bad.site.com');

        $response = Services::response(null, false);

        $response = $cors->addResponseHeaders($request, $response);

        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Origin')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Headers')
        );
        $this->assertFalse(
            $response->hasHeader('Access-Control-Allow-Methods')
        );
    }
}
