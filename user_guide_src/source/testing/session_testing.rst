###############
Session Testing
###############

Testing session behavior in your application is made simple with the ArrayHandler session driver.
Unlike other session drivers, ArrayHandler does not persist data to disk, database, or external storage.
This allows you to simulate session interactions safely during unit or integration tests, without affecting real session data.

Using this driver, you can set, retrieve, and assert session data entirely in memory, making your tests faster and more isolated.
While in most production scenarios you would use file, database, or cache-backed sessions, ArrayHandler exists specifically to support testing workflows and prevent side effects.

.. contents::
   :local:
   :depth: 2

Initializing Sessions
=====================

You can initialize a session using the ArrayHandler driver for testing. This example shows how to create a session instance with a proper configuration:

.. literalinclude:: session_testing/001.php

Setting and Retrieving Data
===========================

Once initialized, you can set session values and retrieve them as usual:

.. literalinclude:: session_testing/002.php

.. note::

   Session data is stored in memory and lasts as long as the ArrayHandler object exists;
   after the object is destroyed (typically at the end of a request or test), the data is lost.

Example Test Case
=================

Here's a simple example demonstrating usage of the ArrayHandler in a PHPUnit test:

.. literalinclude:: session_testing/003.php

Session Assertions
==================

Using PHPUnit Assertions with ArrayHandler
------------------------------------------

When testing sessions directly with Session and ArrayHandler in a unit test, use standard PHPUnit assertions. 
``assertSessionHas()`` and ``assertSessionMissing()`` are not available in this context because you are interacting directly with the session object,
not a response object.

.. literalinclude:: session_testing/004.php

Session Assertions via TestResponse
-----------------------------------

When testing controllers or HTTP responses, you can use CodeIgniter 4’s session 
assertion helpers, such as ``assertSessionHas()`` and ``assertSessionMissing()``,
which are available on the ``TestResponse`` object. These helpers allow you to
assert the state of the session during the HTTP request/response lifecycle.
See more: :ref:`Session Assertions <response-session-assertions>`

Custom Session Values
=====================

In Feature Tests, you can provide custom session data for a single test using the ``withSession()`` method.
This allows you to simulate session states such as logged-in users or specific roles during the request.
For full details and examples, see: :ref:`Setting Session Values <feature-setting-session-values>`