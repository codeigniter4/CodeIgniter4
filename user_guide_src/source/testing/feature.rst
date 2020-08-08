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

Feature testing requires that all of your test classes extend the ``CodeIgniter\Test\FeatureTestCase``
class or use the ``CodeIgniter\Test\FeatureTestTrait``. Since these testing tools extend
`CIDatabaseTestCase <database.html>`_ you must always ensure that ``parent::setUp()`` and ``parent::tearDown()``
are called before you take your actions.
::

    <?php namespace App;

    use CodeIgniter\Test\FeatureTestCase;

    class TestFoo extends FeatureTestCase
    {
        public function setUp(): void
        {
            parent::setUp();
        }

        public function tearDown(): void
        {
            parent::tearDown();
        }
    }

Requesting A Page
=================

Essentially, the FeatureTestCase simply allows you to call an endpoint on your application and get the results back.
to do this, you use the ``call()`` method. The first parameter is the HTTP method to use (most frequently either GET or POST).
The second parameter is the path on your site to test. The third parameter accepts an array that is used to populate the
superglobal variables for the HTTP verb you are using. So, a method of **GET** would have the **$_GET** variable
populated, while a **post** request would have the **$_POST** array populated.
::

    // Get a simple page
    $result = $this->call('get', site_url());

    // Submit a form
    $result = $this->call('post', site_url('contact'), [
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

Bypassing Events
----------------

Events are handy to use in your application, but can be problematic during testing. Especially events that are used
to send out emails. You can tell the system to skip any event handling with the ``skipEvents()`` method::

    $result = $this->skipEvents()
        ->post('users', $userInfo);


Testing the Response
====================

Once you've performed a ``call()`` and have results, there are a number of new assertions that you can use in your
tests.

.. note:: The Response object is publicly available at ``$result->response``. You can use that instance to perform
    other assertions against, if needed.

Checking Response Status
------------------------

**isOK()**

Returns a boolean true/false based on whether the response is perceived to be "ok". This is primarily determined by
a response status code in the 200 or 300's.
::

    if ($result->isOK())
    {
        ...
    }

**assertOK()**

This assertion simply uses the **isOK()** method to test a response.
::

    $this->assertOK();

**isRedirect()**

Returns a boolean true/false based on whether the response is a redirected response.
::

    if ($result->isRedirect())
    {
        ...
    }

**assertRedirect()**

Asserts that the Response is an instance of RedirectResponse.
::

    $this->assertRedirect();

**getRedirectUrl()**

Returns the URL set for a RedirectResponse, or null for failure.
::

    $url = $result->getRedirectUrl();
    $this->assertEquals(site_url('foo/bar'), $url);

**assertStatus(int $code)**

Asserts that the HTTP status code returned matches $code.
::

    $this->assertStatus(403);


Session Assertions
------------------

**assertSessionHas(string $key, $value = null)**

Asserts that a value exists in the resulting session. If $value is passed, will also assert that the variable's value
matches what was specified.
::

    $this->assertSessionHas('logged_in', 123);

**assertSessionMissing(string $key)**

Asserts that the resulting session does not include the specified $key.
::

    $this->assertSessionMissin('logged_in');


Header Assertions
-----------------

**assertHeader(string $key, $value = null)**

Asserts that a header named **$key** exists in the response. If **$value** is not empty, will also assert that
the values match.
::

    $this->assertHeader('Content-Type', 'text/html');

**assertHeaderMissing(string $key)**

Asserts that a header name **$key** does not exist in the response.
::

    $this->assertHeader('Accepts');



Cookie Assertions
-----------------

**assertCookie(string $key, $value = null, string $prefix = '')**

Asserts that a cookie named **$key** exists in the response. If **$value** is not empty, will also assert that
the values match. You can set the cookie prefix, if needed, by passing it in as the third parameter.
::

    $this->assertCookie('foo', 'bar');

**assertCookieMissing(string $key)**

Asserts that a cookie named **$key** does not exist in the response.
::

    $this->assertCookieMissing('ci_session');

**assertCookieExpired(string $key, string $prefix = '')**

Asserts that a cookie named **$key** exists, but has expired. You can set the cookie prefix, if needed, by passing it
in as the second parameter.
::

    $this->assertCookieExpired('foo');


DOM Assertions
--------------

You can perform tests to see if specific elements/text/etc exist with the body of the response with the following
assertions.

**assertSee(string $search = null, string $element = null)**

Asserts that text/HTML is on the page, either by itself or - more specifically - within
a tag, as specified by type, class, or id::

    // Check that "Hello World" is on the page
    $this->assertSee('Hello World');
    // Check that "Hello World" is within an h1 tag
    $this->assertSee('Hello World', 'h1');
    // Check that "Hello World" is within an element with the "notice" class
    $this->assertSee('Hello World', '.notice');
    // Check that "Hello World" is within an element with id of "title"
    $this->assertSee('Hellow World', '#title');


**assertDontSee(string $search = null, string $element = null)**

Asserts the exact opposite of the **assertSee()** method::

    // Checks that "Hello World" does NOT exist on the page
    $results->dontSee('Hello World');
    // Checks that "Hello World" does NOT exist within any h1 tag
    $results->dontSee('Hello World', 'h1');

**assertSeeElement(string $search)**

Similar to **assertSee()**, however this only checks for an existing element. It does not check for specific text::

    // Check that an element with class 'notice' exists
    $results->seeElement('.notice');
    // Check that an element with id 'title' exists
    $results->seeElement('#title')

**assertDontSeeElement(string $search)**

Similar to **assertSee()**, however this only checks for an existing element that is missing. It does not check for
specific text::

    // Verify that an element with id 'title' does NOT exist
    $results->dontSeeElement('#title');

**assertSeeLink(string $text, string $details=null)**

Asserts that an anchor tag is found with matching **$text** as the body of the tag::

    // Check that a link exists with 'Upgrade Account' as the text::
    $results->seeLink('Upgrade Account');
    // Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
    $results->seeLink('Upgrade Account', '.upsell');

**assertSeeInField(string $field, string $value=null)**

Asserts that an input tag exists with the name and value::

    // Check that an input exists named 'user' with the value 'John Snow'
    $results->assertSeeInField('user', 'John Snow');
    // Check a multi-dimensional input
    $results->assertSeeInField('user[name]', 'John Snow');



Working With JSON
-----------------

Responses will frequently contain JSON responses, especially when working with API methods. The following methods
can help to test the responses.

**getJSON()**

This method will return the body of the response as a JSON string::

    // Response body is this:
    ['foo' => 'bar']

    $json = $result->getJSON();

    // $json is this:
    {
        "foo": "bar"
    }

.. note:: Be aware that the JSON string will be pretty-printed in the result.

**assertJSONFragment(array $fragment)**

Asserts that $fragment is found within the JSON response. It does not need to match the entire JSON value.

::

    // Response body is this:
    [
        'config' => ['key-a', 'key-b']
    ]

    // Is true
    $this->assertJSONFragment(['config' => ['key-a']);

.. note:: This simply uses phpUnit's own `assertArraySubset() <https://phpunit.readthedocs.io/en/7.2/assertions.html#assertarraysubset>`_
    method to do the comparison.

**assertJSONExact($test)**

Similar to **assertJSONFragment()**, but checks the entire JSON response to ensure exact matches.


Working With XML
----------------

**getXML()**

If your application returns XML, you can retrieve it through this method.

