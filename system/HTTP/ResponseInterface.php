<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2014-2019 British Columbia Institute of Technology (https://bcit.ca/)
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 3.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP;

/**
 * Representation of an outgoing, getServer-side response.
 *
 * Per the HTTP specification, this interface includes properties for
 * each of the following:
 *
 * - Protocol version
 * - Status code and reason phrase
 * - Headers
 * - Message body
 *
 * @package CodeIgniter\HTTP
 * @mixin   \CodeIgniter\HTTP\RedirectResponse
 */
interface ResponseInterface
{

	/**
	 * Constants for status codes.
	 * From  https://en.wikipedia.org/wiki/List_of_HTTP_status_codes
	 */
	// Informational
	const HTTP_CONTINUE            = 100;
	const HTTP_SWITCHING_PROTOCOLS = 101;
	const HTTP_PROCESSING          = 102;
	const HTTP_EARLY_HINTS         = 103;
	// Success
	const HTTP_OK                           = 200;
	const HTTP_CREATED                      = 201;
	const HTTP_ACCEPTED                     = 202;
	const HTTP_NONAUTHORITATIVE_INFORMATION = 203;
	const HTTP_NO_CONTENT                   = 204;
	const HTTP_RESET_CONTENT                = 205;
	const HTTP_PARTIAL_CONTENT              = 206;
	const HTTP_MULTI_STATUS                 = 207;
	const HTTP_ALREADY_REPORTED             = 208;
	const HTTP_IM_USED                      = 226;
	// Redirection
	const HTTP_MULTIPLE_CHOICES   = 300;
	const HTTP_MOVED_PERMANENTLY  = 301;
	const HTTP_FOUND              = 302;
	const HTTP_SEE_OTHER          = 303;
	const HTTP_NOT_MODIFIED       = 304;
	const HTTP_USE_PROXY          = 305;
	const HTTP_SWITCH_PROXY       = 306;
	const HTTP_TEMPORARY_REDIRECT = 307;
	const HTTP_PERMANENT_REDIRECT = 308;
	// Client Error
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
	// Server Error
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
	 * @throws \InvalidArgumentException For invalid status code arguments.
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
	 */
	public function getReason(): string;

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Convenience Methods
	//--------------------------------------------------------------------

	/**
	 * Sets the date header
	 *
	 * @param \DateTime $date
	 *
	 * @return ResponseInterface
	 */
	public function setDate(\DateTime $date);

	//--------------------------------------------------------------------

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

	//--------------------------------------------------------------------

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

	/**
	 * Sets the Last-Modified date header.
	 *
	 * $date can be either a string representation of the date or,
	 * preferably, an instance of DateTime.
	 *
	 * @param string|\DateTime $date
	 */
	public function setLastModified($date);

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Output Methods
	//--------------------------------------------------------------------

	/**
	 * Sends the output to the browser.
	 *
	 * @return ResponseInterface
	 */
	public function send();

	//--------------------------------------------------------------------
}
