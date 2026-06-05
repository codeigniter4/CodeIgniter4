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

use CodeIgniter\Test\CIUnitTestCase;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class RequestIdTest extends CIUnitTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        context()->clearAll();
    }

    public function testBefore(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);

        $filter->before($request);

        $requestId = context()->get('request_id');

        $this->assertNotEmpty($requestId);
        $this->assertSame(32, strlen($requestId));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9._:-]+$/', $requestId);
    }

    public function testBeforeWithExistingRequestId(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);

        $existingRequestId = 'test-request-id-123';
        $request->setHeader('X-Request-ID', $existingRequestId);

        $filter->before($request);

        $requestId = context()->get('request_id');

        $this->assertSame($existingRequestId, $requestId);
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9._:-]+$/', $requestId);
    }

    public function testBeforeWithExistingInvalidRequestId(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);

        $existingRequestId = 'Abc@!#$';
        $request->setHeader('X-Request-ID', $existingRequestId);

        $filter->before($request);

        $requestId = context()->get('request_id');

        $this->assertNotSame($existingRequestId, $requestId);
        $this->assertSame(32, strlen($requestId));
        $this->assertMatchesRegularExpression('/^[A-Za-z0-9._:-]+$/', $requestId);
    }

    public function testAfter(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);
        $response = service('response', null, false);

        context()->set('request_id', 'test-request-id-123');

        $filter->after($request, $response);

        $this->assertTrue($response->hasHeader('X-Request-ID'));
        $this->assertSame('test-request-id-123', $response->getHeaderLine('X-Request-ID'));
    }

    public function testAfterWithoutRequestId(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);
        $response = service('response', null, false);

        context()->remove('request_id');

        $filter->after($request, $response);

        $this->assertFalse($response->hasHeader('X-Request-ID'));
    }

    public function testResponseOutputsRequestIdFromRequestHeader(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);
        $response = service('response', null, false);

        $existingRequestId = 'test-request-id-123';
        $request->setHeader('X-Request-ID', $existingRequestId);

        $filter->before($request);

        $filter->after($request, $response);

        $this->assertTrue($response->hasHeader('X-Request-ID'));
        $this->assertSame($existingRequestId, $response->getHeaderLine('X-Request-ID'));
    }

    public function testResponseOutputsGeneratedRequestId(): void
    {
        $filter  = new RequestId();
        $request = service('request', null, false);
        $response = service('response', null, false);

        context()->remove('request_id');

        $filter->before($request);

        $generatedRequestId = context()->get('request_id');

        $filter->after($request, $response);

        $this->assertTrue($response->hasHeader('X-Request-ID'));
        $this->assertSame($generatedRequestId, $response->getHeaderLine('X-Request-ID'));
    }
}
