##################
API Response Trait
##################

Much of modern PHP development requires building API's, whether simply to provide data for a javascript-heavy
single page application, or as a standalone product. CodeIgniter provides an API Response trait that can be
used with any controller to make common response types simple, with no need to remember which HTTP status code
should be returned for which response types.

.. contents:: Page Contents
	:local:

*************
Example Usage
*************

The following example shows a common usage pattern within your controllers.

::

    <?php namespace App\Controllers;

    class Users extends \CodeIgniter\Controller
    {
        use CodeIgniter\API\ResponseTrait;

        public function createUser()
        {
            $model = new UserModel();
            $user = $model->save($this->request->getPost());

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

* If $data is a string, it will be treated as HTML to send back to the client
* If $data is an array, it will try to negotiate the content type with what the client asked for, defaulting to JSON
    if nothing else has been specified within Config\API.php, the ``$supportedResponseFormats`` property.



===============
Class Reference
===============

.. php:method:: respond($data[, $statusCode=200[, $message='']])

    :param mixed  $data: The data to return to the client. Either string or array.
    :param int    $statusCode: The HTTP status code to return. Defaults to 200
    :param string $message: A custom "reason" message to return.

    This is the method used by all other methods in this trait to return a response to the client.

    The ``$data`` element can be either a string or an array. By default, a string will be returned as HTML,
    while an array will be run through json_encode and returned as JSON, unless :doc:`Content Negotiation </libraries/content_negotiation>`
    determines it should be returned in a different format.

    If a ``$message`` string is passed, it will be used in place of the standard IANA reason codes for the
    response status. Not every client will respect the custom codes, though, and will use the IANA standards
    that match the status code.

    .. note:: Since it sets the status code and body on the active Response instance, this should always
        be the final method in the script execution.

