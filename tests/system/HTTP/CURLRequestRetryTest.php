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

use CodeIgniter\Config\Factories;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Superglobals;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCURLRequest;
use Config\App;
use Config\CURLRequest as ConfigCURLRequest;
use PHPUnit\Framework\Attributes\Group;

/**
 * @internal
 */
#[Group('Others')]
final class CURLRequestRetryTest extends CIUnitTestCase
{
    private MockCURLRequest $request;

    protected function setUp(): void
    {
        parent::setUp();

        $this->resetServices();
        Services::injectMock('superglobals', new Superglobals());
        $this->request = $this->getRequest();
    }

    /**
     * @param array<string, mixed> $options
     */
    private function getRequest(array $options = []): MockCURLRequest
    {
        $uri = new URI($options['baseURI'] ?? null);

        $config               = new ConfigCURLRequest();
        $config->shareOptions = false;

        Factories::injectMock('config', 'CURLRequest', $config);

        return new MockCURLRequest(new App(), $uri, new Response(), $options);
    }

    public function testRetryIntegerRetriesDefaultStatusCodes(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFirst failure",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry'       => 3,
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Success', $response->getBody());
        $this->assertSame([1.0], $this->request->getSleeps());
    }

    public function testRetryUsesCustomStatusCodes(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 500 Internal Server Error\r\n\r\nFirst failure",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries'  => 1,
                'delay'        => 100,
                'status_codes' => [500],
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([0.1], $this->request->getSleeps());
    }

    public function testRetryDoesNotRetryUnconfiguredStatusCode(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 404 Not Found\r\n\r\nMissing",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry'       => 3,
            'http_errors' => false,
        ]);

        $this->assertSame(404, $response->getStatusCode());
        $this->assertSame('Missing', $response->getBody());
        $this->assertSame([], $this->request->getSleeps());
    }

    public function testZeroRetriesDisableRetryHandling(): void
    {
        $this->request->setOutput("HTTP/1.1 200 OK\r\n\r\nSuccess");

        $this->request->get('http://example.com', [
            'retry' => ['max_retries' => 0],
        ]);

        $this->assertTrue($this->request->curl_options[CURLOPT_FAILONERROR]);
        $this->assertSame([], $this->request->getSleeps());
    }

    public function testRetryUsesDelayBackoffArray(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFirst failure",
            "HTTP/1.1 503 Service Unavailable\r\n\r\nSecond failure",
            "HTTP/1.1 503 Service Unavailable\r\n\r\nThird failure",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 3,
                'delay'       => [100, 500],
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([0.1, 0.5, 0.5], $this->request->getSleeps());
    }

    public function testRetryClampsNegativeDelays(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFirst failure",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => -100,
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([0.0], $this->request->getSleeps());
    }

    public function testRetryAfterSecondsOverridesConfiguredDelay(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 429 Too Many Requests\r\nRetry-After: 2\r\n\r\nRate limited",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([2.0], $this->request->getSleeps());
    }

    public function testRetryAfterDateOverridesConfiguredDelay(): void
    {
        $retryAfter = gmdate('D, d M Y H:i:s', time() + 3600) . ' GMT';

        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\nRetry-After: {$retryAfter}\r\n\r\nUnavailable",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
                'max_delay'   => 5000,
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([5.0], $this->request->getSleeps());
    }

    public function testRetryAfterIsCappedByDefaultMaxDelay(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 429 Too Many Requests\r\nRetry-After: 3600\r\n\r\nRate limited",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry'       => 1,
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([30.0], $this->request->getSleeps());
    }

    public function testRetryAfterCanBeDisabled(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 429 Too Many Requests\r\nRetry-After: 2\r\n\r\nRate limited",
            "HTTP/1.1 200 OK\r\n\r\nSuccess",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries'         => 1,
                'delay'               => 100,
                'respect_retry_after' => false,
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame([0.1], $this->request->getSleeps());
    }

    public function testRetryThrowsAfterExhaustingRetriesWhenHttpErrorsEnabled(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFirst failure",
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFinal failure",
        ]);

        $this->expectException(HTTPException::class);
        $this->expectExceptionMessage('22 : The requested URL returned error: 503');

        $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
            ],
        ]);
    }

    public function testRetryReturnsFinalResponseAfterExhaustingRetriesWhenHttpErrorsDisabled(): void
    {
        $this->request->setOutputs([
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFirst failure",
            "HTTP/1.1 503 Service Unavailable\r\n\r\nFinal failure",
        ]);

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
            ],
            'http_errors' => false,
        ]);

        $this->assertSame(503, $response->getStatusCode());
        $this->assertSame('Final failure', $response->getBody());
        $this->assertSame([0.1], $this->request->getSleeps());
    }

    public function testCurlErrorsAreNotRetriedByDefault(): void
    {
        $this->request->setCurlErrors([
            [CURLE_OPERATION_TIMEDOUT, 'Operation timed out'],
        ]);

        $this->expectException(HTTPException::class);

        $this->request->get('http://example.com', [
            'retry' => 3,
        ]);
    }

    public function testCurlErrorsCanBeRetried(): void
    {
        $this->request->setCurlErrors([
            [CURLE_OPERATION_TIMEDOUT, 'Operation timed out'],
        ])->setOutput("HTTP/1.1 200 OK\r\n\r\nSuccess");

        $response = $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
                'curl_errors' => true,
            ],
        ]);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('Success', $response->getBody());
        $this->assertSame([0.1], $this->request->getSleeps());
    }

    public function testNonTransientCurlErrorsAreNotRetried(): void
    {
        $this->request->setCurlErrors([
            [CURLE_UNSUPPORTED_PROTOCOL, 'Unsupported protocol'],
        ]);

        $this->expectException(HTTPException::class);

        $this->request->get('http://example.com', [
            'retry' => [
                'max_retries' => 1,
                'delay'       => 100,
                'curl_errors' => true,
            ],
        ]);
    }
}
