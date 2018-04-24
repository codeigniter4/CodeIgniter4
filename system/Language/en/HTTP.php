<?php

/**
 * CodeIgniter Core Language file.
 *
 * @package      CodeIgniter
 * @author       CodeIgniter Dev Team
 * @copyright    2014-2018 British Columbia Institute of Technology (https://bcit.ca/)
 * @license      https://opensource.org/licenses/MIT	MIT License
 * @link         https://codeigniter.com
 * @since        Version 4.0.0
 * @filesource
 */
return [
	// CurlRequest
	'missingCurl' => 'CURL must be enabled to use the CURLRequest class.',
	'invalidSSLKey' => 'Cannot set SSL Key. {0, string} is not a valid file.',
	'sslCertNotFound' => 'SSL certificate not found at: {0, string}',
	'curlError' => '{0, string} : {1, string}',

	// IncomingRequest
	'invalidNegotiationType' => '{0, string} is not a valid negotiation type. Must be one of: media, charset, encoding, language.',

	// Message
	'invalidHTTPProtocol' => 'Invalid HTTP Protocol Version. Must be one of: {0, string}',

	// Negotiate
	'emptySupportedNegotiations' => 'You must provide an array of supported values to all Negotiations.',

	// RedirectResponse
	'invalidRoute' => '{0, string} is not a valid route.',

	// Response
	'missingResponseStatus' => 'HTTP Response is missing a status code',
	'invalidStatusCode' => '{0, string} is not a valid HTTP return status code',
	'unknownStatusCode' => 'Unknown HTTP status code provided with no message: {0, string}',

	// URI
	'cannotParseURI' => 'Unable to parse URI: {0, string}',
	'segmentOutOfRange' => 'Request URI segment is our of range: {0, string}',
	'invalidPort' => 'Ports must be between 0 and 65535. Given: {0, string}',
	'malformedQueryString' => 'Query strings may not include URI fragments.',

	// Page Not Found
	'pageNotFound' => 'Page Not Found',
	'emptyController' => 'No Controller specified.',
	'controllerNotFound' => 'Controller or its method is not found: {0, string}::{1, string}',
	'methodNotFound' => 'Controller method is not found: {0, string}',
];
