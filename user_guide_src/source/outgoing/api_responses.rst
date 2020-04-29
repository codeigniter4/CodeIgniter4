##################
API Response Trait
##################

Much of modern PHP development requires building API's, whether simply to provide data for a javascript-heavy
single page application, or as a standalone product. CodeIgniter provides an API Response trait that can be
used with any controller to make common response types simple, with no need to remember which HTTP status code
should be returned for which response types.

.. contents::
    :local:
    :depth: 2

*************
Example Usage
*************

The following example shows a common usage pattern within your controllers.

::

    <?php namespace App\Controllers;

    use CodeIgniter\API\ResponseTrait;

    class Users extends \CodeIgniter\Controller
    {
        use ResponseTrait;

        public function createUser()
        {
            $model = new UserModel();
            $user  = $model->save($this->request->getPost());

            // Respond with 201 status code
            return $this->respondCreated();
        }
    }

In this example, an HTTP status code of 201 is returned, with the generic status message, 'Created'. Methods
exist for the most common use cases::

    // Generic response method
    respond($data, 200);
    // Generic failure response
    fail($errors, 400);
    // Item created response
    respondCreated($data);
    // Item successfully deleted
    respondDeleted($data);
    // Command executed by no response required
    respondNoContent($message);
    // Client isn't authorized
    failUnauthorized($description);
    // Forbidden action
    failForbidden($description);
    // Resource Not Found
    failNotFound($description);
    // Data did not validate
    failValidationError($description);
    // Resource already exists
    failResourceExists($description);
    // Resource previously deleted
    failResourceGone($description);
    // Client made too many requests
    failTooManyRequests($description);

***********************
Handling Response Types
***********************

When you pass your data in any of these methods, they will determine the data type to format the results as based on
the following criteria:

* If $data is a string, it will be treated as HTML to send back to the client.
* If $data is an array, it will be formatted according to the controller's ``$this->format`` value. If that is empty
    it will try to negotiate the content type with what the client asked for, defaulting to JSON
    if nothing else has been specified within Config\API.php, the ``$supportedResponseFormats`` property.

To define the formatter that is used, edit **Config/Format.php**. The ``$supportedResponseFormats`` contains a list of
mime types that your application can automatically format the response for. By default, the system knows how to
format both XML and JSON responses::

        public $supportedResponseFormats = [
            'application/json',
            'application/xml'
        ];

This is the array that is used during :doc:`Content Negotiation </incoming/content_negotiation>` to determine which
type of response to return. If no matches are found between what the client requested and what you support, the first
format in this array is what will be returned.

Next, you need to define the class that is used to format the array of data. This must be a fully qualified class
name, and the class must implement **CodeIgniter\\Format\\FormatterInterface**. Formatters come out of the box that
support both JSON and XML::

    public $formatters = [
        'application/json' => \CodeIgniter\Format\JSONFormatter::class,
        'application/xml'  => \CodeIgniter\Format\XMLFormatter::class
    ];

So, if your request asks for JSON formatted data in an **Accept** header, the data array you pass any of the
``respond*`` or ``fail*`` methods will be formatted by the **CodeIgniter\\API\\JSONFormatter** class. The resulting
JSON data will be sent back to the client.

Class Reference
***************
.. php:method:: setResponseFormat($format)

    :param string $format The type of response to return, either ``json`` or ``xml``

    This defines the format to be used when formatting arrays in responses. If you provide a ``null`` value for
    ``$format``, it will be automatically determined through content negotiation.

::

    return $this->setResponseFormat('json')->respond(['error' => false]);


.. php:method:: respond($data[, $statusCode=200[, $message='']])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param int    $statusCode: The HTTP status code to return. Defaults to 200
    :param string $message: A custom "reason" message to return.

    This is the method used by all other methods in this trait to return a response to the client.

    The ``$data`` element can be either a string or an array. By default, a string will be returned as HTML,
    while an array will be run through json_encode and returned as JSON, unless :doc:`Content Negotiation </incoming/content_negotiation>`
    determines it should be returned in a different format.

    If a ``$message`` string is passed, it will be used in place of the standard IANA reason codes for the
    response status. Not every client will respect the custom codes, though, and will use the IANA standards
    that match the status code.

    .. note:: Since it sets the status code and body on the active Response instance, this should always
        be the final method in the script execution.

.. php:method:: fail($messages[, int $status=400[, string $code=null[, string $message='']]])

    :param mixed $messages: A string or array of strings that contain error messages encountered.
    :param int   $status: The HTTP status code to return. Defaults to 400.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: A multi-part response in the client's preferred format.

    The is the generic method used to represent a failed response, and is used by all of the other "fail" methods.

    The ``$messages`` element can be either a string or an array of strings.

    The ``$status`` parameter is the HTTP status code that should be returned.

    Since many APIs are better served using custom error codes, a custom error code can be passed in the third
    parameter. If no value is present, it will be the same as ``$status``.

    If a ``$message`` string is passed, it will be used in place of the standard IANA reason codes for the
    response status. Not every client will respect the custom codes, though, and will use the IANA standards
    that match the status code.

    The response is an array with two elements: ``error`` and ``messages``. The ``error`` element contains the status
    code of the error. The ``messages`` element contains an array of error messages. It would look something like::

	    $response = [
	        'status'   => 400,
	        'code'     => '321a',
	        'messages' => [
	            'Error message 1',
	            'Error message 2'
	        ]
	    ];

.. php:method:: respondCreated($data = null[, string $message = ''])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a new resource was created, typically 201.::

	    $user = $userModel->insert($data);
	    return $this->respondCreated($user);

.. php:method:: respondDeleted($data = null[, string $message = ''])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a new resource was deleted as the result of this API call, typically 200.

    ::

	    $user = $userModel->delete($id);
	    return $this->respondDeleted(['id' => $id]);

.. php:method:: respondNoContent(string $message = 'No Content')

    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a command was successfully executed by the server but there is no
    meaningful reply to send back to the client, typically 204.

    ::

	    sleep(1);
	    return $this->respondNoContent();

.. php:method:: failUnauthorized(string $description = 'Unauthorized'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the user either has not been authorized,
    or has incorrect authorization. Status code is 401.

    ::

	    return $this->failUnauthorized('Invalid Auth token');

.. php:method:: failForbidden(string $description = 'Forbidden'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Unlike ``failUnauthorized``, this method should be used when the requested API endpoint is never allowed.
    Unauthorized implies the client is encouraged to try again with different credentials. Forbidden means
    the client should not try again because it won't help. Status code is 403.

    ::

    	return $this->failForbidden('Invalid API endpoint.');

.. php:method:: failNotFound(string $description = 'Not Found'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the requested resource cannot be found. Status code is 404.

    ::

    	return $this->failNotFound('User 13 cannot be found.');

.. php:method:: failValidationError(string $description = 'Bad Request'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when data the client sent did not pass validation rules.
    Status code is typically 400.

    ::

    	return $this->failValidationError($validation->getErrors());

.. php:method:: failResourceExists(string $description = 'Conflict'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the resource the client is trying to create already exists.
    Status code is typically 409.

    ::

    	return $this->failResourceExists('A user already exists with that email.');

.. php:method:: failResourceGone(string $description = 'Gone'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the requested resource was previously deleted and
    is no longer available. Status code is typically 410.

    ::

    	return $this->failResourceGone('That user has been previously deleted.');

.. php:method:: failTooManyRequests(string $description = 'Too Many Requests'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the client has called an API endpoint too many times.
    This might be due to some form of throttling or rate limiting. Status code is typically 400.

    ::

    	return $this->failTooManyRequests('You must wait 15 seconds before making another request.');

.. php:method:: failServerError(string $description = 'Internal Server Error'[, string $code = null[, string $message = '']])

    :param string $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when there is a server error.

    ::

    	return $this->failServerError('Server error.');
