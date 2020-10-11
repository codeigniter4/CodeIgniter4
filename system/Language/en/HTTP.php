<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
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

/*
 * HTTP language strings
 *
 * @codeCoverageIgnore
 */

return [
	// CurlRequest
	'missingCurl'     => 'CURL must be enabled to use the CURLRequest class.',
	'invalidSSLKey'   => 'Cannot set SSL Key. {0} is not a valid file.',
	'sslCertNotFound' => 'SSL certificate not found at: {0}',
	'curlError'       => '{0} : {1}',

	// IncomingRequest
	'invalidNegotiationType' => '{0} is not a valid negotiation type. Must be one of: media, charset, encoding, language.',

	// Message
	'invalidHTTPProtocol' => 'Invalid HTTP Protocol Version. Must be one of: {0}',

	// Negotiate
	'emptySupportedNegotiations' => 'You must provide an array of supported values to all Negotiations.',

	// RedirectResponse
	'invalidRoute' => '{0} route cannot be found while reverse-routing.',

	// DownloadResponse
	'cannotSetBinary'        => 'When setting filepath cannot set binary.',
	'cannotSetFilepath'      => 'When setting binary cannot set filepath: {0}',
	'notFoundDownloadSource' => 'Not found download body source.',
	'cannotSetCache'         => 'It does not support caching for downloading.',
	'cannotSetStatusCode'    => 'It does not support change status code for downloading. code: {0}, reason: {1}',

	// Response
	'missingResponseStatus' => 'HTTP Response is missing a status code',
	'invalidStatusCode'     => '{0} is not a valid HTTP return status code',
	'unknownStatusCode'     => 'Unknown HTTP status code provided with no message: {0}',

	// URI
	'cannotParseURI'       => 'Unable to parse URI: {0}',
	'segmentOutOfRange'    => 'Request URI segment is out of range: {0}',
	'invalidPort'          => 'Ports must be between 0 and 65535. Given: {0}',
	'malformedQueryString' => 'Query strings may not include URI fragments.',

	// Page Not Found
	'pageNotFound'       => 'Page Not Found',
	'emptyController'    => 'No Controller specified.',
	'controllerNotFound' => 'Controller or its method is not found: {0}::{1}',
	'methodNotFound'     => 'Controller method is not found: {0}',

	// CSRF
	'disallowedAction' => 'The action you requested is not allowed.',

	// Uploaded file moving
	'alreadyMoved' => 'The uploaded file has already been moved.',
	'invalidFile'  => 'The original file is not a valid file.',
	'moveFailed'   => 'Could not move file {0} to {1} ({2})',

	'uploadErrOk'        => 'The file uploaded with success.',
	'uploadErrIniSize'   => 'The file "%s" exceeds your upload_max_filesize ini directive.',
	'uploadErrFormSize'  => 'The file "%s" exceeds the upload limit defined in your form.',
	'uploadErrPartial'   => 'The file "%s" was only partially uploaded.',
	'uploadErrNoFile'    => 'No file was uploaded.',
	'uploadErrCantWrite' => 'The file "%s" could not be written on disk.',
	'uploadErrNoTmpDir'  => 'File could not be uploaded: missing temporary directory.',
	'uploadErrExtension' => 'File upload was stopped by a PHP extension.',
	'uploadErrUnknown'   => 'The file "%s" was not uploaded due to an unknown error.',

	// SameSite setting
	'invalidSameSiteSetting' => 'The SameSite setting must be None, Lax, Strict, or a blank string. Given: {0}',
];
