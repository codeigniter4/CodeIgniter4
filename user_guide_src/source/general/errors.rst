##############
Error Handling
##############

CodeIgniter builds error reporting into your system through Exceptions, both the `SPL collection <https://www.php.net/manual/en/spl.exceptions.php>`_, as
well as a few custom exceptions that are provided by the framework. Depending on your environment's setup,
the default action when an error or exception is thrown is to display a detailed error report unless the application
is running under the ``production`` environment. In this case, a more generic message is displayed to
keep the best user experience for your users.

.. contents::
    :local:
    :depth: 2

Using Exceptions
================

This section is a quick overview for newer programmers, or for developers who are not experienced with using exceptions.

Exceptions are simply events that happen when the exception is "thrown". This halts the current flow of the script, and
execution is then sent to the error handler which displays the appropriate error page:

.. literalinclude:: errors/001.php

If you are calling a method that might throw an exception, you can catch that exception using a ``try/catch`` block:

.. literalinclude:: errors/002.php

If the ``$userModel`` throws an exception, it is caught and the code within the catch block is executed. In this example,
the scripts dies, echoing the error message that the ``UserModel`` defined.

In the example above, we catch any type of Exception. If we only want to watch for specific types of exceptions, like
a ``UnknownFileException``, we can specify that in the catch parameter. Any other exceptions that are thrown and are
not child classes of the caught exception will be passed on to the error handler:

.. literalinclude:: errors/003.php

This can be handy for handling the error yourself, or for performing cleanup before the script ends. If you want
the error handler to function as normal, you can throw a new exception within the catch block:

.. literalinclude:: errors/004.php

Configuration
=============

By default, CodeIgniter will display all errors in the ``development`` and ``testing`` environments, and will not
display any errors in the ``production`` environment. You can change this by setting the ``CI_ENVIRONMENT`` variable
in the **.env** file.

.. important:: Disabling error reporting DOES NOT stop logs from being written if there are errors.

Logging Exceptions
------------------

By default, all Exceptions other than 404 - Page Not Found exceptions are logged. This can be turned on and off
by setting the ``$log`` value of **app/Config/Exceptions.php**:

.. literalinclude:: errors/005.php

To ignore logging on other status codes, you can set the status code to ignore in the same file:

.. literalinclude:: errors/006.php

.. note:: It is possible that logging still will not happen for exceptions if your current Log settings
    are not set up to log **critical** errors, which all exceptions are logged as.

Custom Exceptions
=================

The following custom exceptions are available:

PageNotFoundException
---------------------

This is used to signal a 404, Page Not Found error. When thrown, the system will show the view found at
**app/Views/errors/html/error_404.php**. You should customize all of the error views for your site.
If, in **app/Config/Routes.php**, you have specified a 404 Override, that will be called instead of the standard
404 page:

.. literalinclude:: errors/007.php

You can pass a message into the exception that will be displayed in place of the default message on the 404 page.

ConfigException
---------------

This exception should be used when the values from the configuration class are invalid, or when the config class
is not the right type, etc:

.. literalinclude:: errors/008.php

This provides an HTTP status code of 500 and an exit code of 3.

DatabaseException
-----------------

This exception is thrown for database errors, such as when the database connection cannot be created,
or when it is temporarily lost:

.. literalinclude:: errors/009.php

This provides an HTTP status code of 500 and an exit code of 8.

RedirectException
-----------------

This exception is a special case allowing for overriding of all other response routing and
forcing a redirect to a specific route or URL:

.. literalinclude:: errors/010.php

``$route`` may be a named route, relative URI, or a complete URL. You can also supply a
redirect code to use instead of the default (``302``, "temporary redirect"):

.. literalinclude:: errors/011.php
