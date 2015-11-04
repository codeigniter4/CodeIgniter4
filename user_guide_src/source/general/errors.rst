##################
Errors and Logging
##################

Logging
=======

You can log information to the local log files by using the ``log_message()`` method. You must supply
the "level" of the error in the first parameter, indicating what type of message it is (debug, error, etc).
The second parameter is the message itself::

	if ($some_var == '')
	{
		log_message('error', 'Some variable did not contain a value.');
	}

There are eight different log levels, matching to the `RFC 5424 <http://tools.ietf.org/html/rfc5424>`_ levels, and they are as follows:

* debug - Detailed debug information
* info - Interesting events in your application, like a user logging in, logging SQL queries, etc. 
* notice - Normal, but significant events in your application.
* warning - Exceptional occurrences that are not errors, like the user of deprecated APIs, poor use of an API, or other undesirable things that are not necessarily wrong.
* error - Runtime errors that do not require immediate action but should typically be logged and monitored.
* critical - Critical conditions, like an application component not available, or an unexpected exception.
* alert - Action must be taken immediately, like when an entire website is down, the database unavailable, etc. 
* emergency - The system is unusable.

The logging system does not provide ways to alert sysadmins or webmasters about these events, they solely log
the information. For many of the more critical event levels, the logging happens automatically by the
Error Handler, described above.

Modifying the Message With Context
==================================

You will often want to modify the details of your message based on the context of the event being logged.
You might need to log a user id, an IP address, the current POST variables, etc. You can do this by use
placeholders in your message. Each placeholder must be wrapped in curly braces. In the third parameter,
you must provide an array of placeholder names (without the braces) and their values. These will be inserted
into the message string::

	// Generates a message like: User 123 logged into the system from 127.0.0.1
	$info = [
		'id' => $user->id,
		'ip_address' => $this->request->ip_address()
	];

	log_message('info', 'User {id} logged into the system from {ip_address}', $info);

If you want to log an Exception or an Error, you can use the key of 'exception', and the value being the
Exception or Error itself. A string will be generated from that object containing the error message, the
file name and line number.  You must still provide the exception placeholder in the message::

	try 
	{
		... Something throws error here
	}
	catch (\Exception #e)
	{
		log_message('error', '[ERROR] {exception}', ['exception' => $e]);
	}

Three placeholders will be automatically expanded for you to display the contents of the $_POST, $_GET,
and $_SESSION values. They are `{post_vars}`, `{get_vars}`, and `{session_vars}`, respectively.

Using Third-Party Loggers
=========================

You can use any other logger that you might like as long as it extends from either
``PSR\Log\LoggerInterface`` and is `PSR3 <http://www.php-fig.org/psr/psr-3/>`_ compatible. This means
that you can easily drop in use for any PSR3-compatible logger, or create your own.

You must ensure that the third-party logger can be found by the system, by adding it to either
the ``/application/config/autoload.php`` configuration file, or through another autoloader,
like Composer. Next, you should modify ``/application/config/services.php`` to point the ``logger``
alias to your new class name.

Now, any call that is done through the ``log_message()`` function will use your library instead.

LoggerAware Trait
=================

If you would like to implement your libraries in a framework-agnostic method, you can use
the ``CodeIgniter\Log\LoggerAwareTrait`` which implements the ``setLogger()`` method for you.
Then, when you use your library under different environments for frameworks, your library should
still be able to log as it would expect, as long as it can find a PSR3 compatible logger.




