#################
Testing Responses
#################

The ``TestResponse`` class provides a number of helpful functions for parsing and testing responses
from your test cases. Usually a ``TestResponse`` will be provided for you as a result of your
:doc:`Controller Tests <controllers>` or :doc:`HTTP Feature Tests <feature>`, but you can always
create your own directly using any ``ResponseInterface``:

.. literalinclude:: response/001.php
   :lines: 2-

.. contents::
    :local:
    :depth: 2

Testing the Response
********************

Whether you have received a ``TestResponse`` as a result of your tests or created one yourself,
there are a number of new assertions that you can use in your tests.

Accessing Request/Response
==========================

request()
---------

You can access directly the Request object, if it was set during testing:

.. literalinclude:: response/002.php
   :lines: 2-

response()
----------

This allows you direct access to the response object:

.. literalinclude:: response/003.php
   :lines: 2-

Checking Response Status
========================

isOK()
------

Returns a boolean true/false based on whether the response is perceived to be "ok". This is primarily determined by
a response status code in the 200 or 300's. An empty body is not considered valid, unless in redirects.

.. literalinclude:: response/004.php
   :lines: 2-

assertOK()
----------

This assertion simply uses the ``isOK()`` method to test a response. ``assertNotOK()`` is the inverse of this assertion.

.. literalinclude:: response/005.php
   :lines: 2-

isRedirect()
------------

Returns a boolean true/false based on whether the response is a redirected response.

.. literalinclude:: response/006.php
   :lines: 2-

assertRedirect()
----------------

Asserts that the Response is an instance of RedirectResponse. ``assertNotRedirect()`` is the inverse of this assertion.

.. literalinclude:: response/007.php
   :lines: 2-

assertRedirectTo()
------------------

Asserts that the Response is an instance of RedirectResponse and the destination
matches the uri given.

.. literalinclude:: response/008.php
   :lines: 2-

getRedirectUrl()
----------------

Returns the URL set for a RedirectResponse, or null for failure.

.. literalinclude:: response/009.php
   :lines: 2-

assertStatus(int $code)
-----------------------

Asserts that the HTTP status code returned matches $code.

.. literalinclude:: response/010.php
   :lines: 2-

Session Assertions
==================

assertSessionHas(string $key, $value = null)
--------------------------------------------

Asserts that a value exists in the resulting session. If $value is passed, will also assert that the variable's value
matches what was specified.

.. literalinclude:: response/011.php
   :lines: 2-

assertSessionMissing(string $key)
---------------------------------

Asserts that the resulting session does not include the specified $key.

.. literalinclude:: response/012.php
   :lines: 2-

Header Assertions
=================

assertHeader(string $key, $value = null)
----------------------------------------

Asserts that a header named ``$key`` exists in the response. If ``$value`` is not empty, will also assert that
the values match.

.. literalinclude:: response/013.php
   :lines: 2-

assertHeaderMissing(string $key)
--------------------------------

Asserts that a header name ``$key`` does not exist in the response.

.. literalinclude:: response/014.php
   :lines: 2-

Cookie Assertions
=================

assertCookie(string $key, $value = null, string $prefix = '')
-------------------------------------------------------------

Asserts that a cookie named ``$key`` exists in the response. If ``$value`` is not empty, will also assert that
the values match. You can set the cookie prefix, if needed, by passing it in as the third parameter.

.. literalinclude:: response/015.php
   :lines: 2-

assertCookieMissing(string $key)
--------------------------------

Asserts that a cookie named ``$key`` does not exist in the response.

.. literalinclude:: response/016.php
   :lines: 2-

assertCookieExpired(string $key, string $prefix = '')
-----------------------------------------------------

Asserts that a cookie named ``$key`` exists, but has expired. You can set the cookie prefix, if needed, by passing it
in as the second parameter.

.. literalinclude:: response/017.php
   :lines: 2-

DOM Helpers
===========

The response you get back contains a number of helper methods to inspect the HTML output within the response. These
are useful for using within assertions in your tests.

see()
-----

Returns a boolean true/false based on whether the text on the page exists either
by itself, or more specifically within
a tag, as specified by type, class, or id:

.. literalinclude:: response/018.php
   :lines: 2-

The ``dontSee()`` method is the exact opposite:

.. literalinclude:: response/019.php
   :lines: 2-

seeElement()
------------

The ``seeElement()`` and ``dontSeeElement()`` are very similar to the previous methods, but do not look at the
values of the elements. Instead, they simply check that the elements exist on the page:

.. literalinclude:: response/020.php
   :lines: 2-

seeLink()
---------

You can use ``seeLink()`` to ensure that a link appears on the page with the specified text:

.. literalinclude:: response/021.php
   :lines: 2-

seeInField()
------------

The ``seeInField()`` method checks for any input tags exist with the name and value:

.. literalinclude:: response/022.php
   :lines: 2-

seeCheckboxIsChecked()
----------------------

Finally, you can check if a checkbox exists and is checked with the ``seeCheckboxIsChecked()`` method:

.. literalinclude:: response/023.php
   :lines: 2-

DOM Assertions
==============

You can perform tests to see if specific elements/text/etc exist with the body of the response with the following
assertions.

assertSee(string $search = null, string $element = null)
--------------------------------------------------------

Asserts that text/HTML is on the page, either by itself or - more specifically - within
a tag, as specified by type, class, or id:

.. literalinclude:: response/024.php
   :lines: 2-

assertDontSee(string $search = null, string $element = null)
------------------------------------------------------------

Asserts the exact opposite of the ``assertSee()`` method:

.. literalinclude:: response/025.php
   :lines: 2-

assertSeeElement(string $search)
--------------------------------

Similar to ``assertSee()``, however this only checks for an existing element. It does not check for specific text:

.. literalinclude:: response/026.php
   :lines: 2-

assertDontSeeElement(string $search)
------------------------------------

Similar to ``assertSee()``, however this only checks for an existing element that is missing. It does not check for
specific text:

.. literalinclude:: response/027.php
   :lines: 2-

assertSeeLink(string $text, string $details = null)
---------------------------------------------------

Asserts that an anchor tag is found with matching ``$text`` as the body of the tag:

.. literalinclude:: response/028.php
   :lines: 2-

assertSeeInField(string $field, string $value = null)
-----------------------------------------------------

Asserts that an input tag exists with the name and value:

.. literalinclude:: response/029.php
   :lines: 2-

Working with JSON
=================

Responses will frequently contain JSON responses, especially when working with API methods. The following methods
can help to test the responses.

getJSON()
---------

This method will return the body of the response as a JSON string:

.. literalinclude:: response/030.php
   :lines: 2-

You can use this method to determine if ``$response`` actually holds JSON content:

.. literalinclude:: response/031.php
   :lines: 2-

.. note:: Be aware that the JSON string will be pretty-printed in the result.

assertJSONFragment(array $fragment)
-----------------------------------

Asserts that ``$fragment`` is found within the JSON response. It does not need to match the entire JSON value.

.. literalinclude:: response/032.php
   :lines: 2-

assertJSONExact($test)
----------------------

Similar to ``assertJSONFragment()``, but checks the entire JSON response to ensure exact matches.

Working with XML
================

getXML()
--------

If your application returns XML, you can retrieve it through this method.
