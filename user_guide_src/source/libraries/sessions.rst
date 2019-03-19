###############
Session Library
###############

The Session class permits you to maintain a user's "state" and track their
activity while they browse your site.

CodeIgniter comes with a few session storage drivers, that you can see
in the last section of the table of contents:

.. contents::
    :local:
    :depth: 2

.. raw:: html

  <div class="custom-index container"></div>

Using the Session Class
*********************************************************************

Initializing a Session
==================================================================

Sessions will typically run globally with each page load, so the Session
class should be magically initialized.

To access and initialize the session::

	$session = \Config\Services::session($config);

The ``$config`` parameter is optional - your application configuration.
If not provided, the services register will instantiate your default
one.

Once loaded, the Sessions library object will be available using::

	$session

Alternatively, you can use the helper function that will use the default
configuration options. This version is a little friendlier to read,
but does not take any configuration options.
::

	$session = session();

How do Sessions work?
=====================

When a page is loaded, the session class will check to see if a valid
session cookie is sent by the user's browser. If a sessions cookie does
**not** exist (or if it doesn't match one stored on the server or has
expired) a new session will be created and saved.

If a valid session does exist, its information will be updated. With each
update, the session ID may be regenerated if configured to do so.

It's important for you to understand that once initialized, the Session
class runs automatically. There is nothing you need to do to cause the
above behavior to happen. You can, as you'll see below, work with session
data, but the process of reading, writing, and updating a session is
automatic.

.. note:: Under CLI, the Session library will automatically halt itself,
	as this is a concept based entirely on the HTTP protocol.

A note about concurrency
------------------------

Unless you're developing a website with heavy AJAX usage, you can skip this
section. If you are, however, and if you're experiencing performance
issues, then this note is exactly what you're looking for.

Sessions in previous versions of CodeIgniter didn't implement locking,
which meant that two HTTP requests using the same session could run exactly
at the same time. To use a more appropriate technical term - requests were
non-blocking.

However, non-blocking requests in the context of sessions also means
unsafe, because, modifications to session data (or session ID regeneration)
in one request can interfere with the execution of a second, concurrent
request. This detail was at the root of many issues and the main reason why
CodeIgniter 3.0 has a completely re-written Session library.

Why are we telling you this? Because it is likely that after trying to
find the reason for your performance issues, you may conclude that locking
is the issue and therefore look into how to remove the locks ...

DO NOT DO THAT! Removing locks would be **wrong** and it will cause you
more problems!

Locking is not the issue, it is a solution. Your issue is that you still
have the session open, while you've already processed it and therefore no
longer need it. So, what you need is to close the session for the
current request after you no longer need it.
::

    $session->destroy();

What is Session Data?
=====================

Session data is simply an array associated with a particular session ID
(cookie).

If you've used sessions in PHP before, you should be familiar with PHP's
`$_SESSION superglobal <http://php.net/manual/en/reserved.variables.session.php>`_
(if not, please read the content on that link).

CodeIgniter gives access to its session data through the same means, as it
uses the session handlers' mechanism provided by PHP. Using session data is
as simple as manipulating (read, set and unset values) the ``$_SESSION``
array.

In addition, CodeIgniter also provides 2 special types of session data
that are further explained below: flashdata and tempdata.

Retrieving Session Data
=======================

Any piece of information from the session array is available through the
``$_SESSION`` superglobal::

	$_SESSION['item']

Or through the conventional accessor method::

	$session->get('item');

Or through the magic getter::

	$session->item

Or even through the session helper method::

	session('item');

Where ``item`` is the array key corresponding to the item you wish to fetch.
For example, to assign a previously stored 'name' item to the ``$name``
variable, you will do this::

	$name = $_SESSION['name'];

	// or:

	$name = $session->name

	// or:

	$name = $session->get('name');

.. note:: The ``get()`` method returns NULL if the item you are trying
	to access does not exist.

If you want to retrieve all of the existing userdata, you can simply
omit the item key (magic getter only works for single property values)::

	$_SESSION

	// or:

	$session->get();

Adding Session Data
===================

Let's say a particular user logs into your site. Once authenticated, you
could add their username and e-mail address to the session, making that
data globally available to you without having to run a database query when
you need it.

You can simply assign data to the ``$_SESSION`` array, as with any other
variable. Or as a property of ``$session``.

The former userdata method is deprecated,
but you can pass an array containing your new session data to the
``set()`` method::

	$session->set($array);

Where ``$array`` is an associative array containing your new data. Here's
an example::

	$newdata = [
		'username'  => 'johndoe',
		'email'     => 'johndoe@some-site.com',
		'logged_in' => TRUE
	];

	$session->set($newdata);

If you want to add session data one value at a time, ``set()`` also
supports this syntax::

	$session->set('some_name', 'some_value');

If you want to verify that a session value exists, simply check with
``isset()``::

	// returns FALSE if the 'some_name' item doesn't exist or is NULL,
	// TRUE otherwise:
	isset($_SESSION['some_name'])

Or you can call ``has()``::

	$session->has('some_name');

Pushing new value to session data
=================================

The push method is used to push a new value onto a session value that is an array.
For instance, if the 'hobbies' key contains an array of hobbies, you can add a new value onto the array like so::

$session->push('hobbies', ['sport'=>'tennis']);

Removing Session Data
=====================

Just as with any other variable, unsetting a value in ``$_SESSION`` can be
done through ``unset()``::

	unset($_SESSION['some_name']);

	// or multiple values:

	unset(
		$_SESSION['some_name'],
		$_SESSION['another_name']
	);

Also, just as ``set()`` can be used to add information to a
session, ``remove()`` can be used to remove it, by passing the
session key. For example, if you wanted to remove 'some_name' from your
session data array::

	$session->remove('some_name');

This method also accepts an array of item keys to unset::

	$array_items = ['username', 'email'];
	$session->remove($array_items);

Flashdata
=========

CodeIgniter supports "flashdata", or session data that will only be
available for the next request, and is then automatically cleared.

This can be very useful, especially for one-time informational, error or
status messages (for example: "Record 2 deleted").

It should be noted that flashdata variables are regular session variables,
managed inside the CodeIgniter session handler.

To mark an existing item as "flashdata"::

	$session->markAsFlashdata('item');

If you want to mark multiple items as flashdata, simply pass the keys as an
array::

	$session->markAsFlashdata(['item', 'item2']);

To add flashdata::

	$_SESSION['item'] = 'value';
	$session->markAsFlashdata('item');

Or alternatively, using the ``setFlashdata()`` method::

	$session->setFlashdata('item', 'value');

You can also pass an array to ``setFlashdata()``, in the same manner as
``set()``.

Reading flashdata variables is the same as reading regular session data
through ``$_SESSION``::

	$_SESSION['item']

.. important:: The ``get()`` method WILL return flashdata items when
	retrieving a single item by key. It will not return flashdata when
	grabbing all userdata from the session, however.

However, if you want to be sure that you're reading "flashdata" (and not
any other kind), you can also use the ``getFlashdata()`` method::

	$session->getFlashdata('item');

Or to get an array with all flashdata, simply omit the key parameter::

	$session->getFlashdata();

.. note:: The ``getFlashdata()`` method returns NULL if the item cannot be
	found.

If you find that you need to preserve a flashdata variable through an
additional request, you can do so using the ``keepFlashdata()`` method.
You can either pass a single item or an array of flashdata items to keep.

::

	$session->keepFlashdata('item');
	$session->keepFlashdata(['item1', 'item2', 'item3']);

Tempdata
========

CodeIgniter also supports "tempdata", or session data with a specific
expiration time. After the value expires, or the session expires or is
deleted, the value is automatically removed.

Similarly to flashdata, tempdata variables are managed internally by the
CodeIgniter session handler.

To mark an existing item as "tempdata", simply pass its key and expiry time
(in seconds!) to the ``mark_as_temp()`` method::

	// 'item' will be erased after 300 seconds
	$session->markAsTempdata('item', 300);

You can mark multiple items as tempdata in two ways, depending on whether
you want them all to have the same expiry time or not::

	// Both 'item' and 'item2' will expire after 300 seconds
	$session->markAsTempdata(['item', 'item2'], 300);

	// 'item' will be erased after 300 seconds, while 'item2'
	// will do so after only 240 seconds
	$session->markAsTempdata([
		'item'	=> 300,
		'item2'	=> 240
	]);

To add tempdata::

	$_SESSION['item'] = 'value';
	$session->markAsTempdata('item', 300); // Expire in 5 minutes

Or alternatively, using the ``setTempdata()`` method::

	$session->setTempdata('item', 'value', 300);

You can also pass an array to ``set_tempdata()``::

	$tempdata = ['newuser' => TRUE, 'message' => 'Thanks for joining!'];
	$session->setTempdata($tempdata, NULL, $expire);

.. note:: If the expiration is omitted or set to 0, the default
	time-to-live value of 300 seconds (or 5 minutes) will be used.

To read a tempdata variable, again you can just access it through the
``$_SESSION`` superglobal array::

	$_SESSION['item']

.. important:: The ``get()`` method WILL return tempdata items when
	retrieving a single item by key. It will not return tempdata when
	grabbing all userdata from the session, however.

Or if you want to be sure that you're reading "tempdata" (and not any
other kind), you can also use the ``getTempdata()`` method::

	$session->getTempdata('item');

And of course, if you want to retrieve all existing tempdata::

	$session->getTempdata();

.. note:: The ``getTempdata()`` method returns NULL if the item cannot be
	found.

If you need to remove a tempdata value before it expires, you can directly
unset it from the ``$_SESSION`` array::

	unset($_SESSION['item']);

However, this won't remove the marker that makes this specific item to be
tempdata (it will be invalidated on the next HTTP request), so if you
intend to reuse that same key in the same request, you'd want to use
``removeTempdata()``::

	$session->removeTempdata('item');

Destroying a Session
====================

To clear the current session (for example, during a logout), you may
simply use either PHP's `session_destroy() <http://php.net/session_destroy>`_
function, or the ``sess_destroy()`` method. Both will work in exactly the
same way::

	session_destroy();

	// or

	$session->destroy();

.. note:: This must be the last session-related operation that you do
	during the same request. All session data (including flashdata and
	tempdata) will be destroyed permanently and functions will be
	unusable during the same request after you destroy the session.

You may also use the ``stop()`` method to completely kill the session
by removing the old session_id, destroying all data, and destroying
the cookie that contained the session id::

    $session->stop();

Accessing session metadata
==========================

In previous CodeIgniter versions, the session data array included 4 items
by default: 'session_id', 'ip_address', 'user_agent', 'last_activity'.

This was due to the specifics of how sessions worked, but is now no longer
necessary with our new implementation. However, it may happen that your
application relied on these values, so here are alternative methods of
accessing them:

  - session_id: ``session_id()``
  - ip_address: ``$_SERVER['REMOTE_ADDR']``
  - user_agent: ``$_SERVER['HTTP_USER_AGENT']`` (unused by sessions)
  - last_activity: Depends on the storage, no straightforward way. Sorry!

Session Preferences
*********************************************************************

CodeIgniter will usually make everything work out of the box. However,
Sessions are a very sensitive component of any application, so some
careful configuration must be done. Please take your time to consider
all of the options and their effects.

You'll find the following Session related preferences in your
**app/Config/App.php** file:

============================== ========================================= ============================================== ============================================================================================
Preference                     Default                                   Options                                        Description
============================== ========================================= ============================================== ============================================================================================
**sessionDriver**              CodeIgniter\Session\Handlers\FileHandler  CodeIgniter\Session\Handlers\FileHandler       The session storage driver to use.
                                                                         CodeIgniter\Session\Handlers\DatabaseHandler
                                                                         CodeIgniter\Session\Handlers\MemcachedHandler
                                                                         CodeIgniter\Session\Handlers\RedisHandler
**sessionCookieName**          ci_session                                [A-Za-z\_-] characters only                    The name used for the session cookie.
**sessionExpiration**          7200 (2 hours)                            Time in seconds (integer)                      The number of seconds you would like the session to last.
                                                                                                                        If you would like a non-expiring session (until browser is closed) set the value to zero: 0
**sessionSavePath**            NULL                                      None                                           Specifies the storage location, depends on the driver being used.
**sessionMatchIP**             FALSE                                     TRUE/FALSE (boolean)                           Whether to validate the user's IP address when reading the session cookie.
                                                                                                                        Note that some ISPs dynamically changes the IP, so if you want a non-expiring session you
                                                                                                                        will likely set this to FALSE.
**sessionTimeToUpdate**        300                                       Time in seconds (integer)                      This option controls how often the session class will regenerate itself and create a new
                                                                                                                        session ID. Setting it to 0 will disable session ID regeneration.
**sessionRegenerateDestroy**   FALSE                                     TRUE/FALSE (boolean)                           Whether to destroy session data associated with the old session ID when auto-regenerating
                                                                                                                        the session ID. When set to FALSE, the data will be later deleted by the garbage collector.
============================== ========================================= ============================================== ============================================================================================

.. note:: As a last resort, the Session library will try to fetch PHP's
	session related INI settings, as well as legacy CI settings such as
	'sess_expire_on_close' when any of the above is not configured.
	However, you should never rely on this behavior as it can cause
	unexpected results or be changed in the future. Please configure
	everything properly.

In addition to the values above, the cookie and native drivers apply the
following configuration values shared by the :doc:`IncomingRequest </incoming/incomingrequest>` and
:doc:`Security <security>` classes:

================== =============== ===========================================================================
Preference         Default         Description
================== =============== ===========================================================================
**cookieDomain**   ''              The domain for which the session is applicable
**cookiePath**     /               The path to which the session is applicable
**cookieSecure**   FALSE           Whether to create the session cookie only on encrypted (HTTPS) connections
================== =============== ===========================================================================

.. note:: The 'cookieHTTPOnly' setting doesn't have an effect on sessions.
	Instead the HttpOnly parameter is always enabled, for security
	reasons. Additionally, the 'cookiePrefix' setting is completely
	ignored.

Session Drivers
*********************************************************************

As already mentioned, the Session library comes with 4 handlers, or storage
engines, that you can use:

  - CodeIgniter\Session\Handlers\FileHandler
  - CodeIgniter\Session\Handlers\DatabaseHandler
  - CodeIgniter\Session\Handlers\MemcachedHandler
  - CodeIgniter\Session\Handlers\RedisHandler

By default, the ``FileHandler`` Driver will be used when a session is initialized,
because it is the safest choice and is expected to work everywhere
(virtually every environment has a file system).

However, any other driver may be selected via the ``public $sessionDriver``
line in your **app/Config/App.php** file, if you chose to do so.
Have it in mind though, every driver has different caveats, so be sure to
get yourself familiar with them (below) before you make that choice.

FileHandler Driver (the default)
==================================================================

The 'FileHandler' driver uses your file system for storing session data.

It can safely be said that it works exactly like PHP's own default session
implementation, but in case this is an important detail for you, have it
mind that it is in fact not the same code and it has some limitations
(and advantages).

To be more specific, it doesn't support PHP's `directory level and mode
formats used in session.save_path
<http://php.net/manual/en/session.configuration.php#ini.session.save-path>`_,
and it has most of the options hard-coded for safety. Instead, only
absolute paths are supported for ``public $sessionSavePath``.

Another important thing that you should know, is to make sure that you
don't use a publicly-readable or shared directory for storing your session
files. Make sure that *only you* have access to see the contents of your
chosen *sessionSavePath* directory. Otherwise, anybody who can do that, can
also steal any of the current sessions (also known as "session fixation"
attack).

On UNIX-like operating systems, this is usually achieved by setting the
0700 mode permissions on that directory via the `chmod` command, which
allows only the directory's owner to perform read and write operations on
it. But be careful because the system user *running* the script is usually
not your own, but something like 'www-data' instead, so only setting those
permissions will probably break your application.

Instead, you should do something like this, depending on your environment
::

	mkdir /<path to your application directory>/Writable/sessions/
	chmod 0700 /<path to your application directory>/Writable/sessions/
	chown www-data /<path to your application directory>/Writable/sessions/

Bonus Tip
--------------------------------------------------------

Some of you will probably opt to choose another session driver because
file storage is usually slower. This is only half true.

A very basic test will probably trick you into believing that an SQL
database is faster, but in 99% of the cases, this is only true while you
only have a few current sessions. As the sessions count and server loads
increase - which is the time when it matters - the file system will
consistently outperform almost all relational database setups.

In addition, if performance is your only concern, you may want to look
into using `tmpfs <http://eddmann.com/posts/storing-php-sessions-file-caches-in-memory-using-tmpfs/>`_,
(warning: external resource), which can make your sessions blazing fast.

DatabaseHandler Driver
==================================================================

The 'DatabaseHandler' driver uses a relational database such as MySQL or
PostgreSQL to store sessions. This is a popular choice among many users,
because it allows the developer easy access to the session data within
an application - it is just another table in your database.

However, there are some conditions that must be met:

  - You can NOT use a persistent connection.
  - You can NOT use a connection with the *cacheOn* setting enabled.

In order to use the 'DatabaseHandler' session driver, you must also create this
table that we already mentioned and then set it as your
``$sessionSavePath`` value.
For example, if you would like to use 'ci_sessions' as your table name,
you would do this::

	public $sessionDriver   = 'CodeIgniter\Session\Handlers\DatabaseHandler';
	public $sessionSavePath = 'ci_sessions';

And then of course, create the database table ...

For MySQL::

	CREATE TABLE IF NOT EXISTS `ci_sessions` (
		`id` varchar(128) NOT NULL,
		`ip_address` varchar(45) NOT NULL,
		`timestamp` int(10) unsigned DEFAULT 0 NOT NULL,
		`data` blob NOT NULL,
		KEY `ci_sessions_timestamp` (`timestamp`)
	);

For PostgreSQL::

	CREATE TABLE "ci_sessions" (
		"id" varchar(128) NOT NULL,
		"ip_address" varchar(45) NOT NULL,
		"timestamp" bigint DEFAULT 0 NOT NULL,
		"data" text DEFAULT '' NOT NULL
	);

	CREATE INDEX "ci_sessions_timestamp" ON "ci_sessions" ("timestamp");

You will also need to add a PRIMARY KEY **depending on your 'sessionMatchIP'
setting**. The examples below work both on MySQL and PostgreSQL::

	// When sessionMatchIP = TRUE
	ALTER TABLE ci_sessions ADD PRIMARY KEY (id, ip_address);

	// When sessionMatchIP = FALSE
	ALTER TABLE ci_sessions ADD PRIMARY KEY (id);

	// To drop a previously created primary key (use when changing the setting)
	ALTER TABLE ci_sessions DROP PRIMARY KEY;

You can choose the Database group to use by adding a new line to the
**application\Config\App.php** file with the name of the group to use::

  public $sessionDBGroup = 'groupName';

If you'd rather not do all of this by hand, you can use the ``session:migration`` command
from the cli to generate a migration file for you::

  > php spark session:migration
  > php spark migrate

This command will take the **sessionSavePath** and **sessionMatchIP** settings into account
when it generates the code.

.. important:: Only MySQL and PostgreSQL databases are officially
	supported, due to lack of advisory locking mechanisms on other
	platforms. Using sessions without locks can cause all sorts of
	problems, especially with heavy usage of AJAX, and we will not
	support such cases. Use ``session_write_close()`` after you've
	done processing session data if you're having performance
	issues.

RedisHandler Driver
==================================================================

.. note:: Since Redis doesn't have a locking mechanism exposed, locks for
	this driver are emulated by a separate value that is kept for up
	to 300 seconds.

Redis is a storage engine typically used for caching and popular because
of its high performance, which is also probably your reason to use the
'RedisHandler' session driver.

The downside is that it is not as ubiquitous as relational databases and
requires the `phpredis <https://github.com/phpredis/phpredis>`_ PHP
extension to be installed on your system, and that one doesn't come
bundled with PHP.
Chances are, you're only be using the RedisHandler driver only if you're already
both familiar with Redis and using it for other purposes.

Just as with the 'FileHandler' and 'DatabaseHandler' drivers, you must also configure
the storage location for your sessions via the
``$sessionSavePath`` setting.
The format here is a bit different and complicated at the same time. It is
best explained by the *phpredis* extension's README file, so we'll simply
link you to it:

	https://github.com/phpredis/phpredis#php-session-handler

.. warning:: CodeIgniter's Session library does NOT use the actual 'redis'
	``session.save_handler``. Take note **only** of the path format in
	the link above.

For the most common case however, a simple ``host:port`` pair should be
sufficient::

	public $sessionDiver    = 'CodeIgniter\Session\Handlers\RedisHandler';
	public $sessionSavePath = 'tcp://localhost:6379';

MemcachedHandler Driver
==================================================================

.. note:: Since Memcached doesn't have a locking mechanism exposed, locks
	for this driver are emulated by a separate value that is kept for
	up to 300 seconds.

The 'MemcachedHandler' driver is very similar to the 'RedisHandler' one in all of its
properties, except perhaps for availability, because PHP's `Memcached
<http://php.net/memcached>`_ extension is distributed via PECL and some
Linux distributions make it available as an easy to install package.

Other than that, and without any intentional bias towards Redis, there's
not much different to be said about Memcached - it is also a popular
product that is usually used for caching and famed for its speed.

However, it is worth noting that the only guarantee given by Memcached
is that setting value X to expire after Y seconds will result in it being
deleted after Y seconds have passed (but not necessarily that it won't
expire earlier than that time). This happens very rarely, but should be
considered as it may result in loss of sessions.

The ``$sessionSavePath`` format is fairly straightforward here,
being just a ``host:port`` pair::

	public $sessionDriver   = 'CodeIgniter\Session\Handlers\MemcachedHandler';
	public $sessionSavePath = 'localhost:11211';

Bonus Tip
--------------------------------------------------------

Multi-server configuration with an optional *weight* parameter as the
third colon-separated (``:weight``) value is also supported, but we have
to note that we haven't tested if that is reliable.

If you want to experiment with this feature (on your own risk), simply
separate the multiple server paths with commas::

	// localhost will be given higher priority (5) here,
	// compared to 192.0.2.1 with a weight of 1.
	public $sessionSavePath = 'localhost:11211:5,192.0.2.1:11211:1';
