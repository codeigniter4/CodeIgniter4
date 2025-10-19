#############
API Responses
#############

Much of modern PHP development requires building APIs, whether simply to provide data for a javascript-heavy
single page application, or as a standalone product. CodeIgniter provides a couple of traits that can be
used with any controller to make common response types simple, with no need to remember which HTTP status code
should be returned for which response types.

.. contents::
    :local:
    :depth: 2

*****************
Response Examples
*****************

The following example shows a common usage pattern within your controllers.

.. literalinclude:: api_responses/001.php

In this example, an HTTP status code of 201 is returned, with the generic status message, 'Created'. Methods
exist for the most common use cases:

.. literalinclude:: api_responses/002.php

.. _api-response-trait-handling-response-types:

***********************
Handling Response Types
***********************

When you pass your data in any of these methods, they will determine the data type to format the results as based on
the following criteria:

* The format is determined according to the controller's ``$this->format`` value.
  If that is ``null``, it will try to negotiate the content type with what the
  client asked for, defaulting to the first element (JSON by default) in the
  ``$supportedResponseFormats`` property within **app/Config/Format.php**.
* The data will be formatted according to the format. If the format is not JSON
  and data is a string, it will be treated as HTML to send back to the client.

.. note:: Prior to v4.5.0, due to a bug, if data is a string, it will be treated
    as HTML even if the format is JSON.

To define the formatter that is used, edit **app/Config/Format.php**. The ``$supportedResponseFormats`` contains a list of
mime types that your application can automatically format the response for. By default, the system knows how to
format both XML and JSON responses:

.. literalinclude:: api_responses/003.php

.. note:: Since ``v4.7.0``, you can change the default JSON encoding depth by editing **app/Config/Format.php** file. The ``$jsonEncodeDepth`` value defines the maximum depth, with a default of ``512``.

This is the array that is used during :doc:`Content Negotiation </incoming/content_negotiation>` to determine which
type of response to return. If no matches are found between what the client requested and what you support, the first
format in this array is what will be returned.

Next, you need to define the class that is used to format the array of data. This must be a fully qualified class
name, and the class must implement ``CodeIgniter\Format\FormatterInterface``. Formatters come out of the box that
support both JSON and XML:

.. literalinclude:: api_responses/004.php

So, if your request asks for JSON formatted data in an **Accept** header, the data array you pass any of the
``respond*`` or ``fail*`` methods will be formatted by the ``CodeIgniter\Format\JSONFormatter`` class. The resulting
JSON data will be sent back to the client.

***************
Class Reference
***************

.. php:method:: setResponseFormat($format)

    :param string $format: The type of response to return, either ``json`` or ``xml``

    This defines the format to be used when formatting arrays in responses. If you provide a ``null`` value for
    ``$format``, it will be automatically determined through content negotiation.

.. literalinclude:: api_responses/005.php

.. php:method:: respond($data[, $statusCode = 200[, $message = '']])

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

.. php:method:: fail($messages[, int $status = 400[, string $code = null[, string $message = '']]])

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

    The response is an array with three elements: ``status``, ``code``, and ``messages``.
    - The ``status`` element contains the status code of the error.
    - The ``code`` element contains a custom, API-specific error code.
    - The ``messages`` element contains an array of error messages.

    Depending on the number of error messages, the response would look something like:

    .. literalinclude:: api_responses/006.php

.. php:method:: respondCreated($data = null[, string $message = ''])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a new resource was created, typically 201:

    .. literalinclude:: api_responses/007.php

.. php:method:: respondDeleted($data = null[, string $message = ''])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a new resource was deleted as the result of this API call, typically 200.

    .. literalinclude:: api_responses/008.php

.. php:method:: respondNoContent(string $message = 'No Content')

    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when a command was successfully executed by the server but there is no
    meaningful reply to send back to the client, typically 204.

    .. literalinclude:: api_responses/009.php

.. php:method:: failUnauthorized(string $description = 'Unauthorized'[, string $code = null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the user either has not been authorized,
    or has incorrect authorization. Status code is 401.

    .. literalinclude:: api_responses/010.php

.. php:method:: failForbidden(string $description = 'Forbidden'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Unlike ``failUnauthorized()``, this method should be used when the requested API endpoint is never allowed.
    Unauthorized implies the client is encouraged to try again with different credentials. Forbidden means
    the client should not try again because it won't help. Status code is 403.

    .. literalinclude:: api_responses/011.php

.. php:method:: failNotFound(string $description = 'Not Found'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the requested resource cannot be found. Status code is 404.

    .. literalinclude:: api_responses/012.php

.. php:method:: failValidationErrors($errors[, string $code=null[, string $message = '']])

    :param mixed  $errors: The error message or array of messages to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when data the client sent did not pass validation rules. Status code is typically 400.

    .. literalinclude:: api_responses/013.php

.. php:method:: failResourceExists(string $description = 'Conflict'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the resource the client is trying to create already exists.
    Status code is typically 409.

    .. literalinclude:: api_responses/014.php

.. php:method:: failResourceGone(string $description = 'Gone'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the requested resource was previously deleted and
    is no longer available. Status code is typically 410.

    .. literalinclude:: api_responses/015.php

.. php:method:: failTooManyRequests(string $description = 'Too Many Requests'[, string $code=null[, string $message = '']])

    :param string  $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when the client has called an API endpoint too many times.
    This might be due to some form of throttling or rate limiting. Status code is typically 400.

    .. literalinclude:: api_responses/016.php

.. php:method:: failServerError(string $description = 'Internal Server Error'[, string $code = null[, string $message = '']])

    :param string $description: The error message to show the user.
    :param string $code: A custom, API-specific, error code.
    :param string $message: A custom "reason" message to return.
    :returns: The value of the Response object's send() method.

    Sets the appropriate status code to use when there is a server error.

    .. literalinclude:: api_responses/017.php

.. _api_response_trait_paginate:

********************
Pagination Responses
********************

When returning paginated results from an API endpoint, you can use the ``paginate()`` method to return the
results along with the pagination information. This helps to keep consistent responses across your API, while
providing all of the information that clients will need to properly page through the results.

-------------
Example Usage
-------------

.. literalinclude:: api_responses/018.php

A typical response might look like:

.. code-block:: json

    {
        "data": [
            {
                "id": 1,
                "username": "admin",
                "email": "admin@example.com"
            },
            {
                "id": 2,
                "username": "user",
                "email": "user@example.com"
            }
        ],
        "meta": {
            "page": 1,
            "perPage": 20,
            "total": 2,
            "totalPages": 1
        },
        "links": {
            "self": "http://example.com/users?page=1",
            "first": "http://example.com/users?page=1",
            "last": "http://example.com/users?page=1",
            "next": null,
            "previous": null
        }
    }

The ``paginate()`` method will always wrap the results in a ``data`` element, and will also include ``meta``
and ``links`` elements to help the client page through the results. If there are no results, the ``data`` element will
be an empty array, and the ``meta`` and ``links`` elements will still be present, but with values that indicate no results.

You can also pass it a Builder instance instead of a Model, as long as the Builder is properly configured with the table
name and any necessary joins or where clauses.

.. literalinclude:: api_responses/019.php

***************
Class Reference
***************

.. php:method:: paginate(Model|BaseBuilder $resource, int $perPage = 20, ?string $transformWith = null)

    :param Model|BaseBuilder $resource: The resource to paginate, either a Model or a Builder instance.
    :param int $perPage: The number of items to return per page.
    :param string|null $transformWith: Optional transformer class name to transform the results.

    Generates a paginated response from the given resource. The resource can be either a Model or a Builder
    instance. The method will automatically determine the current page from the request's query parameters.
    The response will include the paginated data, along with metadata about the pagination state and links
    to navigate through the pages.

    If you provide a ``$transformWith`` parameter with a transformer class name, each item in the paginated
    results will be transformed using that transformer before being returned. This is useful for controlling
    the structure and content of your API responses. See :ref:`API Transformers <api_transformers>` for more
    information on creating and using transformers.

    Example with transformer:

    .. literalinclude:: api_responses/020.php
