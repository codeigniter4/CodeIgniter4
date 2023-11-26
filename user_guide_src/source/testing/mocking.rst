######################
Mocking System Classes
######################

Several components within the framework provide mocked versions of their classes that can be used during testing. These classes
can take the place of the normal class during test execution, often providing additional assertions to test that actions
have taken place (or not taken place) during the execution of the test. This might be checking data gets cached correctly,
emails were sent correctly, etc.

.. contents::
    :local:
    :depth: 2

Cache
=====

You can mock the cache with the ``mock()`` method, using the ``CacheFactory`` as its only parameter.

.. literalinclude:: mocking/001.php

While this returns an instance of ``CodeIgniter\Test\Mock\MockCache`` that you can use directly, it also inserts the
mock into the Service class, so any calls within your code to ``service('cache')`` or ``Config\Services::cache()`` will
use the mocked class within its place.

When using this in more than one test method within a single file you should call either the ``clean()`` or ``bypass()``
methods during the test ``setUp()`` to ensure a clean slate when your tests run.

Additional Methods
------------------

You can instruct the mocked cache handler to never do any caching with the ``bypass()`` method. This will emulate
using the dummy handler and ensures that your test does not rely on cached data for your tests.

.. literalinclude:: mocking/002.php

Available Assertions
--------------------

The following new assertions are available on the mocked class for using during testing:

.. literalinclude:: mocking/003.php
