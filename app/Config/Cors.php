<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

/**
 * Cross-Origin Resource Sharing (CORS) Configuration
 *
 * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
 */
class Cors extends BaseConfig
{
    /**
     * Origins for the `Access-Control-Allow-Origin` header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
     *
     * E.g.:
     *   - ['http://localhost:8080']
     *   - ['https://www.example.com']
     *
     * @var list<string>
     */
    public array $allowedOrigins = [];

    /**
     * Origin regex patterns for the `Access-Control-Allow-Origin` header.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Origin
     *
     * NOTE: You must set `\A` (beginning with) and `\z` (ending with) in patterns,
     *       otherwise you will give access from unintended external domains.
     *       E.g., if you set '!\w+\.example\.com!', 'www.example.com.evil.site'
     *       is permitted.
     *
     * E.g.:
     *   - ['!\Ahttps://\w+\.example\.com\z!']
     *
     * @var list<string>
     */
    public array $allowedOriginsPatterns = [];

    /**
     * Weather to send the `Access-Control-Allow-Credentials` header.
     *
     * The Access-Control-Allow-Credentials response header tells browsers whether
     * the server allows cross-origin HTTP requests to include credentials.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Credentials
     */
    public bool $supportsCredentials = false;

    /**
     * Set headers to allow.
     *
     * The Access-Control-Allow-Headers response header is used in response to
     * a preflight request which includes the Access-Control-Request-Headers to
     * indicate which HTTP headers can be used during the actual request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Headers
     *
     * @var list<string>
     */
    public array $allowedHeaders = [];

    /**
     * Set headers to expose.
     *
     * The Access-Control-Expose-Headers response header allows a server to
     * indicate which response headers should be made available to scripts running
     * in the browser, in response to a cross-origin request.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Expose-Headers
     *
     * @var list<string>
     */
    public array $exposedHeaders = [];

    /**
     * Set methods to allow.
     *
     * The Access-Control-Allow-Methods response header specifies one or more
     * methods allowed when accessing a resource in response to a preflight
     * request.
     *
     * E.g.:
     *   - ['GET', 'POST', 'PUT', 'DELETE']
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Allow-Methods
     *
     * @var list<string>
     */
    public array $allowedMethods = [];

    /**
     * Set how many seconds the results of a preflight request can be cached.
     *
     * @see https://developer.mozilla.org/en-US/docs/Web/HTTP/Headers/Access-Control-Max-Age
     */
    public int $maxAge = 7200;
}
