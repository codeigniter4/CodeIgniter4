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
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockAppConfig;
use Config\Services;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class FilterTest extends CIUnitTestCase
{
    public function testConstructorWithFilterNameOnly(): void
    {
        $filter = new Filter(by: 'auth');

        $this->assertSame('auth', $filter->by);
        $this->assertSame([], $filter->having);
    }

    public function testConstructorWithFilterNameAndArguments(): void
    {
        $filter = new Filter(by: 'auth', having: ['admin', 'editor']);

        $this->assertSame('auth', $filter->by);
        $this->assertSame(['admin', 'editor'], $filter->having);
    }

    public function testConstructorWithEmptyHaving(): void
    {
        $filter = new Filter(by: 'throttle', having: []);

        $this->assertSame('throttle', $filter->by);
        $this->assertSame([], $filter->having);
    }

    public function testBeforeReturnsNull(): void
    {
        $filter  = new Filter(by: 'auth');
        $request = $this->createMockRequest('GET', '/test');

        $result = $filter->before($request);

        $this->assertNull($result);
    }

    public function testAfterReturnsNull(): void
    {
        $filter   = new Filter(by: 'toolbar');
        $request  = $this->createMockRequest('GET', '/test');
        $response = Services::response();

        $result = $filter->after($request, $response);

        $this->assertNotInstanceOf(ResponseInterface::class, $result);
    }

    public function testGetFiltersReturnsArrayWithFilterNameOnly(): void
    {
        $filter = new Filter(by: 'csrf');

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('csrf', $filters[0]);
    }

    public function testGetFiltersReturnsArrayWithFilterNameAndArguments(): void
    {
        $filter = new Filter(by: 'auth', having: ['admin']);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('auth:admin', $filters[0]);
    }

    public function testGetFiltersReturnsArrayWithMultipleArguments(): void
    {
        $filter = new Filter(by: 'permission', having: ['posts.edit', 'posts.delete']);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('permission:posts.edit,posts.delete', $filters[0]);
    }

    public function testGetFiltersWithEmptyHavingReturnsSimpleArray(): void
    {
        $filter = new Filter(by: 'cors', having: []);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('cors', $filters[0]);
    }

    public function testMultipleFiltersCanBeCreated(): void
    {
        $filter1 = new Filter(by: 'auth');
        $filter2 = new Filter(by: 'csrf');
        $filter3 = new Filter(by: 'throttle', having: ['60', '1']);

        $this->assertSame('auth', $filter1->by);
        $this->assertSame('csrf', $filter2->by);
        $this->assertSame('throttle', $filter3->by);
        $this->assertSame(['60', '1'], $filter3->having);
    }

    public function testGetFiltersFormatIsConsistentAcrossInstances(): void
    {
        $filterWithoutArgs = new Filter(by: 'filter1');
        $filterWithArgs    = new Filter(by: 'filter2', having: ['arg1']);

        $filters1 = $filterWithoutArgs->getFilters();
        $filters2 = $filterWithArgs->getFilters();

        // Without args:
        $this->assertCount(1, $filters1);
        $this->assertSame('filter1', $filters1[0]);

        // With args:
        $this->assertCount(1, $filters2);
        $this->assertSame('filter2:arg1', $filters2[0]);
    }

    public function testFilterWithNumericArguments(): void
    {
        $filter = new Filter(by: 'rate_limit', having: [100, 60]);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('rate_limit:100,60', $filters[0]);
    }

    public function testFilterWithMixedTypeArguments(): void
    {
        $filter = new Filter(by: 'custom', having: ['string', 123, true]);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('custom:string,123,1', $filters[0]);
    }

    public function testFilterWithAssociativeArrayArguments(): void
    {
        $filter = new Filter(by: 'configured', having: ['option1' => 'value1', 'option2' => 'value2']);

        $filters = $filter->getFilters();

        $this->assertCount(1, $filters);
        $this->assertSame('configured:value1,value2', $filters[0]);
    }

    public function testBeforeDoesNotModifyRequest(): void
    {
        $filter  = new Filter(by: 'auth', having: ['admin']);
        $request = $this->createMockRequest('POST', '/admin/users');

        $originalMethod = $request->getMethod();
        $originalPath   = $request->getUri()->getPath();

        $result = $filter->before($request);

        $this->assertNull($result);
        $this->assertSame($originalMethod, $request->getMethod());
        $this->assertSame($originalPath, $request->getUri()->getPath());
    }

    public function testAfterDoesNotModifyResponse(): void
    {
        $filter   = new Filter(by: 'toolbar');
        $request  = $this->createMockRequest('GET', '/test');
        $response = Services::response();
        $response->setBody('Test content');
        $response->setStatusCode(200);

        $result = $filter->after($request, $response);

        $this->assertNotInstanceOf(ResponseInterface::class, $result);
        $this->assertSame('Test content', $response->getBody());
        $this->assertSame(200, $response->getStatusCode());
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
