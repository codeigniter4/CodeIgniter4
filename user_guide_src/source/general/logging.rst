###################
Logging Information
###################

.. contents::
    :local:
    :depth: 2

Log Levels
==========

You can log information to the local log files by using the :php:func:`log_message()` method. You must supply
the "level" of the error in the first parameter, indicating what type of message it is (debug, error, etc).
The second parameter is the message itself:

.. literalinclude:: logging/001.php

There are eight different log levels, matching to the `RFC 5424 <https://tools.ietf.org/html/rfc5424>`_ levels, and they are as follows:

=========== ==================================================================
Level       Description
=========== ==================================================================
debug       Detailed debug information.
info        Interesting events in your application, like a user logging in, logging SQL queries, etc.
notice      Normal, but significant events in your application.
warning     Exceptional occurrences that are not errors, like the use of deprecated APIs,
            poor use of an API, or other undesirable things that are not necessarily wrong.
error       Runtime errors that do not require immediate action but should typically be logged and monitored.
critical    Critical conditions, like an application component not available, or an unexpected exception.
alert       Action must be taken immediately, like when an entire website is down, the database unavailable, etc.
emergency   The system is unusable.
=========== ==================================================================

The logging system does not provide ways to alert sysadmins or webmasters about these events, they solely log
the information. For many of the more critical event levels, the logging happens automatically by the
Error Handler, described above.

.. _logging-configuration:

Configuration
=============

You can modify which levels are actually logged, as well as assign different Loggers to handle different levels, within
the **app/Config/Logger.php** configuration file.

The ``threshold`` value of the config file determines which levels are logged across your application. If any levels
are requested to be logged by the application, but the threshold doesn't allow them to log currently, they will be
ignored. The simplest method to use is to set this value to the minimum level that you want to have logged. For example,
if you want to log warning messages, and not information messages, you would set the threshold to ``5``. Any log requests with
a level of 5 or less (which includes runtime errors, system errors, etc) would be logged and info, notices, and debug
would be ignored:

.. literalinclude:: logging/002.php

A complete list of levels and their corresponding threshold value is in the configuration file for your reference.

You can pick and choose the specific levels that you would like logged by assigning an array of log level numbers
to the threshold value:

.. literalinclude:: logging/003.php

Using Multiple Log Handlers
---------------------------

The logging system can support multiple methods of handling logging running at the same time. Each handler can
be set to handle specific levels and ignore the rest. Currently, three handlers come with a default install:

- **File Handler** is the default handler and will create a single file for every day locally. This is the
  recommended method of logging.
- **ChromeLogger Handler** If you have the `ChromeLogger extension <https://craig.is/writing/chrome-logger>`_
  installed in the Chrome web browser, you can use this handler to display the log information in
  Chrome's console window.
- **Errorlog Handler** This handler will take advantage of PHP's native ``error_log()`` function and write
  the logs there. Currently, only the ``0`` and ``4`` message types of ``error_log()`` are supported.

The handlers are configured in the main configuration file, in the ``$handlers`` property, which is simply
an array of handlers and their configuration. Each handler is specified with the key being the fully
name-spaced class name. The value will be an array of varying properties, specific to each handler.
Each handler's section will have one property in common: ``handles``, which is an array of log level
*names* that the handler will log information for.

.. literalinclude:: logging/004.php

Modifying the Message with Context
==================================

You will often want to modify the details of your message based on the context of the event being logged.
You might need to log a user id, an IP address, the current POST variables, etc. You can do this by use
placeholders in your message. Each placeholder must be wrapped in curly braces. In the third parameter,
you must provide an array of placeholder names (without the braces) and their values. These will be inserted
into the message string:

.. literalinclude:: logging/005.php

If you want to log an Exception or an Error, you can use the key of 'exception', and the value being the
Exception or Error itself. A string will be generated from that object containing the error message, the
file name and line number. You must still provide the exception placeholder in the message:

.. literalinclude:: logging/006.php

Several core placeholders exist that will be automatically expanded for you based on the current page request:

+----------------+---------------------------------------------------+
| Placeholder    | Inserted value                                    |
+================+===================================================+
| {post_vars}    | $_POST variables                                  |
+----------------+---------------------------------------------------+
| {get_vars}     | $_GET variables                                   |
+----------------+---------------------------------------------------+
| {session_vars} | $_SESSION variables                               |
+----------------+---------------------------------------------------+
| {env}          | Current environment name, i.e., development       |
+----------------+---------------------------------------------------+
| {file}         | The name of file calling the logger               |
+----------------+---------------------------------------------------+
| {line}         | The line in {file} where the logger was called    |
+----------------+---------------------------------------------------+
| {env:foo}      | The value of 'foo' in $_ENV                       |
+----------------+---------------------------------------------------+

Using Third-Party Loggers
=========================

You can use any other logger that you might like as long as it extends from either
``Psr\Log\LoggerInterface`` and is `PSR-3 <https://www.php-fig.org/psr/psr-3/>`_ compatible. This means
that you can easily drop in use for any PSR-3 compatible logger, or create your own.

You must ensure that the third-party logger can be found by the system, by adding it to either
the **app/Config/Autoload.php** configuration file, or through another autoloader,
like Composer. Next, you should modify **app/Config/Services.php** to point the ``logger``
alias to your new class name.

Now, any call that is done through the :php:func:`log_message()` function will use your library instead.
