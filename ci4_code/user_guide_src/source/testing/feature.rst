####################
HTTP Feature Testing
####################

Feature testing allows you to view the results of a single call to your application. This might be returning the
results of a single web form, hitting an API endpoint, and more. This is handy because it allows you to test the entire
life-cycle of a single request, ensuring that the routing works, the response is the correct format, analyze the results,
and more.

.. contents::
    :local:
    :depth: 2

The Test Class
==============

Feature testing requires that all of your test classes use the ``CodeIgniter\Test\DatabaseTestCase``
and ``CodeIgniter\Test\FeatureTestTrait`` traits. Since these testing tools rely on proper database
staging you must always ensure that ``parent::setUp()`` and ``parent::tearDown()``
are called if you implement your own methods.
::

    <?php

    namespace App;

    use CodeIgniter\Test\DatabaseTestTrait;
    use CodeIgniter\Test\FeatureTestTrait;

    class TestFoo extends FeatureTestCase
    {
    	use DatabaseTestTrait, FeatureTestTrait;

        protected function setUp(): void
        {
            parent::setUp();

			$this->myClassMethod();
        }

        protected function tearDown(): void
        {
            parent::tearDown();

			$this->anotherClassMethod();
        }
    }

Requesting A Page
=================

Essentially, feature tests simply allows you to call an endpoint on your application and get the results back.
to do this, you use the ``call()`` method. The first parameter is the HTTP method to use (most frequently either GET or POST).
The second parameter is the path on your site to test. The third parameter accepts an array that is used to populate the
superglobal variables for the HTTP verb you are using. So, a method of **GET** would have the **$_GET** variable
populated, while a **post** request would have the **$_POST** array populated.
::

    // Get a simple page
    $result = $this->call('get', '/');

    // Submit a form
    $result = $this->call('post', 'contact'), [
        'name' => 'Fred Flintstone',
        'email' => 'flintyfred@example.com'
    ]);

Shorthand methods for each of the HTTP verbs exist to ease typing and make things clearer::

    $this->get($path, $params);
    $this->post($path, $params);
    $this->put($path, $params);
    $this->patch($path, $params);
    $this->delete($path, $params);
    $this->options($path, $params);

.. note:: The $params array does not make sense for every HTTP verb, but is included for consistency.

Setting Different Routes
------------------------

You can use a custom collection of routes by passing an array of "routes" into the ``withRoutes()`` method. This will
override any existing routes in the system::

    $routes = [
       [ 'get', 'users', 'UserController::list' ]
     ];

    $result = $this->withRoutes($routes)
        ->get('users');

Each of the "routes" is a 3 element array containing the HTTP verb (or "add" for all),
the URI to match, and the routing destination.


Setting Session Values
----------------------

You can set custom session values to use during a single test with the ``withSession()`` method. This takes an array
of key/value pairs that should exist within the $_SESSION variable when this request is made, or ``null` to indicate
that the current values of ``$_SESSION`` should be used. This is handy for testing authentication and more.
::

    $values = [
        'logged_in' => 123
    ];

    $result = $this->withSession($values)
        ->get('admin');

    // Or...

    $_SESSION['logged_in'] = 123;

    $result = $this->withSession()->get('admin');

Setting Headers
---------------

You can set header values with the ``withHeaders()`` method. This takes an array of key/value pairs that would be
passed as a header into the call.::

    $headers = [
        'CONTENT_TYPE' => 'application/json'
    ];

    $result = $this->withHeaders($headers)->post('users');

Bypassing Events
----------------

Events are handy to use in your application, but can be problematic during testing. Especially events that are used
to send out emails. You can tell the system to skip any event handling with the ``skipEvents()`` method::

    $result = $this->skipEvents()
        ->post('users', $userInfo);

Formatting The Request
-----------------------

You can set the format of your request's body using the ``withBodyFormat()`` method. Currently this supports either
`json` or `xml`. This will take the parameters passed into ``call(), post(), get()...`` and assign them to the
body of the request in the given format. This will also set the `Content-Type` header for your request accordingly.
This is useful when testing JSON or XML API's so that you can set the request in the form that the controller will expect.
::

    // If your feature test contains this:
    $result = $this->withBodyFormat('json')
        ->post('users', $userInfo);

    // Your controller can then get the parameters passed in with:
    $userInfo = $this->request->getJson();

Setting the Body
----------------

You can set the body of your request with the ``withBody()`` method. This allows you to format the body how you want
to format it. It is recommended that you use this if you have more complicated xml's to test. This will also not set
the Content-Type header for you so if you need that, you can set it with the ``withHeaders()`` method.

Checking the Response
=====================

``FeatureTestTrait::call()`` returns an instance of a ``TestResponse``. See `Testing Responses <response.html>`_ on
how to use this class to perform additional assertions and verification in your test cases.
