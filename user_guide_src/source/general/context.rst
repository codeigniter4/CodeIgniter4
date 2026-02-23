.. _context:

###################
Context
###################

.. versionadded:: 4.8.0

.. contents::
    :local:
    :depth: 2

***********
What is it?
***********

The Context class provides a simple, convenient way to store and retrieve user-defined data throughout a single request. It functions as a key-value store that can hold any data you need to access across different parts of your application during the request lifecycle.

The Context class is particularly useful for:

- Storing request-specific metadata (user IDs, request IDs, correlation IDs)
- Passing data between filters, controllers, and other components
- Adding contextual information to your logs automatically
- Storing sensitive data that should not appear in logs

***********************
Accessing Context Class
***********************

You can access the Context service anywhere in your application using the ``service()`` function or ``context()`` helper:

.. literalinclude:: context/001.php

*********************
Setting Context Data
*********************

Setting a Single Value
======================

You can store a single key-value pair using the ``set()`` method:

.. literalinclude:: context/002.php

Setting Multiple Values
=======================

You can also set multiple values at once by passing an array:

.. literalinclude:: context/003.php

The ``set()`` method returns the Context instance, allowing you to chain multiple calls:

.. literalinclude:: context/004.php

*********************
Getting Context Data
*********************

Retrieving a Single Value
==========================

Use the ``get()`` method to retrieve a value by its key:

.. literalinclude:: context/005.php

You can provide a default value as the second parameter, which will be returned if the key doesn't exist:

.. literalinclude:: context/006.php

Retrieving All Data
===================

To get all stored context data:

.. literalinclude:: context/007.php

Retrieving Specific Keys
=========================

You can retrieve only specific keys using ``getOnly()``:

.. literalinclude:: context/008.php

If you need all data except specific keys, use ``getExcept()``:

.. literalinclude:: context/009.php

**********************
Checking for Data
**********************

You can check if a key exists in the context:

.. literalinclude:: context/010.php

*********************
Removing Context Data
*********************

Removing a Single Value
========================

You can remove data from the context using the ``remove()`` method:

.. literalinclude:: context/011.php

Removing Multiple Values
=========================

To remove multiple keys at once, pass an array:

.. literalinclude:: context/012.php

Clearing All Data
=================

To remove all context data:

.. literalinclude:: context/013.php

*********************
Hidden Context Data
*********************

The Context class provides a separate storage area for sensitive data that should not be included in logs.
This is useful for storing API keys, passwords, tokens, or other sensitive information that you need to access
during the request but don't want to expose in log files.

Setting Hidden Data
===================

Use the ``setHidden()`` method to store sensitive data:

.. literalinclude:: context/014.php

You can also set multiple hidden values at once:

.. literalinclude:: context/015.php

Getting Hidden Data
===================

Retrieve hidden data using ``getHidden()``:

.. literalinclude:: context/016.php

The same methods available for regular data also work with hidden data:

.. literalinclude:: context/017.php

Checking Hidden Data
====================

Check if a hidden key exists:

.. literalinclude:: context/018.php

Removing Hidden Data
====================

Remove hidden data using ``removeHidden()``:

.. literalinclude:: context/019.php

Clearing Hidden Data
====================

To clear all hidden data without affecting regular context data:

.. literalinclude:: context/020.php

To clear both regular and hidden data:

.. literalinclude:: context/021.php

.. important:: Regular data and hidden data are stored separately. A key can exist in both regular and hidden storage with different values. Use ``get()`` for regular data and ``getHidden()`` for hidden data.

***********************************
Integration with Logging
***********************************

The Context class integrates seamlessly with CodeIgniter's logging system. When enabled, context data is automatically
appended to log messages, providing additional information for debugging and monitoring.

Enabling Global Context Logging
================================

To enable automatic logging of context data, set the ``$logGlobalContext`` property to ``true`` in your
**app/Config/Logger.php** file:

.. literalinclude:: context/022.php

When enabled, all context data (excluding hidden data) will be automatically appended to your log messages as JSON:

.. literalinclude:: context/023.php

This would produce a log entry like:

.. code-block:: text

    ERROR - 2026-02-18 --> Payment processing failed {"user_id":123,"transaction_id":"txn_12345"}

.. note:: Hidden data set with ``setHidden()`` is **never** included in logs, even when ``$logGlobalContext`` is enabled. This ensures sensitive information like API keys or tokens remain secure.

***************
Important Notes
***************

- Context data persists only for the duration of a single request. It is not shared between requests.
- The Context service is shared by default, meaning there is one instance per request.
- Hidden data is never included in logs, regardless of the logging configuration.
- Regular context data and hidden context data are stored separately and can have overlapping keys.
- Context is cleared automatically at the end of each request.
- In testing environments, remember to clear context data between tests using ``clearAll()`` to ensure test isolation.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Context

.. php:class:: Context

    .. php:method:: set($key[, $value = null])

        :param array|string $key: The key or an array of key-value pairs
        :param mixed $value: The value to store (ignored if $key is an array)
        :returns: Context instance for method chaining
        :rtype: Context

        Sets one or more key-value pairs in the context.

    .. php:method:: setHidden($key[, $value = null])

        :param array|string $key: The key or an array of key-value pairs
        :param mixed $value: The value to store (ignored if $key is an array)
        :returns: Context instance for method chaining
        :rtype: Context

        Sets one or more key-value pairs in the hidden context.

    .. php:method:: get($key[, $default = null])

        :param string $key: The key to retrieve
        :param mixed $default: Default value if key doesn't exist
        :returns: The value or default
        :rtype: mixed

        Gets a value from the context.

    .. php:method:: getHidden($key[, $default = null])

        :param string $key: The key to retrieve
        :param mixed $default: Default value if key doesn't exist
        :returns: The value or default
        :rtype: mixed

        Gets a value from the hidden context.

    .. php:method:: getOnly($keys)

        :param array|string $keys: Key or array of keys to retrieve
        :returns: Array of key-value pairs
        :rtype: array

        Gets only the specified keys from the context.

    .. php:method:: getOnlyHidden($keys)

        :param array|string $keys: Key or array of keys to retrieve
        :returns: Array of key-value pairs
        :rtype: array

        Gets only the specified keys from the hidden context.

    .. php:method:: getExcept($keys)

        :param array|string $keys: Key or array of keys to exclude
        :returns: Array of key-value pairs
        :rtype: array

        Gets all context data except the specified keys.

    .. php:method:: getExceptHidden($keys)

        :param array|string $keys: Key or array of keys to exclude
        :returns: Array of key-value pairs
        :rtype: array

        Gets all hidden context data except the specified keys.

    .. php:method:: getAll()

        :returns: All context data
        :rtype: array

        Gets all data from the context.

    .. php:method:: getAllHidden()

        :returns: All hidden context data
        :rtype: array

        Gets all data from the hidden context.

    .. php:method:: has($key)

        :param string $key: The key to check
        :returns: True if key exists, false otherwise
        :rtype: bool

        Checks if a key exists in the context.

    .. php:method:: hasHidden($key)

        :param string $key: The key to check
        :returns: True if key exists, false otherwise
        :rtype: bool

        Checks if a key exists in the hidden context.

    .. php:method:: missing($key)

        :param string $key: The key to check
        :returns: True if key doesn't exist, false otherwise
        :rtype: bool

        Checks if a key doesn't exist in the context. Opposite of ``has()``.

    .. php:method:: missingHidden($key)

        :param string $key: The key to check
        :returns: True if key doesn't exist, false otherwise
        :rtype: bool

        Checks if a key doesn't exist in the hidden context. Opposite of ``hasHidden()``.

    .. php:method:: remove($key)

        :param array|string $key: The key or array of keys to remove
        :returns: Context instance for method chaining
        :rtype: Context

        Removes one or more keys from the context.

    .. php:method:: removeHidden($key)

        :param array|string $key: The key or array of keys to remove
        :returns: Context instance for method chaining
        :rtype: Context

        Removes one or more keys from the hidden context.

    .. php:method:: clear()

        :returns: Context instance for method chaining
        :rtype: Context

        Clears all data from the context (does not affect hidden data).

    .. php:method:: clearHidden()

        :returns: Context instance for method chaining
        :rtype: Context

        Clears all data from the hidden context (does not affect regular data).

    .. php:method:: clearAll()

        :returns: Context instance for method chaining
        :rtype: Context

        Clears all data from both the context and hidden context.
