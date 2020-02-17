##############
Error Handling
##############

CodeIgniter builds error reporting into your system through Exceptions, both the `SPL collection <http://php.net/manual/en/spl.exceptions.php>`_, as
well as a few custom exceptions that are provided by the framework. Depending on your environment's setup, the
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
execution is then sent to the error handler which displays the appropriate error page::

	throw new \Exception("Some message goes here");

If you are calling a method that might throw an exception, you can catch that exception using a ``try/catch`` block::

	try {
		$user = $userModel->find($id);
	}
	catch (\Exception $e)
	{
		die($e->getMessage());
	}

If the ``$userModel`` throws an exception, it is caught and the code within the catch block is executed. In this example,
the scripts dies, echoing the error message that the ``UserModel`` defined.

In this example, we catch any type of Exception. If we only want to watch for specific types of exceptions, like
a UnknownFileException, we can specify that in the catch parameter. Any other exceptions that are thrown and are
not child classes of the caught exception will be passed on to the error handler::

	catch (\CodeIgniter\UnknownFileException $e)
	{
		// do something here...
	}

This can be handy for handling the error yourself, or for performing cleanup before the script ends. If you want
the error handler to function as normal, you can throw a new exception within the catch block::

	catch (\CodeIgniter\UnknownFileException $e)
	{
		// do something here...

		throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
	}

Configuration
=============

By default, CodeIgniter will display all errors in the ``development`` and ``testing`` environments, and will not
display any errors in the ``production`` environment. You can change this by setting the ``CI_ENVIRONMENT`` variable
in the ``.env`` file.

.. important:: Disabling error reporting DOES NOT stop logs from being written if there are errors.

Logging Exceptions
------------------

By default, all Exceptions other than 404 - Page Not Found exceptions are logged. This can be turned on and off
by setting the **$log** value of ``Config\Exceptions``::

    class Exceptions
    {
        public $log = true;
    }

To ignore logging on other status codes, you can set the status code to ignore in the same file::

    class Exceptions
    {
        public $ignoredCodes = [ 404 ];
    }

.. note:: It is possible that logging still will not happen for exceptions if your current Log settings
    are not set up to log **critical** errors, which all exceptions are logged as.

Custom Exceptions
=================

The following custom exceptions are available:

PageNotFoundException
---------------------

This is used to signal a 404, Page Not Found error. When thrown, the system will show the view found at
``/app/views/errors/html/error_404.php``. You should customize all of the error views for your site.
If, in ``Config/Routes.php``, you have specified a 404 Override, that will be called instead of the standard
404 page::

	if (! $page = $pageModel->find($id))
	{
		throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
	}

You can pass a message into the exception that will be displayed in place of the default message on the 404 page.

ConfigException
---------------

This exception should be used when the values from the configuration class are invalid, or when the config class
is not the right type, etc::

	throw new \CodeIgniter\Exceptions\ConfigException();

This provides an HTTP status code of 500 and an exit code of 3.

DatabaseException
-----------------

This exception is thrown for database errors, such as when the database connection cannot be created,
or when it is temporarily lost::

	throw new \CodeIgniter\Database\Exceptions\DatabaseException();

This provides an HTTP status code of 500 and an exit code of 8.

RedirectException
-----------------

This exception is a special case allowing for overriding of all other response routing and
forcing a redirect to a specific route or URL::

	throw new \CodeIgniter\Router\Exceptions\RedirectException($route);

``$route`` may be a named route, relative URI, or a complete URL. You can also supply a
redirect code to use instead of the default (``302``, "temporary redirect")::

	throw new \CodeIgniter\Router\Exceptions\RedirectException($route, 301);
