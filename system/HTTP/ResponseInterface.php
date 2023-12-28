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

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\Cookie\CookieStore;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Pager\PagerInterface;
use DateTime;
use InvalidArgumentException;

/**
 * Representation of an outgoing, server-side response.
 * Most of these methods are supplied by ResponseTrait.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 */
interface ResponseInterface extends MessageInterface
{
    /**
     * Constants for status codes.
     * From  https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
     */
    // Informational
    public const HTTP_CONTINUE                        = 100;
    public const HTTP_SWITCHING_PROTOCOLS             = 101;
    public const HTTP_PROCESSING                      = 102;
    public const HTTP_EARLY_HINTS                     = 103;
    public const HTTP_OK                              = 200;
    public const HTTP_CREATED                         = 201;
    public const HTTP_ACCEPTED                        = 202;
    public const HTTP_NONAUTHORITATIVE_INFORMATION    = 203;
    public const HTTP_NO_CONTENT                      = 204;
    public const HTTP_RESET_CONTENT                   = 205;
    public const HTTP_PARTIAL_CONTENT                 = 206;
    public const HTTP_MULTI_STATUS                    = 207;
    public const HTTP_ALREADY_REPORTED                = 208;
    public const HTTP_IM_USED                         = 226;
    public const HTTP_MULTIPLE_CHOICES                = 300;
    public const HTTP_MOVED_PERMANENTLY               = 301;
    public const HTTP_FOUND                           = 302;
    public const HTTP_SEE_OTHER                       = 303;
    public const HTTP_NOT_MODIFIED                    = 304;
    public const HTTP_USE_PROXY                       = 305;
    public const HTTP_SWITCH_PROXY                    = 306;
    public const HTTP_TEMPORARY_REDIRECT              = 307;
    public const HTTP_PERMANENT_REDIRECT              = 308;
    public const HTTP_BAD_REQUEST                     = 400;
    public const HTTP_UNAUTHORIZED                    = 401;
    public const HTTP_PAYMENT_REQUIRED                = 402;
    public const HTTP_FORBIDDEN                       = 403;
    public const HTTP_NOT_FOUND                       = 404;
    public const HTTP_METHOD_NOT_ALLOWED              = 405;
    public const HTTP_NOT_ACCEPTABLE                  = 406;
    public const HTTP_PROXY_AUTHENTICATION_REQUIRED   = 407;
    public const HTTP_REQUEST_TIMEOUT                 = 408;
    public const HTTP_CONFLICT                        = 409;
    public const HTTP_GONE                            = 410;
    public const HTTP_LENGTH_REQUIRED                 = 411;
    public const HTTP_PRECONDITION_FAILED             = 412;
    public const HTTP_PAYLOAD_TOO_LARGE               = 413;
    public const HTTP_URI_TOO_LONG                    = 414;
    public const HTTP_UNSUPPORTED_MEDIA_TYPE          = 415;
    public const HTTP_RANGE_NOT_SATISFIABLE           = 416;
    public const HTTP_EXPECTATION_FAILED              = 417;
    public const HTTP_IM_A_TEAPOT                     = 418;
    public const HTTP_MISDIRECTED_REQUEST             = 421;
    public const HTTP_UNPROCESSABLE_ENTITY            = 422;
    public const HTTP_LOCKED                          = 423;
    public const HTTP_FAILED_DEPENDENCY               = 424;
    public const HTTP_TOO_EARLY                       = 425;
    public const HTTP_UPGRADE_REQUIRED                = 426;
    public const HTTP_PRECONDITION_REQUIRED           = 428;
    public const HTTP_TOO_MANY_REQUESTS               = 429;
    public const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
    public const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS   = 451;
    public const HTTP_CLIENT_CLOSED_REQUEST           = 499;
    public const HTTP_INTERNAL_SERVER_ERROR           = 500;
    public const HTTP_NOT_IMPLEMENTED                 = 501;
    public const HTTP_BAD_GATEWAY                     = 502;
    public const HTTP_SERVICE_UNAVAILABLE             = 503;
    public const HTTP_GATEWAY_TIMEOUT                 = 504;
    public const HTTP_HTTP_VERSION_NOT_SUPPORTED      = 505;
    public const HTTP_VARIANT_ALSO_NEGOTIATES         = 506;
    public const HTTP_INSUFFICIENT_STORAGE            = 507;
    public const HTTP_LOOP_DETECTED                   = 508;
    public const HTTP_NOT_EXTENDED                    = 510;
    public const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
    public const HTTP_NETWORK_CONNECT_TIMEOUT_ERROR   = 599;

    /**
     * Gets the response status code.
     *
     * The status code is a 3-digit integer result code of the server's attempt
     * to understand and satisfy the request.
     *
     * @return int Status code.
     */
    public function getStatusCode(): int;

    /**
     * Return an instance with the specified status code and, optionally, reason phrase.
     *
     * If no reason phrase is specified, will default recommended reason phrase for
     * the response's status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @param int    $code   The 3-digit integer result code to set.
     * @param string $reason The reason phrase to use with the
     *                       provided status code; if none is provided, will
     *                       default to the IANA name.
     *
     * @return $this
     *
     * @throws HTTPException For invalid status code arguments.
     */
    public function setStatusCode(int $code, string $reason = '');

    /**
     * Gets the response phrase associated with the status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @deprecated Use getReasonPhrase()
     */
    public function getReason(): string;

    /**
     * Gets the response reason phrase associated with the status code.
     *
     * Because a reason phrase is not a required element in a response
     * status line, the reason phrase value MAY be null. Implementations MAY
     * choose to return the default RFC 7231 recommended reason phrase (or those
     * listed in the IANA HTTP Status Code Registry) for the response's
     * status code.
     *
     * @see http://tools.ietf.org/html/rfc7231#section-6
     * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
     *
     * @return string Reason phrase; must return an empty string if none present.
     */
    public function getReasonPhrase();

    // --------------------------------------------------------------------
    // Convenience Methods
    // --------------------------------------------------------------------

    /**
     * Sets the date header
     *
     * @return $this
     */
    public function setDate(DateTime $date);

    /**
     * Sets the Last-Modified date header.
     *
     * $date can be either a string representation of the date or,
     * preferably, an instance of DateTime.
     *
     * @param DateTime|string $date
     *
     * @return $this
     */
    public function setLastModified($date);

    /**
     * Set the Link Header
     *
     * @see http://tools.ietf.org/html/rfc5988
     *
     * @return $this
     *
     * @todo Recommend moving to Pager
     */
    public function setLink(PagerInterface $pager);

    /**
     * Sets the Content Type header for this response with the mime type
     * and, optionally, the charset.
     *
     * @return $this
     */
    public function setContentType(string $mime, string $charset = 'UTF-8');

    // --------------------------------------------------------------------
    // Formatter Methods
    // --------------------------------------------------------------------

    /**
     * Converts the $body into JSON and sets the Content Type header.
     *
     * @param array|string $body
     *
     * @return $this
     */
    public function setJSON($body, bool $unencoded = false);

    /**
     * Returns the current body, converted to JSON is it isn't already.
     *
     * @return bool|string|null
     *
     * @throws InvalidArgumentException If the body property is not array.
     */
    public function getJSON();

    /**
     * Converts $body into XML, and sets the correct Content-Type.
     *
     * @param array|string $body
     *
     * @return $this
     */
    public function setXML($body);

    /**
     * Retrieves the current body into XML and returns it.
     *
     * @return bool|string|null
     *
     * @throws InvalidArgumentException If the body property is not array.
     */
    public function getXML();

    // --------------------------------------------------------------------
    // Cache Control Methods
    //
    // http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
    // --------------------------------------------------------------------

    /**
     * Sets the appropriate headers to ensure this response
     * is not cached by the browsers.
     *
     * @return $this
     */
    public function noCache();

    /**
     * A shortcut method that allows the developer to set all of the
     * cache-control headers in one method call.
     *
     * The options array is used to provide the cache-control directives
     * for the header. It might look something like:
     *
     *      $options = [
     *          'max-age'  => 300,
     *          's-maxage' => 900
     *          'etag'     => 'abcde',
     *      ];
     *
     * Typical options are:
     *  - etag
     *  - last-modified
     *  - max-age
     *  - s-maxage
     *  - private
     *  - public
     *  - must-revalidate
     *  - proxy-revalidate
     *  - no-transform
     *
     * @return $this
     */
    public function setCache(array $options = []);

    // --------------------------------------------------------------------
    // Output Methods
    // --------------------------------------------------------------------

    /**
     * Sends the output to the browser.
     *
     * @return $this
     */
    public function send();

    /**
     * Sends the headers of this HTTP request to the browser.
     *
     * @return $this
     */
    public function sendHeaders();

    /**
     * Sends the Body of the message to the browser.
     *
     * @return $this
     */
    public function sendBody();

    // --------------------------------------------------------------------
    // Cookie Methods
    // --------------------------------------------------------------------

    /**
     * Set a cookie
     *
     * Accepts an arbitrary number of binds (up to 7) or an associative
     * array in the first parameter containing all the values.
     *
     * @param array|string $name     Cookie name or array containing binds
     * @param string       $value    Cookie value
     * @param string       $expire   Cookie expiration time in seconds
     * @param string       $domain   Cookie domain (e.g.: '.yourdomain.com')
     * @param string       $path     Cookie path (default: '/')
     * @param string       $prefix   Cookie name prefix
     * @param bool         $secure   Whether to only transfer cookies via SSL
     * @param bool         $httponly Whether only make the cookie accessible via HTTP (no javascript)
     * @param string|null  $samesite
     *
     * @return $this
     */
    public function setCookie(
        $name,
        $value = '',
        $expire = '',
        $domain = '',
        $path = '/',
        $prefix = '',
        $secure = false,
        $httponly = false,
        $samesite = null
    );

    /**
     * Checks to see if the Response has a specified cookie or not.
     */
    public function hasCookie(string $name, ?string $value = null, string $prefix = ''): bool;

    /**
     * Returns the cookie
     *
     * @return Cookie|Cookie[]|null
     */
    public function getCookie(?string $name = null, string $prefix = '');

    /**
     * Sets a cookie to be deleted when the response is sent.
     *
     * @return $this
     */
    public function deleteCookie(string $name = '', string $domain = '', string $path = '/', string $prefix = '');

    /**
     * Returns all cookies currently set.
     *
     * @return Cookie[]
     */
    public function getCookies();

    /**
     * Returns the `CookieStore` instance.
     *
     * @return CookieStore
     */
    public function getCookieStore();

    // --------------------------------------------------------------------
    // Response Methods
    // --------------------------------------------------------------------

    /**
     * Perform a redirect to a new URL, in two flavors: header or location.
     *
     * @param string $uri  The URI to redirect to
     * @param int    $code The type of redirection, defaults to 302
     *
     * @return $this
     *
     * @throws HTTPException For invalid status code.
     */
    public function redirect(string $uri, string $method = 'auto', ?int $code = null);

    /**
     * Force a download.
     *
     * Generates the headers that force a download to happen. And
     * sends the file to the browser.
     *
     * @param string      $filename The path to the file to send
     * @param string|null $data     The data to be downloaded
     * @param bool        $setMime  Whether to try and send the actual MIME type
     *
     * @return DownloadResponse|null
     */
    public function download(string $filename = '', $data = '', bool $setMime = false);

    // --------------------------------------------------------------------
    // CSP Methods
    // --------------------------------------------------------------------

    /**
     * Get Content Security Policy handler.
     */
    public function getCSP(): ContentSecurityPolicy;
}
