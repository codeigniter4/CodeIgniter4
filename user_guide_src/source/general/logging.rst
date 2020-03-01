###################
Logging Information
###################

CodeIgniter logging is a PSR-3 compliant logger interface that allows writing messages to multiple destinations. 
This is accomplished by using *handler* classes for each message destination. Currently, two logging handlers come with CodeIgniter.

- **FileHandler** performs the logging task the traditional way, by writing to a local file.

- **ChromeLoggerHandler** sends messages to Chrome browsers.  The `Chrome Logger Extension <https://chrome.google.com/webstore/detail/chrome-logger/noaneddfkdjfnfdakjjmocngnfkfehhd>`_ must be installed on your Chrome browser. When installed, it allows debugging server-side applications using the Chrome javascript console.

All handlers support more than just a basic string of text but can also use variables and objects as the "message" or as the "context" of a message. Nearly all data types, from simple integers to complex objects, are displayed in a human-readable form for easy analysis.

.. contents::
    :local:
    :depth: 2

The Logger Class
================
The ``Logger`` class is readily available through a call to ``Services``
::

    $logger = \Config\Services::logger();

But you might not even need that simple call. Some often-used classes already have a Logger instance established. Every ``Controller``, for example, has a ``Logger`` instance available in its ``$logger`` property. So anywhere in any controller, you could do something like this.
::

    // message modified with "context"
    $this->logger->debug("The value of id is {id}", ['id' => $id]);  

The ``Session`` and ``View`` classes also contain a ``$logger`` property.

.. _levels:

There are eight different message severity levels, matching `RFC 5424 <http://tools.ietf.org/html/rfc5424>`_ :

* **emergency** - The system is unusable.
* **alert** - Action must be taken immediately, like when an entire website is down, the database unavailable, etc.
* **critical** - Critical conditions, like an application component not available, or an unexpected exception.
* **error** - Runtime errors that do not require immediate action but should typically be logged and monitored.
* **warning** - Exceptional occurrences that are not errors, like the use of deprecated APIs, poor use of an API, or other undesirable things that are not necessarily wrong.
* **notice** - Normal, but significant events in your application.
* **info** - Interesting events in your application, like a user logging in, logging SQL queries, etc.
* **debug** - Detailed debug information.

The ``Logger`` class lets you log information in many different ways. 
The first eight methods are named after the eight severity levels.
::

$this->logger->emergency('There be Dragons!');
$this->logger->alert('You don\'t seem very alert');
$this->logger->critical('I mean to be critical');
$this->logger->error('There was a error.');
$this->logger->warning('Warning the sign has sharp edges');
$this->logger->notice('Did you notice my new shoes?');
$this->logger->info('For your information...');
$this->logger->debug('This is a debug message.');

All of the above support an optional second parameter providing the *context* for the message. 
Read about how that works in :ref:`context` .

The ninth logging method lets you use an arbitrary level.
::

$this->logger->log($level, $message, $context);

In that example, the first parameter ``$level`` is the *name* of the :ref:`severity level <levels>`. 
You probably already guessed what ``$message`` is for, but you should know it must evaluate to a string. 
The third parameter,  ``$context``, must be an array, but the array can contain any 
type of data - number, string, object, etc.
Details on how this context data is expressed in a log message are explained in :ref:`context`.

The tenth way to create a log entry is by using the :doc:`Global Function <common_functions>` ``log_message()``
which is convenient because you don't need an instance of ``Logger`` at hand. It can
be called from anywhere.

In the first parameter, you must supply a string indicating one of the eight severity levels. 
The second parameter is the message itself.
::

	if (! $some_thing)
	{
		log_message('error', 'Something blowed up real good.');
	}

Just like all other ``Logger`` methods *context* can be applied to ``log_message()``, 
In this case, as the third parameter.

Logger Configuration
=====================

You will find all configuration settings for the ``Logger`` class along with settings 
for the related *handlers* in `app/Config/Logger.php`. The first section of the file 
sets up ``FileHander`` properties, the second section sets the ``ChromeLoggerHandler``, 
and the third is for properties that all associated log classes share.

But before we get into details on each configuration section, let's talk about setting log levels.

.. _setlevels:

Setting the Log Level
---------------------

The ``FileHander`` and ``ChromeLoggerHandler`` have individual log level settings which means
it is possible to do things like have the file handler log `error` through `emergency` 
level messages while the ChromeLogger is sending only `debug` messages. 

The two properties that control each handler are:

 - ``$fileLevelsHandled`` sets levels for the ``FilerHandler`` 
 - ``$chromeLoggerLevelsHandled`` sets levels for the ``ChromeLoggerHandler``


The values set determines which of the eight levels will actually be logged. 
Any call to log a message at a level not in those properties is ignored.

Here are the acceptable numerical values used for setting logging severity levels:

- 0 = off (no logging from the related handler)
- 1 = 'emergency'
- 2 = 'alert'
- 3 = 'critical'
- 4 = 'error'
- 5 = 'warning'
- 6 = 'notice'
- 7 = 'info'
- 8 = 'debug'

As you see, setting a value of 0 (zero) turns the handler off. You can enable logging by 
setting a value in the range 1-8. 

Not setting a value (leaving it blank), or setting a value not in the acceptable range will cause an exception to be thrown.

If a single value is supplied all log levels from 1 to the supplied value will be logged. 
In the following example that would mean that *emergency*, *alert*, and *critical*  
messages would be the only levels logged.
::

    public $levelsHandled = 3;

You can pick and choose the specific levels that you want to be logged by assigning an array of numbers.
For instance, the following could be used to log only  *emergency*, *critical*, and *debug* messages.
::

    public $levelsHandled = [1, 3, 8];

The order of the values in the array makes no difference.
 
If you put a zero anywhere in the array, the effect is the same as using a single zero. 
All other values in the array are ignored, and the handler is effectively turned off.

If you put a single value in an array then only that level will be logged.
For instance, by using the following only 'debug' messages are logged.
::

    public $levelsHandled = [8];

Configuring the FileHandler
---------------------------

The ``FileHandler`` class has these configurable items.

Using the property ``$fileLevelsHandled``  is discussed in :ref:`setlevels`. 

``$logsDir`` is the absolute path to the directory where logfiles are saved. The
default is ``WRITEPATH . 'logs/'``.

``$fileName`` is actually only the prefix to the log's file name. 
The default prefix is `'CI_'`. The prefix is followed by the date in the `'Y-m-d H:i:s.'`
format, which in turn is followed by the file extention. (e.g. `CI_2020-03-01.log`) 
The logger creates one file for each day.

``$fileExtension`` is the file extension and the  default is  ".log".

``$filePermissions`` is the file system permissions applied to newly created log files. 
This **must** be an integer (no quotes) using octal notation, e.g. 0700, 0644, etc.

Configuring the ChromeLoggerHandler
-----------------------------------

Using the property ``$chromeLoggerLevelsHandled`` is discussed in :ref:`setlevels`. 

The "main switch" for the Chrome Logger is the property ``$enableChromeLogger``.
When it is set to `false` the ``ChromeLoggerHandler`` will not run, in fact, the class won't even be created.

It's very important to realize that the Chrome Logger could send potentially 
sensitive information from the server to a browser. For that reason, the ``ChromeLoggerHandler`` 
will only send data to the browser if both of the following are true.

    1. ``$enableChromeLogger`` is set to `true`
    2. ``ENVIRONMENT`` is set to `'development'`

General Logger Settings
-----------------------
The only general setting you might want to change is ``$dateFormat`` which holds a format string 
using the `Date and Time Formats <https://www.php.net/manual/en/datetime.formats.date.php>`_ supported by PHP.

``$dateFormat`` is used with log message entries as you can see in this typical log file entry
using the default 'Y-m-d H:i:s' format.
::

	DEBUG - 2020-02-23 19:32:37 --> Your Message Here

The ``$handlers`` property is a list of handler classes that ``Logger`` will instantiate.
Handlers run sequentially in the order defined. The array key of each item is the 
handler's class name and the value is a `fully qualified 
class name <https://www.php.net/manual/en/language.oop5.basic.php#language.oop5.basic.class.class>`_ constant.
::

	public $handlers = [
		'FileHandler'         => \CodeIgniter\Log\Handlers\FileHandler::class,
		'ChromeLoggerHandler' => \CodeIgniter\Log\Handlers\ChromeLoggerHandler::class,
	];

You probably don't need to change anything here. If you plan on never using one 
of the handlers you could remove it by deleting or commenting it. There has to
be at least one handler available or an exception will be thrown.


.. _context:

Modifying the Message With Context
==================================

You will often want to modify the details of your message based on the `context` of the event being logged.
You might need to log a user id, an IP address, the current POST variables, etc. 

One way to do this by using placeholders in your message string. Each placeholder must be wrapped in curly braces. The ``$context`` parameter is an array of placeholder names (without the braces) and their values. The values will replace the `{key}` placeholder.
::

	$context = [
	    'id' => $user->id,
	    'ip_address' => $this->request->getIPAddress()
	];

	log_message('info', 'User {id} logged into the system from {ip_address}', $context);
	// Generates a message like: User 123 logged into the system from 127.0.0.1

.. note:: The example uses an associative array, but an indexed array can be used too. The placeholder simply uses the key index number instead of the key name. For instance, if ``$context`` was an indexed array (i.e. ``[$user->id, $ipAddress]``) the call would be changed to
    ``log_message('info', 'User {0} logged into the system from {1}', $context);``.

``Logger`` does it's best to parse and display information about complex data types it receives.
Consider this example.
::
    
    $user = new \stdClass();
    $user->name = 'Dent, Arthur';
    $user->id = '42';
    $this->logger->debug($user);

Will produce the log entry:
::

    DEBUG - 2020-02-15 11:45:31 --> stdClass Object
    (
	[name] => Dent, Arthur
	[id] => 42
    )

You have to change the approach a little bit if you want to use ``log_message()``.
The following will produce the same output as the previous example. But in this case we have to use *context*.
::

    // $user is the same as previous example
    log_message('debug', '{0}', [$user]);

Admittedly, ``$user`` isn't all that complex, but ``Logger`` can handle large, complex objects
and display their details in a readable form.

If you want to log an Exception, you can use the key of 'exception', with the value being the
Exception itself. A string will be generated from that object containing the 
exception message, file name and line number. 
All that you need to do is provide the {exception} placeholder in the message.
::

	try
	{
	    throw new \Exception('Division by zero.');
	}
	catch (\Exception $e)
	{
	    log_message('error', '{exception}', ['exception' => $e]);
	}

Which will produce a log entry that look something like this.

	ERROR - 2020-02-29 11:35:58 --> Division by zero. APPPATH/Controllers/Home.php:43

Several core placeholders exist that will be automatically expanded for you based on the current request:

+----------------+---------------------------------------------------+
| Placeholder    | Inserted value                                    |
+================+===================================================+
| {post_vars}    | $_POST array                                      |
+----------------+---------------------------------------------------+
| {get_vars}     | $_GET array                                       |
+----------------+---------------------------------------------------+
| {session_vars} | $_SESSION array                                   |
+----------------+---------------------------------------------------+
| {env}          | Current environment name, i.e. development        |
+----------------+---------------------------------------------------+
| {file}         | The name of file calling the logger               |
+----------------+---------------------------------------------------+
| {line}         | The line in {file} where the logger was called    |
+----------------+---------------------------------------------------+
| {env:foo}      | The value of 'foo' in $_ENV                       |
+----------------+---------------------------------------------------+

Using Third-Party Loggers
=========================

You can use any other logger that you like as long as it extends from either
``Psr\Log\LoggerInterface`` or is `PSR3 <http://www.php-fig.org/psr/psr-3/>`_ compatible. 
This means that you can drop in any PSR3-compatible logger, including one of your own creations.

You must ensure that the third-party logger can be found by the system, by adding it to either
the ``/app/Config/Autoload.php`` configuration file or through another autoloader,
like Composer. Next, you should modify ``/app/Config/Services.php`` to point the ``logger``
alias to your new class name.

Creating Handlers
=================

Handlers MUST implement ``CodeIgniter\Log\Handlers\HandlerInterface``.

The custom handler must also be added to the ``$handlers`` property in the Logger config file
and use the same key=value naming convention found there. 

The only required Handler configuration item is the ``${className}levelsHandled`` property,
the same as exists for the other handlers. (i.e. ``$fileLevelsHandled``)

Custom handlers **must** extend ``CodeIgniter\Log\Handlers\BaseHandler``.


LoggerAware Trait
=================

If you would like to implement your custom classes in a framework-agnostic way you can use
the ``CodeIgniter\Log\LoggerAwareTrait``. The trait defines a ``setLogger()`` method for you.
Then, as long as it can find a PSR3 compatible logger, when you use your class under 
different environments it should be able to log as it expects.

