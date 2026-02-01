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

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class RestrictTest extends CIUnitTestCase
{
    public function testConstructorWithNoRestrictions(): void
    {
        $restrict = new Restrict();

        $this->assertNull($restrict->environment);
        $this->assertNull($restrict->hostname);
        $this->assertNull($restrict->subdomain);
    }

    public function testConstructorWithEnvironmentOnly(): void
    {
        $restrict = new Restrict(environment: 'production');

        $this->assertSame('production', $restrict->environment);
        $this->assertNull($restrict->hostname);
        $this->assertNull($restrict->subdomain);
    }

    public function testConstructorWithHostnameOnly(): void
    {
        $restrict = new Restrict(hostname: 'example.com');

        $this->assertNull($restrict->environment);
        $this->assertSame('example.com', $restrict->hostname);
        $this->assertNull($restrict->subdomain);
    }

    public function testConstructorWithSubdomainOnly(): void
    {
        $restrict = new Restrict(subdomain: 'api');

        $this->assertNull($restrict->environment);
        $this->assertNull($restrict->hostname);
        $this->assertSame('api', $restrict->subdomain);
    }

    public function testConstructorWithMultipleRestrictions(): void
    {
        $restrict = new Restrict(
            environment: ['production', 'staging'],
            hostname: ['example.com', 'test.com'],
            subdomain: ['api', 'admin'],
        );

        $this->assertSame(['production', 'staging'], $restrict->environment);
        $this->assertSame(['example.com', 'test.com'], $restrict->hostname);
        $this->assertSame(['api', 'admin'], $restrict->subdomain);
    }

    public function testBeforeReturnsNullWhenNoRestrictionsSet(): void
    {
        $restrict = new Restrict();
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testAfterReturnsNull(): void
    {
        $restrict = new Restrict(environment: 'testing');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');
        $response = Services::response();

        $result = $restrict->after($request, $response);

        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testCheckEnvironmentAllowsCurrentEnvironment(): void
    {
        $restrict = new Restrict(environment: ENVIRONMENT);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckEnvironmentAllowsFromArray(): void
    {
        $restrict = new Restrict(environment: ['development', 'testing']);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckEnvironmentThrowsWhenNotAllowed(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Current environment is not allowed.');

        $restrict = new Restrict(environment: 'production');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckEnvironmentThrowsWhenExplicitlyDenied(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Current environment is blocked.');

        $currentEnv = ENVIRONMENT;
        $restrict   = new Restrict(environment: ['!' . $currentEnv]);
        $request    = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckEnvironmentDenialTakesPrecedence(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Current environment is blocked.');

        $currentEnv = ENVIRONMENT;
        // Include current env in allowed list but also deny it
        $restrict = new Restrict(environment: [$currentEnv, '!' . $currentEnv]);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckEnvironmentAllowsWithOnlyDenials(): void
    {
        // Only deny production, allow everything else
        $restrict = new Restrict(environment: ['!production', '!staging']);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckEnvironmentWithEmptyArray(): void
    {
        $restrict = new Restrict(environment: []);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckHostnameAllowsSingleHost(): void
    {
        $restrict = new Restrict(hostname: 'example.com');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckHostnameAllowsFromArray(): void
    {
        $restrict = new Restrict(hostname: ['example.com', 'test.com']);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckHostnameThrowsWhenNotAllowed(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Host is not allowed.');

        $restrict = new Restrict(hostname: 'allowed.com');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckHostnameIsCaseInsensitive(): void
    {
        $restrict = new Restrict(hostname: 'EXAMPLE.COM');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckHostnameWithEmptyArray(): void
    {
        $restrict = new Restrict(hostname: []);
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckSubdomainAllowsSingleSubdomain(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckSubdomainAllowsFromArray(): void
    {
        $restrict = new Restrict(subdomain: ['api', 'admin']);
        $request  = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckSubdomainThrowsWhenNotAllowed(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: subdomain is blocked.');

        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'admin.example.com');

        $restrict->before($request);
    }

    public function testCheckSubdomainThrowsWhenNoSubdomainExists(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Subdomain required');

        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckSubdomainIsCaseInsensitive(): void
    {
        $restrict = new Restrict(subdomain: 'API');
        $request  = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testCheckSubdomainWithEmptyArray(): void
    {
        $restrict = new Restrict(subdomain: []);
        $request  = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testGetSubdomainReturnsEmptyForLocalhost(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'localhost');

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Subdomain required');

        $restrict->before($request);
    }

    public function testGetSubdomainReturnsEmptyForIPAddress(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', '192.168.1.1');

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Subdomain required');

        $restrict->before($request);
    }

    public function testGetSubdomainReturnsEmptyForTwoPartDomain(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'example.com');

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Subdomain required');

        $restrict->before($request);
    }

    public function testGetSubdomainHandlesSingleSubdomain(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testGetSubdomainHandlesMultipleSubdomains(): void
    {
        $restrict = new Restrict(subdomain: 'admin.api');
        $request  = $this->createMockRequest('GET', '/test', 'admin.api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testGetSubdomainHandlesTwoPartTLD(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'api.example.co.uk');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testGetSubdomainReturnsEmptyForDomainWithTwoPartTLD(): void
    {
        $restrict = new Restrict(subdomain: 'api');
        $request  = $this->createMockRequest('GET', '/test', 'example.co.uk');

        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Subdomain required');

        $restrict->before($request);
    }

    public function testGetSubdomainHandlesMultipleSubdomainsWithTwoPartTLD(): void
    {
        $restrict = new Restrict(subdomain: 'admin.api');
        $request  = $this->createMockRequest('GET', '/test', 'admin.api.example.co.uk');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testMultipleRestrictionsAllMustPass(): void
    {
        $restrict = new Restrict(
            environment: ENVIRONMENT,
            hostname: ['api.example.com', 'example.com'],
            subdomain: ['api'],
        );
        $request = $this->createMockRequest('GET', '/test', 'api.example.com');

        $result = $restrict->before($request);

        $this->assertNull($result);
    }

    public function testMultipleRestrictionsFailIfAnyFails(): void
    {
        $this->expectException(PageNotFoundException::class);

        $restrict = new Restrict(
            environment: ENVIRONMENT,  // Passes
            hostname: 'api.example.com', // Passes
            subdomain: 'admin',           // Fails
        );
        $request = $this->createMockRequest('GET', '/test', 'api.example.com');

        $restrict->before($request);
    }

    public function testCheckEnvironmentFailsFirst(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Current environment is not allowed.');

        $restrict = new Restrict(
            environment: 'production',  // Fails
            hostname: 'example.com',     // Would pass
        );
        $request = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckHostnameFailsSecond(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: Host is not allowed.');

        $restrict = new Restrict(
            environment: ENVIRONMENT,   // Passes
            hostname: 'allowed.com',    // Fails
            subdomain: 'api',            // Would fail
        );
        $request = $this->createMockRequest('GET', '/test', 'example.com');

        $restrict->before($request);
    }

    public function testCheckSubdomainFailsLast(): void
    {
        $this->expectException(PageNotFoundException::class);
        $this->expectExceptionMessage('Access denied: subdomain is blocked.');

        $restrict = new Restrict(
            environment: ENVIRONMENT,     // Passes
            hostname: 'api.example.com',  // Passes
            subdomain: 'admin',            // Fails
        );
        $request = $this->createMockRequest('GET', '/test', 'api.example.com');

        $restrict->before($request);
    }

    private function createMockRequest(string $method, string $path, string $host = 'example.com', string $query = ''): IncomingRequest
    {
        $config = new MockAppConfig();
        // Use the host parameter to properly set the host in SiteURI
        $uri       = new SiteURI($config, $path . ($query !== '' ? '?' . $query : ''), $host, 'http');
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
