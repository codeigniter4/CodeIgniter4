<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP;

use CodeIgniter\Cookie\Cookie;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Pager\PagerInterface;
use DateTime;
use InvalidArgumentException;

/**
 * Representation of an outgoing, getServer-side response.
 * Most of these methods are supplied by ResponseTrait.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * @mixin RedirectResponse
 */
interface ResponseInterface
{
	/**
	 * Constants for status codes.
	 * From  https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 */
	// Informational
	const HTTP_CONTINUE                        = 100;
	const HTTP_SWITCHING_PROTOCOLS             = 101;
	const HTTP_PROCESSING                      = 102;
	const HTTP_EARLY_HINTS                     = 103;
	const HTTP_OK                              = 200;
	const HTTP_CREATED                         = 201;
	const HTTP_ACCEPTED                        = 202;
	const HTTP_NONAUTHORITATIVE_INFORMATION    = 203;
	const HTTP_NO_CONTENT                      = 204;
	const HTTP_RESET_CONTENT                   = 205;
	const HTTP_PARTIAL_CONTENT                 = 206;
	const HTTP_MULTI_STATUS                    = 207;
	const HTTP_ALREADY_REPORTED                = 208;
	const HTTP_IM_USED                         = 226;
	const HTTP_MULTIPLE_CHOICES                = 300;
	const HTTP_MOVED_PERMANENTLY               = 301;
	const HTTP_FOUND                           = 302;
	const HTTP_SEE_OTHER                       = 303;
	const HTTP_NOT_MODIFIED                    = 304;
	const HTTP_USE_PROXY                       = 305;
	const HTTP_SWITCH_PROXY                    = 306;
	const HTTP_TEMPORARY_REDIRECT              = 307;
	const HTTP_PERMANENT_REDIRECT              = 308;
	const HTTP_BAD_REQUEST                     = 400;
	const HTTP_UNAUTHORIZED                    = 401;
	const HTTP_PAYMENT_REQUIRED                = 402;
	const HTTP_FORBIDDEN                       = 403;
	const HTTP_NOT_FOUND                       = 404;
	const HTTP_METHOD_NOT_ALLOWED              = 405;
	const HTTP_NOT_ACCEPTABLE                  = 406;
	const HTTP_PROXY_AUTHENTICATION_REQUIRED   = 407;
	const HTTP_REQUEST_TIMEOUT                 = 408;
	const HTTP_CONFLICT                        = 409;
	const HTTP_GONE                            = 410;
	const HTTP_LENGTH_REQUIRED                 = 411;
	const HTTP_PRECONDITION_FAILED             = 412;
	const HTTP_PAYLOAD_TOO_LARGE               = 413;
	const HTTP_URI_TOO_LONG                    = 414;
	const HTTP_UNSUPPORTED_MEDIA_TYPE          = 415;
	const HTTP_RANGE_NOT_SATISFIABLE           = 416;
	const HTTP_EXPECTATION_FAILED              = 417;
	const HTTP_IM_A_TEAPOT                     = 418;
	const HTTP_MISDIRECTED_REQUEST             = 421;
	const HTTP_UNPROCESSABLE_ENTITY            = 422;
	const HTTP_LOCKED                          = 423;
	const HTTP_FAILED_DEPENDENCY               = 424;
	const HTTP_TOO_EARLY                       = 425;
	const HTTP_UPGRADE_REQUIRED                = 426;
	const HTTP_PRECONDITION_REQUIRED           = 428;
	const HTTP_TOO_MANY_REQUESTS               = 429;
	const HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;
	const HTTP_UNAVAILABLE_FOR_LEGAL_REASONS   = 451;
	const HTTP_CLIENT_CLOSED_REQUEST           = 499;
	const HTTP_INTERNAL_SERVER_ERROR           = 500;
	const HTTP_NOT_IMPLEMENTED                 = 501;
	const HTTP_BAD_GATEWAY                     = 502;
	const HTTP_SERVICE_UNAVAILABLE             = 503;
	const HTTP_GATEWAY_TIMEOUT                 = 504;
	const HTTP_HTTP_VERSION_NOT_SUPPORTED      = 505;
	const HTTP_VARIANT_ALSO_NEGOTIATES         = 506;
	const HTTP_INSUFFICIENT_STORAGE            = 507;
	const HTTP_LOOP_DETECTED                   = 508;
	const HTTP_NOT_EXTENDED                    = 510;
	const HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;
	const HTTP_NETWORK_CONNECT_TIMEOUT_ERROR   = 599;

	/**
	 * Gets the response status code.
	 *
	 * The status code is a 3-digit integer result code of the getServer's attempt
	 * to understand and satisfy the request.
	 *
	 * @return integer Status code.
	 *
	 * @deprecated To be replaced by the PSR-7 version (compatible)
	 */
	public function getStatusCode(): int;

	//--------------------------------------------------------------------

	/**
	 * Return an instance with the specified status code and, optionally, reason phrase.
	 *
	 * If no reason phrase is specified, will default recommended reason phrase for
	 * the response's status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @param integer $code   The 3-digit integer result code to set.
	 * @param string  $reason The reason phrase to use with the
	 *                        provided status code; if none is provided, will
	 *                        default to the IANA name.
	 *
	 * @return self
	 * @throws InvalidArgumentException For invalid status code arguments.
	 */
	public function setStatusCode(int $code, string $reason = '');

	//--------------------------------------------------------------------

	/**
	 * Gets the response response phrase associated with the status code.
	 *
	 * @see http://tools.ietf.org/html/rfc7231#section-6
	 * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
	 *
	 * @return string
	 *
	 * @deprecated Use getReasonPhrase()
	 */
	public function getReason(): string;

	//--------------------------------------------------------------------
	// Convenience Methods
	//--------------------------------------------------------------------

	/**
	 * Sets the date header
	 *
	 * @param DateTime $date
	 *
	 * @return ResponseInterface
	 */
	public function setDate(DateTime $date);

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param string|DateTime $date
	 */
	public function setLastModified($date);

	/**
	 * Set the Link Header
	 *
	 * @param PagerInterface $pager
	 *
	 * @see http://tools.ietf.org/html/rfc5988
	 *
	 * @return Response
	 *
	 * @todo Recommend moving to Pager
	 */
	public function setLink(PagerInterface $pager);

	/**
	 * Sets the Content Type header for this response with the mime type
	 * and, optionally, the charset.
	 *
	 * @param string $mime
	 * @param string $charset
	 *
	 * @return ResponseInterface
	 */
	public function setContentType(string $mime, string $charset = 'UTF-8');

	//--------------------------------------------------------------------
	// Formatter Methods
	//--------------------------------------------------------------------

	/**
	 * Converts the $body into JSON and sets the Content Type header.
	 *
	 * @param array|string $body
	 * @param boolean      $unencoded
	 *
	 * @return $this
	 */
	public function setJSON($body, bool $unencoded = false);

	/**
	 * Returns the current body, converted to JSON is it isn't already.
	 *
	 * @return mixed|string
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
	 * @return mixed|string
	 * @throws InvalidArgumentException If the body property is not array.
	 */
	public function getXML();

	//--------------------------------------------------------------------
	// Cache Control Methods
	//
	// http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.9
	//--------------------------------------------------------------------

	/**
	 * Sets the appropriate headers to ensure this response
	 * is not cached by the browsers.
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
	 * @param array $options
	 *
	 * @return ResponseInterface
	 */
	public function setCache(array $options = []);

	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * Sends the output to the browser.
	 *
	 * @return ResponseInterface
	 */
	public function send();

	/**
	 * Sends the headers of this HTTP request to the browser.
	 *
	 * @return Response
	 */
	public function sendHeaders();

	/**
	 * Sends the Body of the message to the browser.
	 *
	 * @return Response
	 */
	public function sendBody();

	//--------------------------------------------------------------------
	// Cookie Methods
	//--------------------------------------------------------------------

	/**
	 * Set a cookie
	 *
	 * Accepts an arbitrary number of binds (up to 7) or an associative
	 * array in the first parameter containing all the values.
	 *
	 * @param string|array $name     Cookie name or array containing binds
	 * @param string       $value    Cookie value
	 * @param string       $expire   Cookie expiration time in seconds
	 * @param string       $domain   Cookie domain (e.g.: '.yourdomain.com')
	 * @param string       $path     Cookie path (default: '/')
	 * @param string       $prefix   Cookie name prefix
	 * @param boolean      $secure   Whether to only transfer cookies via SSL
	 * @param boolean      $httponly Whether only make the cookie accessible via HTTP (no javascript)
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
	 *
	 * @param string      $name
	 * @param string|null $value
	 * @param string      $prefix
	 *
	 * @return boolean
	 */
	public function hasCookie(string $name, string $value = null, string $prefix = ''): bool;

	/**
	 * Returns the cookie
	 *
	 * @param string|null $name
	 * @param string      $prefix
	 *
	 * @return Cookie[]|Cookie|null
	 */
	public function getCookie(string $name = null, string $prefix = '');

	/**
	 * Sets a cookie to be deleted when the response is sent.
	 *
	 * @param string $name
	 * @param string $domain
	 * @param string $path
	 * @param string $prefix
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

	//--------------------------------------------------------------------
	// Response Methods
	//--------------------------------------------------------------------

	/**
	 * Perform a redirect to a new URL, in two flavors: header or location.
	 *
	 * @param string  $uri    The URI to redirect to
	 * @param string  $method
	 * @param integer $code   The type of redirection, defaults to 302
	 *
	 * @return $this
	 * @throws HTTPException For invalid status code.
	 */
	public function redirect(string $uri, string $method = 'auto', int $code = null);

	/**
	 * Force a download.
	 *
	 * Generates the headers that force a download to happen. And
	 * sends the file to the browser.
	 *
	 * @param string      $filename The path to the file to send
	 * @param string|null $data     The data to be downloaded
	 * @param boolean     $setMime  Whether to try and send the actual MIME type
	 *
	 * @return DownloadResponse|null
	 */
	public function download(string $filename = '', $data = '', bool $setMime = false);
}
