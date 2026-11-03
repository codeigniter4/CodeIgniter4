# Testing Responses

The `TestResponse` class provides a number of helpful functions for parsing and testing responses from your test cases. Usually a `TestResponse` will be provided for you as a result of your [Controller Tests](controllers.md) or [HTTP Feature Tests](feature.md), but you can always create your own directly using any `ResponseInterface`:

```php
$result = new \CodeIgniter\Test\TestResponse($response);
$result->assertOK();
```

## Testing the Response

Whether you have received a `TestResponse` as a result of your tests or created one yourself, there are a number of new assertions that you can use in your tests.

### Accessing Request/Response

#### request()

You can directly access the Request object, if it was set during testing:

```php
$request = $results->request();
```

#### response()

This allows you direct access to the response object:

```php
$response = $results->response();
```

### Checking Response Status

#### isOK()

Returns a boolean true/false based on whether the response is perceived to be "ok". This is primarily determined by a response status code in the 200s or 300s. An empty body is not considered valid, unless in redirects.

```php
if ($result->isOK()) {
    // ...
}
```

#### assertOK()

This assertion simply uses the `isOK()` method to test a response. `assertNotOK()` is the inverse of this assertion.

```php
$result->assertOK();
```

#### isRedirect()

Returns a boolean true/false based on whether the response is a redirected response.

```php
if ($result->isRedirect()) {
    // ...
}
```

#### assertRedirect()

Asserts that the Response is an instance of RedirectResponse. `assertNotRedirect()` is the inverse of this assertion.

```php
$result->assertRedirect();
```

#### assertRedirectTo()

Asserts that the Response is an instance of RedirectResponse and the destination matches the URI given.

```php
$result->assertRedirectTo('foo/bar');
```

#### getRedirectUrl()

Returns the URL set for a RedirectResponse, or null for failure.

```php
$url = $result->getRedirectUrl();
$this->assertEquals(site_url('foo/bar'), $url);
```

#### assertStatus(int $code)

Asserts that the HTTP status code returned matches `$code`.

```php
$result->assertStatus(403);
```

### Session Assertions

#### assertSessionHas(string $key, $value = null)

Asserts that a value exists in the resulting session. If `$value` is passed, it will also assert that the variable's value matches what was specified.

```php
$result->assertSessionHas('logged_in', 123);
```

#### assertSessionMissing(string $key)

Asserts that the resulting session does not include the specified `$key`.

```php
$result->assertSessionMissing('logged_in');
```

### Header Assertions

#### assertHeader(string $key, $value = null)

Asserts that a header named `$key` exists in the response. If `$value` is not empty, it will also assert that the values match.

```php
$result->assertHeader('Content-Type', 'text/html');
```

#### assertHeaderMissing(string $key)

Asserts that a header named `$key` does not exist in the response.

```php
$result->assertHeader('Accepts');
```

### Cookie Assertions

#### assertCookie(string $key, $value = null, string $prefix = '')

Asserts that a cookie named `$key` exists in the response. If `$value` is not empty, it will also assert that the values match. You can set the cookie prefix, if needed, by passing it in as the third parameter.

```php
$result->assertCookie('foo', 'bar');
```

#### assertCookieMissing(string $key)

Asserts that a cookie named `$key` does not exist in the response.

```php
$result->assertCookieMissing('ci_session');
```

#### assertCookieExpired(string $key, string $prefix = '')

Asserts that a cookie named `$key` exists, but has expired. You can set the cookie prefix, if needed, by passing it in as the second parameter.

```php
$result->assertCookieExpired('foo');
```

### DOM Helpers

The response you get back contains a number of helper methods to inspect the HTML output within the response. These are useful for use within assertions in your tests.

#### see()

Returns a boolean true/false based on whether the text on the page exists either by itself, or more specifically within a tag, as specified by type, class, or id:

```php
// Check that "Hello World" is on the page
if ($results->see('Hello World')) {
    // ...
}

// Check that "Hello World" is within an h1 tag
if ($results->see('Hello World', 'h1')) {
    // ...
}

// Check that "Hello World" is within an element with the "notice" class
if ($results->see('Hello World', '.notice')) {
    // ...
}

// Check that "Hello World" is within an element with id of "title"
if ($results->see('Hello World', '#title')) {
    // ...
}
```

The `dontSee()` method is the exact opposite:

```php
// Checks that "Hello World" does NOT exist on the page
if ($results->dontSee('Hello World')) {
    // ...
}

// Checks that "Hellow World" does NOT exist within any h1 tag
if ($results->dontSee('Hello World', 'h1')) {
    // ...
}
```

#### seeElement()

The `seeElement()` and `dontSeeElement()` methods are very similar to the previous methods, but do not look at the values of the elements. Instead, they simply check that the elements exist on the page:

```php
// Check that an element with class 'notice' exists
if ($results->seeElement('.notice')) {
    // ...
}

// Check that an element with id 'title' exists
if ($results->seeElement('#title')) {
    // ...
}

// Verify that an element with id 'title' does NOT exist
if ($results->dontSeeElement('#title')) {
    // ...
}
```

#### seeLink()

You can use `seeLink()` to ensure that a link appears on the page with the specified text:

```php
// Check that a link exists with 'Upgrade Account' as the text::
if ($results->seeLink('Upgrade Account')) {
    // ...
}

// Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
if ($results->seeLink('Upgrade Account', '.upsell')) {
    // ...
}
```

#### seeInField()

The `seeInField()` method checks whether any input tags exist with the name and value:

```php
// Check that an input exists named 'user' with the value 'John Snow'
if ($results->seeInField('user', 'John Snow')) {
    // ...
}

// Check a multi-dimensional input
if ($results->seeInField('user[name]', 'John Snow')) {
    // ...
}
```

#### seeCheckboxIsChecked()

Finally, you can check if a checkbox exists and is checked with the `seeCheckboxIsChecked()` method:

```php
// Check if checkbox is checked with class of 'foo'
if ($results->seeCheckboxIsChecked('.foo')) {
    // ...
}

// Check if checkbox with id of 'bar' is checked
if ($results->seeCheckboxIsChecked('#bar')) {
    // ...
}
```

#### seeXPath()

!!! success "Available from version 4.5.0"

You can use `seeXPath()` to take advantage of the full power that XPath gives you. This method is aimed at more advanced users who want to write more complex expressions using the DOMXPath object directly:

```php
// Check that h1 element which contains class "heading" is on the page
if ($results->seeXPath('//h1[contains(@class, "heading")]')) {
    // ...
}

// Check that h1 element which contains class "heading" have a text "Hello World"
if ($results->seeXPath('//h1[contains(@class, "heading")][contains(.,"Hello world")]')) {
    // ...
}
```

The `dontSeeXPath()` method is the exact opposite:

```php
// Check that h1 element which contains class "heading" does NOT exist on the page
if ($results->dontSeeXPath('//h1[contains(@class, "heading")]')) {
    // ...
}

// Check that h1 element which contains class "heading" and text "Hello World" does NOT exist on the page
if ($results->dontSeeXPath('//h1[contains(@class, "heading")][contains(.,"Hello world")]')) {
    // ...
}
```

### DOM Assertions

You can perform tests to see if specific elements/text/etc. exist within the body of the response with the following assertions.

#### assertSee(string $search = null, string $element = null)

Asserts that text/HTML is on the page, either by itself or, more specifically, within a tag, as specified by type, class, or id:

```php
// Verify that "Hello World" is on the page
$result->assertSee('Hello World');

// Verify that "Hello World" is within an h1 tag
$result->assertSee('Hello World', 'h1');

// Verify that "Hello World" is within an element with the "notice" class
$result->assertSee('Hello World', '.notice');

// Verify that "Hello World" is within an element with id of "title"
$result->assertSee('Hello World', '#title');
```

#### assertDontSee(string $search = null, string $element = null)

Asserts the exact opposite of the `assertSee()` method:

```php
// Verify that "Hello World" does NOT exist on the page
$results->assertDontSee('Hello World');

// Verify that "Hello World" does NOT exist within any h1 tag
$results->assertDontSee('Hello World', 'h1');
```

#### assertSeeElement(string $search)

Similar to `assertSee()`, but this only checks for an existing element. It does not check for specific text:

```php
// Verify that an element with class 'notice' exists
$results->assertSeeElement('.notice');

// Verify that an element with id 'title' exists
$results->assertSeeElement('#title');
```

#### assertDontSeeElement(string $search)

Similar to `assertSee()`, but this only checks for a missing element. It does not check for specific text:

```php
// Verify that an element with id 'title' does NOT exist
$results->assertDontSeeElement('#title');
```

#### assertSeeLink(string $text, string $details = null)

Asserts that an anchor tag is found with matching `$text` as the body of the tag:

```php
// Verify that a link exists with 'Upgrade Account' as the text::
$results->assertSeeLink('Upgrade Account');

// Verify that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
$results->assertSeeLink('Upgrade Account', '.upsell');
```

#### assertSeeInField(string $field, string $value = null)

Asserts that an input tag exists with the name and value:

```php
// Verify that an input exists named 'user' with the value 'John Snow'
$results->assertSeeInField('user', 'John Snow');

// Verify a multi-dimensional input
$results->assertSeeInField('user[name]', 'John Snow');
```

### Working with JSON

Responses will frequently contain JSON, especially when working with API methods. The following methods can help to test the responses.

#### getJSON()

This method will return the body of the response as a JSON string:

```php
/*
 * Response body is this:
 * ['foo' => 'bar']
 */

$json = $result->getJSON();
/*
 * $json is this:
 * {
 *     "foo": "bar"
 * }
`*/
```

You can use this method to determine if `$response` actually holds JSON content:

```php
// Verify the response is JSON
$this->assertTrue($result->getJSON() !== false);
```

!!! note "Note"
    Be aware that the JSON string will be pretty-printed in the result.

#### assertJSONFragment(array $fragment)

Asserts that `$fragment` is found within the JSON response. It does not need to match the entire JSON value.

```php
/*
 * Response body is this:
 * [
 *     'config' => ['key-a', 'key-b'],
 * ]
 */

// Is true
$result->assertJSONFragment(['config' => ['key-a']]);
```

#### assertJSONExact($test)

Similar to `assertJSONFragment()`, but checks the entire JSON response to ensure exact matches.

### Working with XML

#### getXML()

If your application returns XML, you can retrieve it through this method.
