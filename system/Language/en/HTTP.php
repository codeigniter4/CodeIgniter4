<?php

/**
 * This file is part of the CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

// HTTP language settings
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
    // @deprecated use `Security.disallowedAction`
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
    // @deprecated
    'invalidSameSiteSetting' => 'The SameSite setting must be None, Lax, Strict, or a blank string. Given: {0}',
];
