####################
Testing CLI Commands
####################

.. _testing-cli-output:

Testing CLI Output
==================

StreamFilterTrait
-----------------

.. versionadded:: 4.3.0

**StreamFilterTrait** provides an alternate to these helper methods.

You may need to test things that are difficult to test. Sometimes, capturing a stream, like PHP's own STDOUT, or STDERR,
might be helpful. The ``StreamFilterTrait`` helps you capture the output from the stream of your choice.

**Overview of methods**

- ``StreamFilterTrait::getStreamFilterBuffer()`` Get the captured data from the buffer.
- ``StreamFilterTrait::resetStreamFilterBuffer()`` Reset captured data.

An example demonstrating this inside one of your test cases:

.. literalinclude:: overview/018.php

The ``StreamFilterTrait`` has a configurator that is called automatically.
See :ref:`Testing Traits <testing-overview-traits>`.

If you override the ``setUp()`` or ``tearDown()`` methods in your test, then you must call the ``parent::setUp()`` and
``parent::tearDown()`` methods respectively to configure the ``StreamFilterTrait``.

CITestStreamFilter
------------------

**CITestStreamFilter** for manual/single use.

If you need to capture streams in only one test, then instead of using the StreamFilterTrait trait, you can manually
add a filter to streams.

**Overview of methods**

- ``CITestStreamFilter::registration()`` Filter registration.
- ``CITestStreamFilter::addOutputFilter()`` Adding a filter to the output stream.
- ``CITestStreamFilter::addErrorFilter()`` Adding a filter to the error stream.
- ``CITestStreamFilter::removeOutputFilter()`` Removing a filter from the output stream.
- ``CITestStreamFilter::removeErrorFilter()`` Removing a filter from the error stream.

.. literalinclude:: overview/020.php

.. _testing-cli-input:

Testing CLI Input
=================

PhpStreamWrapper
----------------

.. versionadded:: 4.3.0

**PhpStreamWrapper** provides a way to write tests for methods that require user input,
such as ``CLI::prompt()``, ``CLI::wait()``, and ``CLI::input()``.

.. note:: The PhpStreamWrapper is a stream wrapper class.
    If you don't know PHP's stream wrapper,
    see `The streamWrapper class <https://www.php.net/manual/en/class.streamwrapper.php>`_
    in the PHP maual.

**Overview of methods**

- ``PhpStreamWrapper::register()`` Register the ``PhpStreamWrapper`` to the ``php`` protocol.
- ``PhpStreamWrapper::restore()`` Restore the php protocol wrapper back to the PHP built-in wrapper.
- ``PhpStreamWrapper::setContent()`` Set the input data.

.. important:: The PhpStreamWrapper is intended for only testing ``php://stdin``.
    But when you register it, it handles all the `php protocol <https://www.php.net/manual/en/wrappers.php.php>`_ streams,
    such as ``php://stdout``, ``php://stderr``, ``php://memory``.
    So it is strongly recommended that ``PhpStreamWrapper`` be registered/unregistered
    only when needed. Otherwise, it will interfere with other built-in php streams
    while registered.

An example demonstrating this inside one of your test cases:

.. literalinclude:: overview/019.php
