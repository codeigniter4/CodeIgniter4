#################
Testing Responses
#################

The ``TestResponse`` class provides a number of helpful functions for parsing and testing responses
from your test cases. Usually a ``TestResponse`` will be provided for you as a result of your
`Controller Tests <controllers.html>`_ or `HTTP Feature Tests <feature.html>`_, but you can always
create your own directly using any ``ResponseInterface``::

	$result = new \CodeIgniter\Test\TestResponse($response);
	$result->assertOK();

Testing the Response
====================

Whether you have received a ``TestResponse`` as a result of your tests or created one yourself,
there are a number of new assertions that you can use in your tests.

Accessing Request/Response
--------------------------

**request()**

You can access directly the Request object, if it was set during testing::

    $request = $results->request();

**response()**

This allows you direct access to the response object::

    $response = $results->response();

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

This assertion simply uses the **isOK()** method to test a response. **assertNotOK** is the inverse of this assertion.
::

    $result->assertOK();

**isRedirect()**

Returns a boolean true/false based on whether the response is a redirected response.
::

    if ($result->isRedirect())
    {
        ...
    }

**assertRedirect()**

Asserts that the Response is an instance of RedirectResponse. **assertNotRedirect** is the inverse of this assertion.
::

    $result->assertRedirect();

**assertRedirectTo()**

Asserts that the Response is an instance of RedirectResponse and the destination
matches the uri given.
::

    $result->assertRedirectTo('foo/bar');

**getRedirectUrl()**

Returns the URL set for a RedirectResponse, or null for failure.
::

    $url = $result->getRedirectUrl();
    $this->assertEquals(site_url('foo/bar'), $url);

**assertStatus(int $code)**

Asserts that the HTTP status code returned matches $code.
::

    $result->assertStatus(403);


Session Assertions
------------------

**assertSessionHas(string $key, $value = null)**

Asserts that a value exists in the resulting session. If $value is passed, will also assert that the variable's value
matches what was specified.
::

    $result->assertSessionHas('logged_in', 123);

**assertSessionMissing(string $key)**

Asserts that the resulting session does not include the specified $key.
::

    $result->assertSessionMissin('logged_in');


Header Assertions
-----------------

**assertHeader(string $key, $value = null)**

Asserts that a header named **$key** exists in the response. If **$value** is not empty, will also assert that
the values match.
::

    $result->assertHeader('Content-Type', 'text/html');

**assertHeaderMissing(string $key)**

Asserts that a header name **$key** does not exist in the response.
::

    $result->assertHeader('Accepts');


Cookie Assertions
-----------------

**assertCookie(string $key, $value = null, string $prefix = '')**

Asserts that a cookie named **$key** exists in the response. If **$value** is not empty, will also assert that
the values match. You can set the cookie prefix, if needed, by passing it in as the third parameter.
::

    $result->assertCookie('foo', 'bar');

**assertCookieMissing(string $key)**

Asserts that a cookie named **$key** does not exist in the response.
::

    $result->assertCookieMissing('ci_session');

**assertCookieExpired(string $key, string $prefix = '')**

Asserts that a cookie named **$key** exists, but has expired. You can set the cookie prefix, if needed, by passing it
in as the second parameter.
::

    $result->assertCookieExpired('foo');

DOM Helpers
-----------

The response you get back contains a number of helper methods to inspect the HTML output within the response. These
are useful for using within assertions in your tests.

The **see()** method checks the text on the page to see if it exists either by itself, or more specifically within
a tag, as specified by type, class, or id::

    // Check that "Hello World" is on the page
    $results->see('Hello World');
    // Check that "Hello World" is within an h1 tag
    $results->see('Hello World', 'h1');
    // Check that "Hello World" is within an element with the "notice" class
    $results->see('Hello World', '.notice');
    // Check that "Hello World" is within an element with id of "title"
    $results->see('Hellow World', '#title');

The **dontSee()** method is the exact opposite::

    // Checks that "Hello World" does NOT exist on the page
    $results->dontSee('Hello World');
    // Checks that "Hellow World" does NOT exist within any h1 tag
    $results->dontSee('Hello World', 'h1');

The **seeElement()** and **dontSeeElement()** are very similar to the previous methods, but do not look at the
values of the elements. Instead, they simply check that the elements exist on the page::

    // Check that an element with class 'notice' exists
    $results->seeElement('.notice');
    // Check that an element with id 'title' exists
    $results->seeElement('#title')
    // Verify that an element with id 'title' does NOT exist
    $results->dontSeeElement('#title');

You can use **seeLink()** to ensure that a link appears on the page with the specified text::

    // Check that a link exists with 'Upgrade Account' as the text::
    $results->seeLink('Upgrade Account');
    // Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
    $results->seeLink('Upgrade Account', '.upsell');

The **seeInField()** method checks for any input tags exist with the name and value::

    // Check that an input exists named 'user' with the value 'John Snow'
    $results->seeInField('user', 'John Snow');
    // Check a multi-dimensional input
    $results->seeInField('user[name]', 'John Snow');

Finally, you can check if a checkbox exists and is checked with the **seeCheckboxIsChecked()** method::

    // Check if checkbox is checked with class of 'foo'
    $results->seeCheckboxIsChecked('.foo');
    // Check if checkbox with id of 'bar' is checked
    $results->seeCheckboxIsChecked('#bar');

DOM Assertions
--------------

You can perform tests to see if specific elements/text/etc exist with the body of the response with the following
assertions.

**assertSee(string $search = null, string $element = null)**

Asserts that text/HTML is on the page, either by itself or - more specifically - within
a tag, as specified by type, class, or id::

    // Check that "Hello World" is on the page
    $result->assertSee('Hello World');
    // Check that "Hello World" is within an h1 tag
    $result->assertSee('Hello World', 'h1');
    // Check that "Hello World" is within an element with the "notice" class
    $result->assertSee('Hello World', '.notice');
    // Check that "Hello World" is within an element with id of "title"
    $result->assertSee('Hellow World', '#title');


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

You can use this method to determine if ``$response`` actually holds JSON content::

	// Verify the response is JSON
	$this->assertTrue($result->getJSON() !== false)

.. note:: Be aware that the JSON string will be pretty-printed in the result.

**assertJSONFragment(array $fragment)**

Asserts that $fragment is found within the JSON response. It does not need to match the entire JSON value.

::

    // Response body is this:
    [
        'config' => ['key-a', 'key-b']
    ]

    // Is true
    $result->assertJSONFragment(['config' => ['key-a']]);

**assertJSONExact($test)**

Similar to **assertJSONFragment()**, but checks the entire JSON response to ensure exact matches.


Working With XML
----------------

**getXML()**

If your application returns XML, you can retrieve it through this method.
