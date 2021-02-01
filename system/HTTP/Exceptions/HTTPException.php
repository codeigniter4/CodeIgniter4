<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\FrameworkException;

/**
 * Things that can go wrong with HTTP
 */
class HTTPException extends FrameworkException
{
	/**
	 * For CurlRequest
	 *
	 * @return HTTPException
	 *
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
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
	 */
	public static function forInvalidHTTPProtocol(string $protocols)
	{
		return new static(lang('HTTP.invalidHTTPProtocol', [$protocols]));
	}

	/**
	 * For Negotiate
	 *
	 * @return HTTPException
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
	 * @return HTTPException
	 */
	public static function forInvalidRedirectRoute(string $route)
	{
		return new static(lang('HTTP.invalidRoute', [$route]));
	}

	/**
	 * For Response
	 *
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
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
	 * @return HTTPException
	 */
	public static function forInvalidPort(int $port)
	{
		return new static(lang('HTTP.invalidPort', [$port]));
	}

	/**
	 * For URI
	 *
	 * @return HTTPException
	 */
	public static function forMalformedQueryString()
	{
		return new static(lang('HTTP.malformedQueryString'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @return HTTPException
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
	 * @return HTTPException
	 */
	public static function forInvalidFile(string $path = null)
	{
		return new static(lang('HTTP.invalidFile'));
	}

	/**
	 * For Uploaded file move
	 *
	 * @param string $source
	 * @param string $target
	 * @param string $error
	 *
	 * @return HTTPException
	 */
	public static function forMoveFailed(string $source, string $target, string $error)
	{
		return new static(lang('HTTP.moveFailed', [$source, $target, $error]));
	}

	/**
	 * For Invalid SameSite attribute setting
	 *
	 * @param string $samesite
	 *
	 * @return HTTPException
	 */
	public static function forInvalidSameSiteSetting(string $samesite)
	{
		return new static(lang('Security.invalidSameSiteSetting', [$samesite]));
	}
}
