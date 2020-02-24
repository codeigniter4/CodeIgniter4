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

namespace CodeIgniter\API;

use Config\Format;
use CodeIgniter\HTTP\Response;

/**
 * Response trait.
 *
 * Provides common, more readable, methods to provide
 * consistent HTTP responses under a variety of common
 * situations when working as an API.
 *
 * @property \CodeIgniter\HTTP\IncomingRequest $request
 * @property \CodeIgniter\HTTP\Response        $response
 *
 * @package CodeIgniter\API
 */
trait ResponseTrait
{

	/**
	 * Allows child classes to override the
	 * status code that is used in their API.
	 *
	 * @var array
	 */
	protected $codes = [
		'created'                   => 201,
		'deleted'                   => 200,
		'no_content'                => 204,
		'invalid_request'           => 400,
		'unsupported_response_type' => 400,
		'invalid_scope'             => 400,
		'temporarily_unavailable'   => 400,
		'invalid_grant'             => 400,
		'invalid_credentials'       => 400,
		'invalid_refresh'           => 400,
		'no_data'                   => 400,
		'invalid_data'              => 400,
		'access_denied'             => 401,
		'unauthorized'              => 401,
		'invalid_client'            => 401,
		'forbidden'                 => 403,
		'resource_not_found'        => 404,
		'not_acceptable'            => 406,
		'resource_exists'           => 409,
		'conflict'                  => 409,
		'resource_gone'             => 410,
		'payload_too_large'         => 413,
		'unsupported_media_type'    => 415,
		'too_many_requests'         => 429,
		'server_error'              => 500,
		'unsupported_grant_type'    => 501,
		'not_implemented'           => 501,
	];

	//--------------------------------------------------------------------

	/**
	 * Provides a single, simple method to return an API response, formatted
	 * to match the requested format, with proper content-type and status code.
	 *
	 * @param array|string|null $data
	 * @param integer           $status
	 * @param string            $message
	 *
	 * @return mixed
	 */
	public function respond($data = null, int $status = null, string $message = '')
	{
		// If data is null and status code not provided, exit and bail
		if ($data === null && $status === null)
		{
			$status = 404;

			// Create the output var here in case of $this->response([]);
			$output = null;
		} // If data is null but status provided, keep the output empty.
		elseif ($data === null && is_numeric($status))
		{
			$output = null;
		}
		else
		{
			$status = empty($status) ? 200 : $status;
			$output = $this->format($data);
		}

		return $this->response->setBody($output)
						->setStatusCode($status, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used for generic failures that no custom methods exist for.
	 *
	 * @param string|array $messages
	 * @param integer|null $status        HTTP status code
	 * @param string|null  $code          Custom, API-specific, error code
	 * @param string       $customMessage
	 *
	 * @return mixed
	 */
	public function fail($messages, int $status = 400, string $code = null, string $customMessage = '')
	{
		if (! is_array($messages))
		{
			$messages = ['error' => $messages];
		}

		$response = [
			'status'   => $status,
			'error'    => $code === null ? $status : $code,
			'messages' => $messages,
		];

		return $this->respond($response, $status, $customMessage);
	}

	//--------------------------------------------------------------------
	//--------------------------------------------------------------------
	// Response Helpers
	//--------------------------------------------------------------------

	/**
	 * Used after successfully creating a new resource.
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondCreated($data = null, string $message = '')
	{
		return $this->respond($data, $this->codes['created'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a resource has been successfully deleted.
	 *
	 * @param mixed  $data    Data.
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondDeleted($data = null, string $message = '')
	{
		return $this->respond($data, $this->codes['deleted'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used after a command has been successfully executed but there is no
	 * meaningful reply to send back to the client.
	 *
	 * @param string $message Message.
	 *
	 * @return mixed
	 */
	public function respondNoContent(string $message = 'No Content')
	{
		return $this->respond(null, $this->codes['no_content'], $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the client is either didn't send authorization information,
	 * or had bad authorization credentials. User is encouraged to try again
	 * with the proper information.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failUnauthorized(string $description = 'Unauthorized', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['unauthorized'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when access is always denied to this resource and no amount
	 * of trying again will help.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failForbidden(string $description = 'Forbidden', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['forbidden'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when a specified resource cannot be found.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failNotFound(string $description = 'Not Found', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_not_found'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the data provided by the client cannot be validated.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failValidationError(string $description = 'Bad Request', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['invalid_data'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when trying to create a new resource and it already exists.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failResourceExists(string $description = 'Conflict', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_exists'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Use when a resource was previously deleted. This is different than
	 * Not Found, because here we know the data previously existed, but is now gone,
	 * where Not Found means we simply cannot find any information about it.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failResourceGone(string $description = 'Gone', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['resource_gone'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when the user has made too many requests for the resource recently.
	 *
	 * @param string $description
	 * @param string $code
	 * @param string $message
	 *
	 * @return mixed
	 */
	public function failTooManyRequests(string $description = 'Too Many Requests', string $code = null, string $message = '')
	{
		return $this->fail($description, $this->codes['too_many_requests'], $code, $message);
	}

	//--------------------------------------------------------------------

	/**
	 * Used when there is a server error.
	 *
	 * @param string      $description The error message to show the user.
	 * @param string|null $code        A custom, API-specific, error code.
	 * @param string      $message     A custom "reason" message to return.
	 *
	 * @return Response The value of the Response's send() method.
	 */
	public function failServerError(string $description = 'Internal Server Error', string $code = null, string $message = ''): Response
	{
		return $this->fail($description, $this->codes['server_error'], $code, $message);
	}

	//--------------------------------------------------------------------
	// Utility Methods
	//--------------------------------------------------------------------

	/**
	 * Handles formatting a response. Currently makes some heavy assumptions
	 * and needs updating! :)
	 *
	 * @param string|array|null $data
	 *
	 * @return string|null
	 */
	protected function format($data = null)
	{
		// If the data is a string, there's not much we can do to it...
		if (is_string($data))
		{
			// The content type should be text/... and not application/...
			$contentType = $this->response->getHeaderLine('Content-Type');
			$contentType = str_replace('application/json', 'text/html', $contentType);
			$contentType = str_replace('application/', 'text/', $contentType);
			$this->response->setContentType($contentType);

			return $data;
		}

		// Determine correct response type through content negotiation
		$config = new Format();
		$format = $this->request->negotiate('media', $config->supportedResponseFormats, false);

		$this->response->setContentType($format);

		// if we don't have a formatter, make one
		if (! isset($this->formatter))
		{
			// if no formatter, use the default
			$this->formatter = $config->getFormatter($format);
		}

		if ($format !== 'application/json')
		{
			// Recursively convert objects into associative arrays
			// Conversion not required for JSONFormatter
			$data = json_decode(json_encode($data), true);
		}

		return $this->formatter->format($data);
	}

}
