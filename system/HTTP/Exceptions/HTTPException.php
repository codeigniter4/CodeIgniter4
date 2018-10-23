<?php
namespace CodeIgniter\HTTP\Exceptions;

use CodeIgniter\Exceptions\ExceptionInterface;
use CodeIgniter\Exceptions\FrameworkException;

class HTTPException extends FrameworkException implements ExceptionInterface
{

	/**
	 * For CurlRequest
	 *
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
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
	 * @return \CodeIgniter\HTTP\Exceptions\HTTPException
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
	 * @param int $code
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
	 * @param int $code
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
	 * @param int $segment
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
	 * @param int $port
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
	public static function forInvalidFile(string $path=null)
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
