<?php namespace CodeIgniter\API;

/**
 * Class ResponseTrait
 *
 * Provides common, more readable, methods to provide
 * consistent HTTP responses under a variety of common
 * situations when working as an API.
 *
 * @property $request   CodeIgniter\HTTP\Request
 * @property $response  CodeIgniter\HTTP\Response
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
     * @param null   $data
     * @param int    $status
     * @param string $message
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
        } else
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
     * @param             $messages
     * @param int|null    $status HTTP status code
     * @param string|null $code   Custom, API-specific, error code
     * @param string      $customMessage
     *
     * @return mixed
     */
    public function fail($messages, int $status = 400, string $code = null, string $customMessage = '')
    {
        if (! is_array($messages))
        {
            $messages = [$messages];
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
     * @param        $data
     * @param string $message
     *
     * @return mixed
     */
    public function respondCreated($data, string $message = '')
    {
        return $this->respond($data, $this->codes['created'], $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used after a resource has been successfully deleted.
     *
     * @param        $data
     * @param string $message
     *
     * @return mixed
     */
    public function respondDeleted($data, string $message = '')
    {
        return $this->respond($data, $this->codes['deleted'], $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used when the client is either didn't send authorization information,
     * or had bad authorization credentials. User is encouraged to try again
     * with the proper information.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failUnauthorized(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['unauthorized'], $code, $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used when access is always denied to this resource and no amount
     * of trying again will help.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failForbidden(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['forbidden'], $code, $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used when a specified resource cannot be found.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failNotFound(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['resource_not_found'], $code, $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used when the data provided by the client cannot be validated.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failValidationError(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['invalid_data'], $code, $message);
    }

    //--------------------------------------------------------------------

    /**
     * Use when trying to create a new resource and it already exists.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failResourceExists(string $description, string $code=null, string $message = '')
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
     * @param string $message
     *
     * @return mixed
     */
    public function failResourceGone(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['resource_gone'], $code, $message);
    }

    //--------------------------------------------------------------------

    /**
     * Used when the user has made too many requests for the resource recently.
     *
     * @param string $description
     * @param string $message
     *
     * @return mixed
     */
    public function failTooManyRequests(string $description, string $code=null, string $message = '')
    {
        return $this->fail($description, $this->codes['too_many_requests'], $code, $message);
    }

    //--------------------------------------------------------------------


    //--------------------------------------------------------------------
    // Utility Methods
    //--------------------------------------------------------------------

    /**
     * Handles formatting a response. Currently makes some heavy assumptions
     * and needs updating! :)
     *
     * @param null $data
     *
     * @return null|string
     */
    protected function format($data = null)
    {
        // If the data is a string, there's not much we can do to it...
        if (is_string($data))
        {
            $this->setContentType('text/html');

            return $data;
        }

        $config = new \Config\API();

        // Determine correct response type through content negotiation
        $format = $this->request->negotiate('media', $config->supportedResponseFormats);

        $this->setContentType($format);

        $formatter = $config->getFormatter($format);

        return $formatter->format($data);
    }

    //--------------------------------------------------------------------

    /**
     * Sets the response's content type. If a type is permitted
     * ('html', 'json', or 'xml'), the appropriate content type is set.
     *
     * @param string $type
     */
    protected function setContentType(string $type = null)
    {
        switch ($type)
        {
            case 'text/html':
                $this->response = $this->response->setContentType('text/html');
                break;
            case 'application/json':
                $this->response = $this->response->setContentType('application/json');
                break;
            case 'application/xml':
                $this->response = $this->response->setContentType('text/xml');
                break;
        }
    }
}
