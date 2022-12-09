<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\API;

use CodeIgniter\Format\FormatterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Services;

/**
 * Provides common, more readable, methods to provide
 * consistent HTTP responses under a variety of common
 * situations when working as an API.
 *
 * @property IncomingRequest $request
 * @property Response        $response
 */
trait ResponseTrait
{
    /**
     * Allows child classes to override the
     * status code that is used in their API.
     *
     * @var array<string, int>
     */
    protected $codes = [
        'created'                   => 201,
        'deleted'                   => 200,
        'updated'                   => 200,
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

    /**
     * How to format the response data.
     * Either 'json' or 'xml'. If blank will be
     * determined through content negotiation.
     *
     * @var string
     */
    protected $format = 'json';

    /**
     * Current Formatter instance. This is usually set by ResponseTrait::format
     *
     * @var FormatterInterface|null
     */
    protected $formatter;

    /**
     * Provides a single, simple method to return an API response, formatted
     * to match the requested format, with proper content-type and status code.
     *
     * @param array|string|null $data
     *
     * @return Response
     */
    protected function respond($data = null, ?int $status = null, string $message = '')
    {
        if ($data === null && $status === null) {
            $status = 404;
            $output = null;
        } elseif ($data === null && is_numeric($status)) {
            $output = null;
        } else {
            $status = empty($status) ? 200 : $status;
            $output = $this->format($data);
        }

        if ($output !== null) {
            if ($this->format === 'json') {
                return $this->response->setJSON($output)->setStatusCode($status, $message);
            }

            if ($this->format === 'xml') {
                return $this->response->setXML($output)->setStatusCode($status, $message);
            }
        }

        return $this->response->setBody($output)->setStatusCode($status, $message);
    }

    /**
     * Used for generic failures that no custom methods exist for.
     *
     * @param array|string $messages
     * @param int          $status   HTTP status code
     * @param string|null  $code     Custom, API-specific, error code
     *
     * @return Response
     */
    protected function fail($messages, int $status = 400, ?string $code = null, string $customMessage = '')
    {
        if (! is_array($messages)) {
            $messages = ['error' => $messages];
        }

        $response = [
            'status'   => $status,
            'error'    => $code ?? $status,
            'messages' => $messages,
        ];

        return $this->respond($response, $status, $customMessage);
    }

    // --------------------------------------------------------------------
    // Response Helpers
    // --------------------------------------------------------------------

    /**
     * Used after successfully creating a new resource.
     *
     * @param array|string|null $data
     *
     * @return Response
     */
    protected function respondCreated($data = null, string $message = '')
    {
        return $this->respond($data, $this->codes['created'], $message);
    }

    /**
     * Used after a resource has been successfully deleted.
     *
     * @param array|string|null $data
     *
     * @return Response
     */
    protected function respondDeleted($data = null, string $message = '')
    {
        return $this->respond($data, $this->codes['deleted'], $message);
    }

    /**
     * Used after a resource has been successfully updated.
     *
     * @param array|string|null $data
     *
     * @return Response
     */
    protected function respondUpdated($data = null, string $message = '')
    {
        return $this->respond($data, $this->codes['updated'], $message);
    }

    /**
     * Used after a command has been successfully executed but there is no
     * meaningful reply to send back to the client.
     *
     * @return Response
     */
    protected function respondNoContent(string $message = 'No Content')
    {
        return $this->respond(null, $this->codes['no_content'], $message);
    }

    /**
     * Used when the client is either didn't send authorization information,
     * or had bad authorization credentials. User is encouraged to try again
     * with the proper information.
     *
     * @return Response
     */
    protected function failUnauthorized(string $description = 'Unauthorized', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['unauthorized'], $code, $message);
    }

    /**
     * Used when access is always denied to this resource and no amount
     * of trying again will help.
     *
     * @return Response
     */
    protected function failForbidden(string $description = 'Forbidden', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['forbidden'], $code, $message);
    }

    /**
     * Used when a specified resource cannot be found.
     *
     * @return Response
     */
    protected function failNotFound(string $description = 'Not Found', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['resource_not_found'], $code, $message);
    }

    /**
     * Used when the data provided by the client cannot be validated.
     *
     * @return Response
     *
     * @deprecated Use failValidationErrors instead
     */
    protected function failValidationError(string $description = 'Bad Request', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['invalid_data'], $code, $message);
    }

    /**
     * Used when the data provided by the client cannot be validated on one or more fields.
     *
     * @param string|string[] $errors
     *
     * @return Response
     */
    protected function failValidationErrors($errors, ?string $code = null, string $message = '')
    {
        return $this->fail($errors, $this->codes['invalid_data'], $code, $message);
    }

    /**
     * Use when trying to create a new resource and it already exists.
     *
     * @return Response
     */
    protected function failResourceExists(string $description = 'Conflict', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['resource_exists'], $code, $message);
    }

    /**
     * Use when a resource was previously deleted. This is different than
     * Not Found, because here we know the data previously existed, but is now gone,
     * where Not Found means we simply cannot find any information about it.
     *
     * @return Response
     */
    protected function failResourceGone(string $description = 'Gone', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['resource_gone'], $code, $message);
    }

    /**
     * Used when the user has made too many requests for the resource recently.
     *
     * @return Response
     */
    protected function failTooManyRequests(string $description = 'Too Many Requests', ?string $code = null, string $message = '')
    {
        return $this->fail($description, $this->codes['too_many_requests'], $code, $message);
    }

    /**
     * Used when there is a server error.
     *
     * @param string      $description The error message to show the user.
     * @param string|null $code        A custom, API-specific, error code.
     * @param string      $message     A custom "reason" message to return.
     *
     * @return Response The value of the Response's send() method.
     */
    protected function failServerError(string $description = 'Internal Server Error', ?string $code = null, string $message = ''): Response
    {
        return $this->fail($description, $this->codes['server_error'], $code, $message);
    }

    // --------------------------------------------------------------------
    // Utility Methods
    // --------------------------------------------------------------------

    /**
     * Handles formatting a response. Currently makes some heavy assumptions
     * and needs updating! :)
     *
     * @param array|string|null $data
     *
     * @return string|null
     */
    protected function format($data = null)
    {
        // If the data is a string, there's not much we can do to it...
        if (is_string($data)) {
            // The content type should be text/... and not application/...
            $contentType = $this->response->getHeaderLine('Content-Type');
            $contentType = str_replace('application/json', 'text/html', $contentType);
            $contentType = str_replace('application/', 'text/', $contentType);
            $this->response->setContentType($contentType);
            $this->format = 'html';

            return $data;
        }

        $format = Services::format();
        $mime   = "application/{$this->format}";

        // Determine correct response type through content negotiation if not explicitly declared
        if (
            (empty($this->format) || ! in_array($this->format, ['json', 'xml'], true))
            && $this->request instanceof IncomingRequest
        ) {
            $mime = $this->request->negotiate(
                'media',
                $format->getConfig()->supportedResponseFormats,
                false
            );
        }

        $this->response->setContentType($mime);

        // if we don't have a formatter, make one
        if (! isset($this->formatter)) {
            // if no formatter, use the default
            $this->formatter = $format->getFormatter($mime);
        }

        if ($mime !== 'application/json') {
            // Recursively convert objects into associative arrays
            // Conversion not required for JSONFormatter
            $data = json_decode(json_encode($data), true);
        }

        return $this->formatter->format($data);
    }

    /**
     * Sets the format the response should be in.
     *
     * @return $this
     */
    protected function setResponseFormat(?string $format = null)
    {
        $this->format = strtolower($format);

        return $this;
    }
}
