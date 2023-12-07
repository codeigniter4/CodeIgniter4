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
     * @return HTTPException
     */
    public static function forSSLCertNotFound(string $cert)
    {
        return new static(lang('HTTP.sslCertNotFound', [$cert]));
    }

    /**
     * For CurlRequest
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
     * @return HTTPException
     *
     * @codeCoverageIgnore
     */
    public static function forCurlError(string $errorNum, string $error)
    {
        return new static(lang('HTTP.curlError', [$errorNum, $error]));
    }

    /**
     * For IncomingRequest
     *
     * @return HTTPException
     */
    public static function forInvalidNegotiationType(string $type)
    {
        return new static(lang('HTTP.invalidNegotiationType', [$type]));
    }

    /**
     * Thrown in IncomingRequest when the json_decode() produces
     *  an error code other than JSON_ERROR_NONE.
     *
     * @param string $error The error message
     *
     * @return static
     */
    public static function forInvalidJSON(?string $error = null)
    {
        return new static(lang('HTTP.invalidJSON', [$error]));
    }

    /**
     * For Message
     *
     * @return HTTPException
     */
    public static function forInvalidHTTPProtocol(string $invalidVersion)
    {
        return new static(lang('HTTP.invalidHTTPProtocol', [$invalidVersion]));
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
     * @return HTTPException
     */
    public static function forInvalidStatusCode(int $code)
    {
        return new static(lang('HTTP.invalidStatusCode', [$code]));
    }

    /**
     * For Response
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
     * @return HTTPException
     */
    public static function forUnableToParseURI(string $uri)
    {
        return new static(lang('HTTP.cannotParseURI', [$uri]));
    }

    /**
     * For URI
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
     * @return HTTPException
     */
    public static function forInvalidFile(?string $path = null)
    {
        return new static(lang('HTTP.invalidFile'));
    }

    /**
     * For Uploaded file move
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
     * @return HTTPException
     *
     * @deprecated Use `CookieException::forInvalidSameSite()` instead.
     *
     * @codeCoverageIgnore
     */
    public static function forInvalidSameSiteSetting(string $samesite)
    {
        return new static(lang('Security.invalidSameSiteSetting', [$samesite]));
    }

    /**
     * Thrown when the JSON format is not supported.
     * This is specifically for cases where data validation is expected to work with key-value structures.
     *
     * @return HTTPException
     */
    public static function forUnsupportedJSONFormat()
    {
        return new static(lang('HTTP.unsupportedJSONFormat'));
    }
}
