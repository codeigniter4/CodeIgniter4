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
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use PHPUnit\Framework\Attributes\Group;

/**
 * This test case is based on:
 *   https://github.com/asm89/stack-cors/blob/master/tests/CorsTest.php
 *   https://github.com/asm89/stack-cors/blob/b6920bd8996449400ac976e083b55fb45f035467/tests/CorsTest.php
 *
 * @internal
 */
#[Group('Others')]
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

    /**
     * @param array<string, mixed> $options
     */
    private function createCors(array $options = []): Cors
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

    public function testBeforeDoesNothingWhenCliRequest(): void
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

    public function testItDoesModifyOnARequestWithoutOrigin(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createRequest();

        $this->handle($request);

        $this->assertHeader(
            'Access-Control-Allow-Origin',
            'http://localhost'
        );
    }

    public function testItDoesNotModifyOnARequestWithoutOrigin(): void
    {
        $this->cors = $this->createCors([
            'allowedOrigins' => ['https://www.example.com', 'https://app.example.com'],
        ]);
        $request = $this->createRequest();

        $response = $this->handle($request);

        $this->assertFalse($response->hasHeader('Access-Control-Allow-Origin'));
    }

    private function handle(RequestInterface $request): ResponseInterface
    {
        $response = $this->cors->before($request);
        if ($response instanceof ResponseInterface) {
            $this->response = $response;

            return $response;
        }

        $response ??= service('response');

        $response = $this->cors->after($request, $response);
        $response ??= service('response');

        $this->response = $response;

        return $response;
    }

    public function testItDoesModifyOnARequestWithSameOrigin(): void
    {
        $this->cors = $this->createCors(['allowedOrigins' => ['*']]);
        $request    = $this->createRequest();
        $request->setHeader('Host', 'foo.com');
        $request->setHeader('Origin', 'http://foo.com');

        $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function testItReturnsAllowOriginHeaderOnValidActualRequest(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsAllowOriginHeaderOnAllowAllOriginRequest(): void
    {
        $this->cors = $this->createCors(['allowedOrigins' => ['*']]);
        $request    = $this->createRequest();
        $request->setHeader('Origin', 'http://localhost');

        $response = $this->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', '*');
    }

    public function testItReturnsAllowHeadersHeaderOnAllowAllHeadersRequest(): void
    {
        $this->cors = $this->createCors(['allowedHeaders' => ['*']]);
        $request    = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', 'Foo, BAR');

        $response = $this->handle($request);

        $this->assertSame(204, $response->getStatusCode());
        $this->assertHeader('Access-Control-Allow-Headers', '*');
        // Always adds `Vary: Access-Control-Request-Method` header.
        $this->assertHeader('Vary', 'Access-Control-Request-Method');
    }

    public function testItDoesntPermitWildcardAllowedHeadersAndSupportsCredentials(): void
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

    public function testItSetsAllowCredentialsHeaderWhenFlagIsSetOnValidActualRequest(): void
    {
        $this->cors = $this->createCors(['supportsCredentials' => true]);
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function testItDoesNotSetAllowCredentialsHeaderWhenFlagIsNotSetOnValidActualRequest(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testItSetsExposedHeadersWhenConfiguredOnActualRequest(): void
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

    public function testItDoesNotPermitWildcardAllowedOriginsAndSupportsCredentials(): void
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

    public function testItDoesNotPermitWildcardAllowedOriginsAllowedMethodAndSupportsCredentials(): void
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

    public function testItAddsAVaryHeaderWhenHasOriginPatterns(): void
    {
        $this->cors = $this->createCors([
            'allowedOriginsPatterns' => ['http://l(o|0)calh(o|0)st'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Vary'));
        $this->assertHeader('Vary', 'Origin');
    }

    public function testItDoesntPermitWildcardAndOrigin(): void
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

    public function testItDoesntAddAVaryHeaderWhenSimpleOrigins(): void
    {
        $this->cors = $this->createCors([
            'allowedOrigins' => ['http://localhost'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $this->assertFalse($response->hasHeader('Vary'));
    }

    public function testItAddsAVaryHeaderWhenMultipleOrigins(): void
    {
        $this->cors = $this->createCors([
            'allowedOrigins' => ['http://localhost', 'http://example.com'],
        ]);
        $request = $this->createValidActualRequest();

        $response = $this->handle($request);

        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
        $this->assertTrue($response->hasHeader('Vary'));
    }

    public function testItReturnsAccessControlHeadersOnCorsRequest(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createRequest();
        $request->setHeader('Origin', 'http://localhost');

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsAccessControlHeadersOnCorsRequestWithPatternOrigin(): void
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

    public function testItReturnsAccessControlHeadersOnValidPreflightRequest(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Origin'));
        $this->assertHeader('Access-Control-Allow-Origin', 'http://localhost');
    }

    public function testItReturnsOkOnValidPreflightRequestWithRequestedHeadersAllowed(): void
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

    public function testItSetsAllowCredentialsHeaderWhenFlagIsSetOnValidPreflightRequest(): void
    {
        $this->cors = $this->createCors(['supportsCredentials' => true]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertHeader('Access-Control-Allow-Credentials', 'true');
    }

    public function testItDoesNotSetAllowCredentialsHeaderWhenFlagIsNotSetOnValidPreflightRequest(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testItSetsMaxAgeWhenSet(): void
    {
        $this->cors = $this->createCors(['maxAge' => 42]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertHeader('Access-Control-Max-Age', '42');
    }

    public function testItSetsMaxAgeWhenZero(): void
    {
        $this->cors = $this->createCors(['maxAge' => 0]);
        $request    = $this->createValidPreflightRequest();

        $response = $this->handle($request);

        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertHeader('Access-Control-Max-Age', '0');
    }

    public function testItSkipsEmptyAccessControlRequestHeader(): void
    {
        $this->cors = $this->createCors();
        $request    = $this->createValidPreflightRequest();
        $request->setHeader('Access-Control-Request-Headers', '');

        $response = $this->handle($request);

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testItAddsVaryAccessControlRequestMethodHeaderEvenIfItIsNormalOptionsRequest(): void
    {
        $this->cors = $this->createCors(['allowedHeaders' => ['*']]);
        $request    = $this->createRequest()->withMethod('OPTIONS');

        $response = $this->handle($request);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Access-Control-Allow-Headers'));
        // Always adds `Vary: Access-Control-Request-Method` header.
        $this->assertHeader('Vary', 'Access-Control-Request-Method');
    }
}
