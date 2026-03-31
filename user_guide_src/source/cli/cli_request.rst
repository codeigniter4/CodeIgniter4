****************
CLIRequest Class
****************

If a request comes from a command line invocation, the request object is actually a
``CLIRequest``. It behaves the same as a :doc:`conventional request </incoming/request>`
but adds some accessor methods for convenience.

====================
Additional Accessors
====================

getSegments()
-------------

Returns an array of the command line arguments deemed to be part of a path:

.. literalinclude:: cli_request/001.php

getPath()
---------

Returns the reconstructed path as a string:

.. literalinclude:: cli_request/002.php

getOptions()
------------

Returns an array of the command line arguments deemed to be options:

.. literalinclude:: cli_request/003.php

getOption($key)
-----------------

Returns the value of a specific command line argument deemed to be an option:

.. literalinclude:: cli_request/004.php

.. note:: Starting in v4.8.0, if the option you are trying to access is an array, this method will return
    the last value in the array. Use ``getRawOption()`` to get the full array of values for that option.

getRawOption($key)
------------------

.. versionadded:: 4.8.0

Similar to ``getOption()``, but returns the full array of values for the option if it is an array:

.. literalinclude:: cli_request/007.php

getOptionString()
-----------------

Returns the reconstructed command line string for the options:

.. literalinclude:: cli_request/005.php

Passing ``true`` to the first argument will try to write long options using two dashes:

.. literalinclude:: cli_request/006.php
