<?php

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

/**
 * @internal
 *
 * @group Others
 */
final class OutgoingRequestTest extends CIUnitTestCase
{
    public function testCreateWithHeader(): void
    {
        $uri     = new URI('https://example.com/');
        $headers = ['User-Agent' => 'Mozilla/5.0'];
        $request = new OutgoingRequest('GET', $uri, $headers);

        $this->assertSame('Mozilla/5.0', $request->header('User-Agent')->getValue());
    }

    public function testGetUri(): void
    {
        $uri     = new URI('https://example.com/');
        $request = new OutgoingRequest('GET', $uri);

        $this->assertSame($uri, $request->getUri());
    }

    public function testWithMethod(): void
    {
        $uri     = new URI('https://example.com/');
        $request = new OutgoingRequest('GET', $uri);

        $newRequest = $request->withMethod('POST');

        $this->assertSame('GET', strtoupper($request->getMethod()));
        $this->assertSame('POST', strtoupper($newRequest->getMethod()));
    }

    public function testWithUri(): void
    {
        $uri     = new URI('https://example.com/');
        $request = new OutgoingRequest('GET', $uri);

        $newUri     = new URI('https://example.jp/');
        $newRequest = $request->withUri($newUri);

        $this->assertSame('example.jp', $newRequest->header('Host')->getValue());
    }

    /**
     * If the Host header is missing or empty, and the new URI contains
     *   a host component, this method MUST update the Host header in the returned
     *   request.
     * https://www.php-fig.org/psr/psr-7/#32-psrhttpmessagerequestinterface
     */
    public function testWithUriPreserveHostHostHeaderIsMissingAndNewUriContainsHost(): void
    {
        $uri     = new URI();
        $request = new OutgoingRequest('GET', $uri);

        $newUri     = new URI('https://example.com/');
        $newRequest = $request->withUri($newUri, true);

        $this->assertSame('example.com', $newRequest->header('Host')->getValue());
    }

    /**
     * If the Host header is missing or empty, and the new URI does not contain a
     *   host component, this method MUST NOT update the Host header in the returned
     *   request.
     * https://www.php-fig.org/psr/psr-7/#32-psrhttpmessagerequestinterface
     */
    public function testWithUriPreserveHostHostHeaderIsMissingAndNewUriDoesNotContainsHost(): void
    {
        $uri     = new URI();
        $request = new OutgoingRequest('GET', $uri);

        $newUri     = new URI();
        $newRequest = $request->withUri($newUri, true);

        $this->assertSame($request->header('Host'), $newRequest->header('Host'));
    }

    /**
     * If a Host header is present and non-empty, this method MUST NOT update
     *   the Host header in the returned request.
     * https://www.php-fig.org/psr/psr-7/#32-psrhttpmessagerequestinterface
     */
    public function testWithUriPreserveHostHostHostIsNonEmpty(): void
    {
        $uri     = new URI('https://example.com/');
        $request = new OutgoingRequest('GET', $uri);

        $newUri     = new URI('https://example.jp/');
        $newRequest = $request->withUri($newUri, true);

        $this->assertSame('example.com', $newRequest->header('Host')->getValue());
    }
}
