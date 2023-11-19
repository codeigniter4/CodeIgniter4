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

/**
 * HTTP Method List
 */
class Method
{
    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/CONNECT
     */
    public const CONNECT = 'CONNECT';

    /**
     * Idempotent
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/DELETE
     */
    public const DELETE = 'DELETE';

    /**
     * Safe, Idempotent, Cacheable
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/GET
     */
    public const GET = 'GET';

    /**
     * Safe, Idempotent, Cacheable
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/HEAD
     */
    public const HEAD = 'HEAD';

    /**
     * Safe, Idempotent
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/OPTIONS
     */
    public const OPTIONS = 'OPTIONS';

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PATCH
     */
    public const PATCH = 'PATCH';

    /**
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/POST
     */
    public const POST = 'POST';

    /**
     * Idempotent
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/PUT
     */
    public const PUT = 'PUT';

    /**
     * Safe, Idempotent
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Methods/TRACE
     */
    public const TRACE = 'TRACE';

    /**
     * Returns all HTTP methods.
     *
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::CONNECT,
            self::DELETE,
            self::GET,
            self::HEAD,
            self::OPTIONS,
            self::PATCH,
            self::POST,
            self::PUT,
            self::TRACE,
        ];
    }
}
