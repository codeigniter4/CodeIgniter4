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

use Attribute;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\I18n\Time;

/**
 * Cache Attribute
 *
 * Caches the response of a controller method at the server level for a specified duration.
 * This is server-side caching to avoid expensive operations, not browser-level caching.
 *
 * Usage:
 * ```php
 * #[Cache(for: 3600)] // Cache for 1 hour
 * #[Cache(for: 300, key: 'custom_key')] // Cache with custom key
 * ```
 *
 * Limitations:
 * - Only caches GET requests; POST, PUT, DELETE, and other methods are ignored
 * - Streaming responses or file downloads may not cache properly
 * - Cache key includes HTTP method, path, query string, and possibly user_id(), but not request headers
 * - Does not automatically invalidate related cache entries
 * - Cookies set in the response are cached and reused for all subsequent requests
 * - Large responses may impact cache storage performance
 * - Browser Cache-Control headers do not affect server-side caching behavior
 *
 * Security Considerations:
 * - Ensure cache backend is properly secured and not accessible publicly
 * - Be aware that authorization checks happen before cache lookup
 */
#[Attribute(Attribute::TARGET_METHOD)]
class Cache implements RouteAttributeInterface
{
    public function __construct(
        public int $for = 3600,
        public ?string $key = null,
    ) {
    }

    public function before(RequestInterface $request): RequestInterface|ResponseInterface|null
    {
        // Only cache GET requests
        if ($request->getMethod() !== 'GET') {
            return null;
        }

        // Check cache before controller execution
        $cacheKey = $this->key ?? $this->generateCacheKey($request);

        $cached = cache($cacheKey);
        // Validate cached data structure
        if ($cached !== null && (is_array($cached) && isset($cached['body'], $cached['headers'], $cached['status']))) {
            $response = service('response');
            $response->setBody($cached['body']);
            $response->setStatusCode($cached['status']);
            // Mark response as served from cache to prevent re-caching
            $response->setHeader('X-Cached-Response', 'true');

            // Restore headers from cached array of header name => value strings
            foreach ($cached['headers'] as $name => $value) {
                $response->setHeader($name, $value);
            }
            $time = Time::now()->getTimestamp();
            $response->setHeader('Age', (string) ($time - ($cached['timestamp'] ?? $time)));

            return $response;
        }

        return null; // Continue to controller
    }

    public function after(RequestInterface $request, ResponseInterface $response): ?ResponseInterface
    {
        // Don't re-cache if response was already served from cache
        if ($response->hasHeader('X-Cached-Response')) {
            // Remove the marker header before sending response
            $response->removeHeader('X-Cached-Response');

            return null;
        }

        // Only cache GET requests
        if ($request->getMethod() !== 'GET') {
            return null;
        }

        $cacheKey = $this->key ?? $this->generateCacheKey($request);

        // Convert Header objects to strings for caching
        $headers = [];

        foreach ($response->headers() as $name => $header) {
            // Handle both single Header and array of Headers
            if (is_array($header)) {
                // Multiple headers with same name
                $values = [];

                foreach ($header as $h) {
                    $values[] = $h->getValueLine();
                }
                $headers[$name] = implode(', ', $values);
            } else {
                // Single header
                $headers[$name] = $header->getValueLine();
            }
        }

        $data = [
            'body'      => $response->getBody(),
            'headers'   => $headers,
            'status'    => $response->getStatusCode(),
            'timestamp' => Time::now()->getTimestamp(),
        ];

        cache()->save($cacheKey, $data, $this->for);

        return $response;
    }

    protected function generateCacheKey(RequestInterface $request): string
    {
        return 'route_cache_' . hash(
            'xxh128',
            $request->getMethod() .
            $request->getUri()->getPath() .
            $request->getUri()->getQuery() .
            (function_exists('user_id') ? user_id() : ''),
        );
    }
}
