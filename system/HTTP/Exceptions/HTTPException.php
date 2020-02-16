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
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

/**
 * Things that can go wrong with HTTP
 */
class HTTPException extends FrameworkException implements ExceptionInterface
{

	/**
	 * For CurlRequest
	 *
	 * @return             \CodeIgniter\HTTP\Exceptions\HTTPException
	 *
	 * Not testable with travis-ci
	 * @codeCoverageIgnore
	 */
	public static function forMissingCurl()
	{
		return new static(lang('HTTP.missingCurl'));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $cert
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forSSLCertNotFound(string $cert)
	{
		return new static(lang('HTTP.sslCertNotFound', [$cert]));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $key
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidSSLKey(string $key)
	{
		return new static(lang('HTTP.invalidSSLKey', [$key]));
	}

	/**
	 * For CurlRequest
	 *
	 * @param string $errorNum
	 * @param string $error
	 *
	 * @return             \CodeIgniter\HTTP\Exceptions\HTTPException
	 *
	 * Not testable with travis-ci; we over-ride the method which triggers it
	 * @codeCoverageIgnore
	 */
	public static function forCurlError(string $errorNum, string $error)
	{
		return new static(lang('HTTP.curlError', [$errorNum, $error]));
	}

	/**
	 * For IncomingRequest
	 *
	 * @param string $type
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidNegotiationType(string $type)
	{
		return new static(lang('HTTP.invalidNegotiationType', [$type]));
	}

	/**
	 * For Message
	 *
	 * @param string $protocols
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidHTTPProtocol(string $protocols)
	{
		return new static(lang('HTTP.invalidHTTPProtocol', [$protocols]));
	}

	/**
	 * For Negotiate
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forEmptySupportedNegotiations()
	{
		return new static(lang('HTTP.emptySupportedNegotiations'));
	}

	/**
	 * For RedirectResponse
	 *
	 * @param string $route
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidRedirectRoute(string $route)
	{
		return new static(lang('HTTP.invalidRoute', [$route]));
	}

	/**
	 * For Response
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forMissingResponseStatus()
	{
		return new static(lang('HTTP.missingResponseStatus'));
	}

	/**
	 * For Response
	 *
	 * @param integer $code
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidStatusCode(int $code)
	{
		return new static(lang('HTTP.invalidStatusCode', [$code]));
	}

	/**
	 * For Response
	 *
	 * @param integer $code
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forUnkownStatusCode(int $code)
	{
		return new static(lang('HTTP.unknownStatusCode', [$code]));
	}

	/**
	 * For URI
	 *
	 * @param string $uri
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forUnableToParseURI(string $uri)
	{
		return new static(lang('HTTP.cannotParseURI', [$uri]));
	}

	/**
	 * For URI
	 *
	 * @param integer $segment
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forURISegmentOutOfRange(int $segment)
	{
		return new static(lang('HTTP.segmentOutOfRange', [$segment]));
	}

	/**
	 * For URI
	 *
	 * @param integer $port
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidPort(int $port)
	{
		return new static(lang('HTTP.invalidPort', [$port]));
	}

	/**
	 * For URI
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forMalformedQueryString()
	{
		return new static(lang('HTTP.malformedQueryString'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forAlreadyMoved()
	{
		return new static(lang('HTTP.alreadyMoved'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @param string|null $path
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forInvalidFile(string $path = null)
	{
		return new static(lang('HTTP.invalidFile'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
	 */
	public static function forMoveFailed(string $source, string $target, string $error)
	{
		return new static(lang('HTTP.moveFailed', [$source, $target, $error]));
	}

}
