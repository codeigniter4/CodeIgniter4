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

use CodeIgniter\Exceptions\ConfigException;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;

/**
 * @internal
 *
 * @group Others
 */
final class CorsTest extends CIUnitTestCase
{
    private Cors $cors;
    private ResponseInterface $response;

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->resetServices();
    }

    private function createRequest(): IncomingRequest
    {
        $config    = new MockAppConfig();
        $uri       = new SiteURI($config);
        $userAgent = new UserAgent();
        $request   = $this->getMockBuilder(IncomingRequest::class)
            ->setConstructorArgs([$config, $uri, null, $userAgent])
            ->onlyMethods(['isCLI'])
            ->getMock();
        $request->method('isCLI')->willReturn(false);

        return $request;
    }

    private function createValidPreflightRequest(): IncomingRequest
    {
        $request = $this->createRequest();

        return $request->setHeader('Origin', 'http://localhost')
            ->setHeader('Access-Control-Request-Method', 'GET')
            ->withMethod('OPTIONS');
    }

    private function createValidActualRequest(): IncomingRequest
    {
        $request = $this->createRequest();
        $request->setHeader('Origin', 'http://localhost');

        return $request;
    }

    private function createCors(array $options = [])
    {
        $passedOptions = array_merge(
            [
                'allowedHeaders'      => ['x-allowed-header', 'x-other-allowed-header'],
                'allowedMethods'      => ['DELETE', 'GET', 'POST', 'PUT'],
                'allowedOrigins'      => ['http://localhost'],
                'exposedHeaders'      => [],
                'maxAge'              => 0,
                'supportsCredentials' => false,
            ],
            $options
        );

        return new Cors($passedOptions);
    }

    public function testBeforeDoesNothingWhenCLIRequest(): void
    {
        $this->cors = $this->createCors();
        $cliRequest = new CLIRequest(new MockAppConfig());

        $return = $this->cors->before($cliRequest);

        $this->assertNull($return);
    }

    private function assertHeader(string $name, string $value): void
    {
        $this->assertSame($value, $this->response->getHeaderLine($name));
    }

    public function testItDoesModifyOnARequestWithoutOrigin()
    {
        $this->cors = $this->createCors();
        $request    = $this->createRequest();

        $this->handle($request);

        $this->assertHeader(
            'Access-Control-Allow-Origin',
            'http://localhost'
        );
    }

    private function handle($request): ResponseInterface
    {
        $response = $this->cors->before($request);
        if ($response instanceof ResponseInterface) {
            $this->response = $response;

            return $response;
        }

        $response ??= Services::response();

        $response = $this->cors->after($request, $response);
        $response ??= Services::response();

        $this->response = $response;

        return $response;
    }

    public function testItDoesModifyOnARequestWithSameOrigin()
    {
        $this->cors = $this->createCors(['allowedOrigins' => ['*']]);
        $request    = $this->createRequest();
        $request->setHeader('Host', 'foo.com');
        $request->setHeader('Origin', 'http://foo.com');

        $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function testItReturnsAllowOriginHeaderOnValidActualRequest()
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsAllowOriginHeaderOnAllowAllOriginRequest()
    {
        $this->cors = $this->createCors(['allowedOrigins' => ['*']]);
        $request    = $this->createRequest();
        $request->setHeader('Origin', 'http://localhost');

        $response = $this->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function testItReturnsAllowHeadersHeaderOnAllowAllHeadersRequest()
    {
        $this->cors = $this->createCors(['allowedHeaders' => ['*']]);
        $request    = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', 'Foo, BAR');

        $response = $this->handle($request);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertHeader('Access-Control-Allow-Headers', '*');
        $this->assertFalse($response->hasHeader('Vary'));
    }

    public function testItDoesntPermitWildcardAllowedHeadersAndSupportsCredentials()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Headers response-header value.'
        );

        $this->cors = $this->createCors(['allowedHeaders' => ['*'], 'supportsCredentials' => true]);
        $request    = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', 'Foo, BAR');

        $this->handle($request);
    }

    public function testItSetsAllowCredentialsHeaderWhenFlagIsSetOnValidActualRequest()
    {
        $this->cors = $this->createCors(['supportsCredentials' => true]);
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function testItDoesNotSetAllowCredentialsHeaderWhenFlagIsNotSetOnValidActualRequest()
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testItSetsExposedHeadersWhenConfiguredOnActualRequest()
    {
        $this->cors = $this->createCors(['exposedHeaders' => ['x-exposed-header', 'x-another-exposed-header']]);
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Expose-Headers'));
        $this->assertHeader(
            'Access-Control-Expose-Headers',
            'x-exposed-header, x-another-exposed-header'
        );
    }

    public function testItDoesNotPermitWildcardAllowedOriginsAndSupportsCredentials()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Origin response-header value.'
        );

        $this->cors = $this->createCors([
            'allowedOrigins'      => ['*'],
            'supportsCredentials' => true,
        ]);
        $request = $this->createValidActualRequest();

        $this->handle($request);
    }

    public function testItDoesNotPermitWildcardAllowedOriginsAllowedMethodAndSupportsCredentials()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            'When responding to a credentialed request, the server must not specify the "*" wildcard for the Access-Control-Allow-Origin response-header value.'
        );

        $this->cors = $this->createCors([
            'allowedOrigins'      => ['*'],
            'allowedMethods'      => ['*'],
            'supportsCredentials' => true,
        ]);
        $request = $this->createValidPreflightRequest();

        $this->handle($request);
    }

    public function testItAddsAVaryHeaderWhenHasOriginPatterns()
    {
        $this->cors = $this->createCors([
            'allowedOriginsPatterns' => ['http://l(o|0)calh(o|0)st'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Vary'));
        $this->assertHeader('Vary', 'Origin');
    }

    public function testItDoesntPermitWildcardAndOrigin()
    {
        $this->expectException(ConfigException::class);
        $this->expectExceptionMessage(
            "If wildcard is specified, you must set `'allowedOrigins' => ['*']`. But using wildcard is not recommended."
        );

        $this->cors = $this->createCors([
            'allowedOrigins' => ['*', 'http://localhost'],
        ]);
        $request = $this->createValidActualRequest();

        $this->handle($request);
    }

    public function testItDoesntAddAVaryHeaderWhenSimpleOrigins()
    {
        $this->cors = $this->createCors([
            'allowedOrigins' => ['http://localhost'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $this->assertFalse($response->hasHeader('Vary'));
    }

    public function testItAddsAVaryHeaderWhenMultipleOrigins()
    {
        $this->cors = $this->createCors([
            'allowedOrigins' => ['http://localhost', 'http://example.com'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $this->assertTrue($response->hasHeader('Vary'));
    }

    public function testItReturnsAccessControlHeadersOnCorsRequest()
    {
        $this->cors = $this->createCors();
        $request    = $this->createRequest();
        $request->setHeader('Origin', 'http://localhost');

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsAccessControlHeadersOnCorsRequestWithPatternOrigin()
    {
        $this->cors = $this->createCors([
            'allowedOrigins'         => [],
            'allowedOriginsPatterns' => ['http://l(o|0)calh(o|0)st'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $this->assertTrue($response->hasHeader('Vary'));
        $this->assertHeader('Vary', 'Origin');
    }

    public function testItReturnsAccessControlHeadersOnValidPreflightRequest()
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsOkOnValidPreflightRequestWithRequestedHeadersAllowed()
    {
        $this->cors     = $this->createCors();
        $requestHeaders = 'X-Allowed-Header, x-other-allowed-header';
        $request        = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', $requestHeaders);

        $response = $this->handle($request);

        $this->assertSame(204, $response->getStatusCode());

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Headers'));
        // the response will have the "allowedHeaders" value passed to Cors rather than the request one
        $this->assertHeader('Access-Control-Allow-Headers', 'x-allowed-header, x-other-allowed-header');
    }

    public function testItSetsAllowCredentialsHeaderWhenFlagIsSetOnValidPreflightRequest()
    {
        $this->cors = $this->createCors(['supportsCredentials' => true]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function testItDoesNotSetAllowCredentialsHeaderWhenFlagIsNotSetOnValidPreflightRequest()
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testItSetsMaxAgeWhenSet()
    {
        $this->cors = $this->createCors(['maxAge' => 42]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertHeader('Access-Control-Max-Age', '42');
    }

    public function testItSetsMaxAgeWhenZero()
    {
        $this->cors = $this->createCors(['maxAge' => 0]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertHeader('Access-Control-Max-Age', '0');
    }

    public function testItSkipsEmptyAccessControlRequestHeader()
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', '');

        $response = $this->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }
}
