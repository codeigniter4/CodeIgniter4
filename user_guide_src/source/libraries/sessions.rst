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

Using the Session Class
***********************

Initializing a Session
======================

Sessions will typically run globally with each page load, so the Session
class should be magically initialized.

To access and initialize the session:

.. literalinclude:: sessions/001.php

The ``$config`` parameter is optional - your application configuration.
If not provided, the services register will instantiate your default
one.

Once loaded, the Sessions library object will be available using::

    $session

Alternatively, you can use the helper function that will use the default
configuration options. This version is a little friendlier to read,
but does not take any configuration options.

.. literalinclude:: sessions/002.php

How Do Sessions Work?
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

A Note about Concurrency
------------------------

Unless you're developing a website with heavy AJAX usage, you can skip this
section. If you are, however, and if you're experiencing performance
issues, then this note is exactly what you're looking for.

Sessions in CodeIgniter v2.x didn't implement locking,
which meant that two HTTP requests using the same session could run exactly
at the same time. To use a more appropriate technical term - requests were
non-blocking.

However, non-blocking requests in the context of sessions also means
unsafe, because, modifications to session data (or session ID regeneration)
in one request can interfere with the execution of a second, concurrent
request. This detail was at the root of many issues and the main reason why
CodeIgniter 3 has a completely re-written Session library.

Why are we telling you this? Because it is likely that after trying to
find the reason for your performance issues, you may conclude that locking
is the issue and therefore look into how to remove the locks ...

DO NOT DO THAT! Removing locks would be **wrong** and it will cause you
more problems!

Locking is not the issue, it is a solution. Your issue is that you still
have the session open, while you've already processed it and therefore no
longer need it. So, what you need is to close the session for the
current request after you no longer need it.

.. literalinclude:: sessions/003.php

What is Session Data?
=====================

Session data is simply an array associated with a particular session ID
(cookie).

If you've used sessions in PHP before, you should be familiar with PHP's
`$_SESSION superglobal <https://www.php.net/manual/en/reserved.variables.session.php>`_
(if not, please read the content on that link).

CodeIgniter gives access to its session data through the same means, as it
uses the session handlers' mechanism provided by PHP. Using session data is
as simple as manipulating (read, set and unset values) the ``$_SESSION``
array.

.. note:: In general, it is bad practice to use global variables.
    So using the superglobal ``$_SESSION`` directly is not recommended.

In addition, CodeIgniter also provides 2 special types of session data
that are further explained below: `Flashdata`_ and `Tempdata`_.

.. note:: For historical reasons, we refer to session data excluding Flashdata and Tempdata as "userdata".

Retrieving Session Data
=======================

Any piece of information from the session array is available through the
``$_SESSION`` superglobal:

.. literalinclude:: sessions/004.php

Or through the conventional accessor method:

.. literalinclude:: sessions/005.php

Or through the magic getter:

.. literalinclude:: sessions/006.php

Or even through the session helper method:

.. literalinclude:: sessions/007.php

Where ``item`` is the array key corresponding to the item you wish to fetch.
For example, to assign a previously stored ``name`` item to the ``$name``
variable, you will do this:

.. literalinclude:: sessions/008.php

.. note:: The ``get()`` method returns null if the item you are trying
    to access does not exist.

If you want to retrieve all of the existing session data, you can simply
omit the item key (magic getter only works for single property values):

.. literalinclude:: sessions/009.php

.. important:: The ``get()`` method WILL return flashdata or tempdata items when
    retrieving a single item by key. It will not return flashdata or tempdata when
    grabbing all data from the session, however.

Adding Session Data
===================

Let's say a particular user logs into your site. Once authenticated, you
could add their username and e-mail address to the session, making that
data globally available to you without having to run a database query when
you need it.

You can simply assign data to the ``$_SESSION`` array, as with any other
variable. Or as a property of ``$session``.

You can pass an array containing your new session data to the
``set()`` method:

.. literalinclude:: sessions/010.php

Where ``$array`` is an associative array containing your new data. Here's
an example:

.. literalinclude:: sessions/011.php

If you want to add session data one value at a time, ``set()`` also
supports this syntax:

.. literalinclude:: sessions/012.php

If you want to verify that a session value exists, simply check with
``isset()``:

.. literalinclude:: sessions/013.php

Or you can call ``has()``:

.. literalinclude:: sessions/014.php

Pushing New Value to Session Data
=================================

The ``push()`` method is used to push a new value onto a session value that is an array.
For instance, if the ``hobbies`` key contains an array of hobbies, you can add a new value onto the array like so:

.. literalinclude:: sessions/015.php

Removing Session Data
=====================

Just as with any other variable, unsetting a value in ``$_SESSION`` can be
done through ``unset()``:

.. literalinclude:: sessions/016.php

Also, just as ``set()`` can be used to add information to a
session, ``remove()`` can be used to remove it, by passing the
session key. For example, if you wanted to remove ``some_name`` from your
session data array:

.. literalinclude:: sessions/017.php

This method also accepts an array of item keys to unset:

.. literalinclude:: sessions/018.php

.. _sessions-flashdata:

Flashdata
=========

CodeIgniter supports "flashdata", or session data that will only be
available for the next request, and is then automatically cleared.

This can be very useful, especially for one-time informational, error or
status messages (for example: "Record 2 deleted").

It should be noted that flashdata variables are regular session variables,
managed inside the CodeIgniter session handler.

To mark an existing item as "flashdata":

.. literalinclude:: sessions/019.php

If you want to mark multiple items as flashdata, simply pass the keys as an
array:

.. literalinclude:: sessions/020.php

To add flashdata:

.. literalinclude:: sessions/021.php

Or alternatively, using the ``setFlashdata()`` method:

.. literalinclude:: sessions/022.php

You can also pass an array to ``setFlashdata()``, in the same manner as
``set()``.

Reading flashdata variables is the same as reading regular session data
through ``$_SESSION``:

.. literalinclude:: sessions/023.php

.. important:: The ``get()`` method WILL return flashdata items when
    retrieving a single item by key. It will not return flashdata when
    grabbing all data from the session, however.

However, if you want to be sure that you're reading "flashdata" (and not
any other kind), you can also use the ``getFlashdata()`` method:

.. literalinclude:: sessions/024.php

.. note:: The ``getFlashdata()`` method returns null if the item cannot be
    found.

Or to get an array with all flashdata, simply omit the key parameter:

.. literalinclude:: sessions/025.php


If you find that you need to preserve a flashdata variable through an
additional request, you can do so using the ``keepFlashdata()`` method.
You can either pass a single item or an array of flashdata items to keep.

.. literalinclude:: sessions/026.php

Tempdata
========

CodeIgniter also supports "tempdata", or session data with a specific
expiration time. After the value expires, or the session expires or is
deleted, the value is automatically removed.

Similarly to flashdata, tempdata variables are managed internally by the
CodeIgniter session handler.

To mark an existing item as "tempdata", simply pass its key and expiry time
(in seconds!) to the ``markAsTempdata()`` method:

.. literalinclude:: sessions/027.php

You can mark multiple items as tempdata in two ways, depending on whether
you want them all to have the same expiry time or not:

.. literalinclude:: sessions/028.php

To add tempdata:

.. literalinclude:: sessions/029.php

Or alternatively, using the ``setTempdata()`` method:

.. literalinclude:: sessions/030.php

You can also pass an array to ``setTempdata()``:

.. literalinclude:: sessions/031.php

.. note:: If the expiration is omitted or set to 0, the default
    time-to-live value of 300 seconds (or 5 minutes) will be used.

To read a tempdata variable, again you can just access it through the
``$_SESSION`` superglobal array:

.. literalinclude:: sessions/032.php

.. important:: The ``get()`` method WILL return tempdata items when
    retrieving a single item by key. It will not return tempdata when
    grabbing all data from the session, however.

Or if you want to be sure that you're reading "tempdata" (and not any
other kind), you can also use the ``getTempdata()`` method:

.. literalinclude:: sessions/033.php

.. note:: The ``getTempdata()`` method returns null if the item cannot be
    found.

And of course, if you want to retrieve all existing tempdata:

.. literalinclude:: sessions/034.php

If you need to remove a tempdata value before it expires, you can directly
unset it from the ``$_SESSION`` array:

.. literalinclude:: sessions/035.php

However, this won't remove the marker that makes this specific item to be
tempdata (it will be invalidated on the next HTTP request), so if you
intend to reuse that same key in the same request, you'd want to use
``removeTempdata()``:

.. literalinclude:: sessions/036.php

Closing a Session
=================

.. _session-close:

close()
-------

.. versionadded:: 4.4.0

To close the current session manually after you no longer need it, use the
``close()`` method:

.. literalinclude:: sessions/044.php

You do not have to close the session manually, PHP will close it automatically
after your script terminated. But as session data is locked to prevent concurrent
writes only one request may operate on a session at any time. You may improve
your site performance by closing the session as soon as all changes to session
data are done.

This method will work in exactly the same way as PHP's
`session_write_close() <https://www.php.net/session_write_close>`_ function.

Destroying a Session
====================

.. _session-destroy:

destroy()
---------

To clear the current session (for example, during a logout), you may
simply use the library's ``destroy()`` method:

.. literalinclude:: sessions/037.php

This method will work in exactly the same way as PHP's
`session_destroy() <https://www.php.net/session_destroy>`_ function.

This must be the last session-related operation that you do during the same request.
All session data (including flashdata and tempdata) will be destroyed permanently.

.. note:: You do not have to call this method from usual code. Cleanup session
    data rather than destroying the session.

.. _session-stop:

stop()
------

.. deprecated:: 4.3.5

The session class also has the ``stop()`` method.

.. warning:: Prior to v4.3.5, this method did not destroy the session due to a bug.

Starting with v4.3.5, this method has been modified to destroy the session.
However, it is deprecated because it is exactly the same as the ``destroy()``
method. Use the ``destroy()`` method instead.

Accessing Session Metadata
==========================

In CodeIgniter 2, the session data array included 4 items
by default: 'session_id', 'ip_address', 'user_agent', 'last_activity'.

This was due to the specifics of how sessions worked, but is now no longer
necessary with our new implementation. However, it may happen that your
application relied on these values, so here are alternative methods of
accessing them:

  - session_id: ``$session->session_id`` or ``session_id()`` (PHP's built-in function)
  - ip_address: ``$_SERVER['REMOTE_ADDR']``
  - user_agent: ``$_SERVER['HTTP_USER_AGENT']`` (unused by sessions)
  - last_activity: Depends on the storage, no straightforward way. Sorry!

Session Preferences
*******************

CodeIgniter will usually make everything work out of the box. However,
Sessions are a very sensitive component of any application, so some
careful configuration must be done. Please take your time to consider
all of the options and their effects.

.. note:: Since v4.3.0, the new **app/Config/Session.php** has been added.
    Previously, the Session Preferences were in your **app/Config/App.php** file.

You'll find the following Session related preferences in your
**app/Config/Session.php** file:

======================= ============================================ ================================================= ============================================================================================
Preference                     Default                                      Options                                           Description
======================= ============================================ ================================================= ============================================================================================
**driver**              CodeIgniter\\Session\\Handlers\\FileHandler  CodeIgniter\\Session\\Handlers\\FileHandler       The session storage driver to use.
                                                                     CodeIgniter\\Session\\Handlers\\DatabaseHandler
                                                                     CodeIgniter\\Session\\Handlers\\MemcachedHandler
                                                                     CodeIgniter\\Session\\Handlers\\RedisHandler
                                                                     CodeIgniter\\Session\\Handlers\\ArrayHandler
**cookieName**          ci_session                                   [A-Za-z\_-] characters only                       The name used for the session cookie.
**expiration**          7200 (2 hours)                               Time in seconds (integer)                         The number of seconds you would like the session to last.
                                                                                                                       If you would like a non-expiring session (until browser is closed) set the value to zero: 0
**savePath**            null                                         None                                              Specifies the storage location, depends on the driver being used.
**matchIP**             false                                        true/false (boolean)                              Whether to validate the user's IP address when reading the session cookie.
                                                                                                                       Note that some ISPs dynamically changes the IP, so if you want a non-expiring session you
                                                                                                                       will likely set this to false.
**timeToUpdate**        300                                          Time in seconds (integer)                         This option controls how often the session class will regenerate itself and create a new
                                                                                                                       session ID. Setting it to 0 will disable session ID regeneration.
**regenerateDestroy**   false                                        true/false (boolean)                              Whether to destroy session data associated with the old session ID when auto-regenerating
                                                                                                                       the session ID. When set to false, the data will be later deleted by the garbage collector.
======================= ============================================ ================================================= ============================================================================================

.. note:: As a last resort, the Session library will try to fetch PHP's
    session related INI settings, as well as CodeIgniter 3 settings such as
    'sess_expire_on_close' when any of the above is not configured.
    However, you should never rely on this behavior as it can cause
    unexpected results or be changed in the future. Please configure
    everything properly.

.. note:: If ``expiration`` is set to ``0``, the ``session.gc_maxlifetime``
    setting set by PHP in session management will be used as-is
    (often the default value of ``1440``). This needs to be changed in
    ``php.ini`` or via ``ini_set()`` as needed.

In addition to the values above, the Session cookie uses the
following configuration values in your **app/Config/Cookie.php** file:

============== =============== ===========================================================================
Preference           Default         Description
============== =============== ===========================================================================
**domain**     ''              The domain for which the session is applicable
**path**       /               The path to which the session is applicable
**secure**     false           Whether to create the session cookie only on encrypted (HTTPS) connections
**sameSite**   Lax             The SameSite setting for the session cookie
============== =============== ===========================================================================

.. note:: The ``httponly`` setting doesn't have an effect on sessions.
    Instead the HttpOnly parameter is always enabled, for security
    reasons. Additionally, the ``Config\Cookie::$prefix`` setting is completely
    ignored.

Session Drivers
***************

As already mentioned, the Session library comes with 4 handlers, or storage
engines, that you can use:

  - CodeIgniter\\Session\\Handlers\\FileHandler
  - CodeIgniter\\Session\\Handlers\\DatabaseHandler
  - CodeIgniter\\Session\\Handlers\\MemcachedHandler
  - CodeIgniter\\Session\\Handlers\\RedisHandler
  - CodeIgniter\\Session\\Handlers\\ArrayHandler

By default, the ``FileHandler`` Driver will be used when a session is initialized,
because it is the safest choice and is expected to work everywhere
(virtually every environment has a file system).

However, any other driver may be selected via the ``public $driver``
line in your **app/Config/Session.php** file, if you chose to do so.
Have it in mind though, every driver has different caveats, so be sure to
get yourself familiar with them (below) before you make that choice.

.. note:: The ArrayHandler is used during testing and stores all data within
    a PHP array, while preventing the data from being persisted.

FileHandler Driver (the default)
================================

The 'FileHandler' driver uses your file system for storing session data.

It can safely be said that it works exactly like PHP's own default session
implementation, but in case this is an important detail for you, have it
mind that it is in fact not the same code and it has some limitations
(and advantages).

To be more specific, it doesn't support PHP's `directory level and mode
formats used in session.save_path
<https://www.php.net/manual/en/session.configuration.php#ini.session.save-path>`_,
and it has most of the options hard-coded for safety. Instead, only
absolute paths are supported for ``public string $savePath``.

Another important thing that you should know, is to make sure that you
don't use a publicly-readable or shared directory for storing your session
files. Make sure that *only you* have access to see the contents of your
chosen *savePath* directory. Otherwise, anybody who can do that, can
also steal any of the current sessions (also known as "session fixation"
attack).

On UNIX-like operating systems, this is usually achieved by setting the
0700 mode permissions on that directory via the `chmod` command, which
allows only the directory's owner to perform read and write operations on
it. But be careful because the system user *running* the script is usually
not your own, but something like 'www-data' instead, so only setting those
permissions will probably break your application.

Instead, you should do something like this, depending on your environment:

.. code-block:: console

    mkdir /<path to your application directory>/writable/sessions/
    chmod 0700 /<path to your application directory>/writable/sessions/
    chown www-data /<path to your application directory>/writable/sessions/

Bonus Tip
---------

Some of you will probably opt to choose another session driver because
file storage is usually slower. This is only half true.

A very basic test will probably trick you into believing that an SQL
database is faster, but in 99% of the cases, this is only true while you
only have a few current sessions. As the sessions count and server loads
increase - which is the time when it matters - the file system will
consistently outperform almost all relational database setups.

In addition, if performance is your only concern, you may want to look
into using `tmpfs <https://eddmann.com/posts/storing-php-sessions-file-caches-in-memory-using-tmpfs/>`_,
(warning: external resource), which can make your sessions blazing fast.

.. _sessions-databasehandler-driver:

DatabaseHandler Driver
======================

.. important:: Only MySQL and PostgreSQL databases are officially
    supported, due to lack of advisory locking mechanisms on other
    platforms. Using sessions without locks can cause all sorts of
    problems, especially with heavy usage of AJAX, and we will not
    support such cases. Use the :ref:`session-close` method after you've
    done processing session data if you're having performance
    issues.

The 'DatabaseHandler' driver uses a relational database such as MySQL or
PostgreSQL to store sessions. This is a popular choice among many users,
because it allows the developer easy access to the session data within
an application - it is just another table in your database.

However, there are some conditions that must be met:

  - You can NOT use a persistent connection.

Configure DatabaseHandler
-------------------------

Setting Table Name
^^^^^^^^^^^^^^^^^^

In order to use the 'DatabaseHandler' session driver, you must also create this
table that we already mentioned and then set it as your
``$savePath`` value.
For example, if you would like to use 'ci_sessions' as your table name,
you would do this:

.. literalinclude:: sessions/039.php

Creating Database Table
^^^^^^^^^^^^^^^^^^^^^^^

And then of course, create the database table ...

For MySQL::

    CREATE TABLE IF NOT EXISTS `ci_sessions` (
        `id` varchar(128) NOT null,
        `ip_address` varchar(45) NOT null,
        `timestamp` timestamp DEFAULT CURRENT_TIMESTAMP NOT null,
        `data` blob NOT null,
        KEY `ci_sessions_timestamp` (`timestamp`)
    );

For PostgreSQL::

    CREATE TABLE "ci_sessions" (
        "id" varchar(128) NOT NULL,
        "ip_address" inet NOT NULL,
        "timestamp" timestamptz DEFAULT CURRENT_TIMESTAMP NOT NULL,
        "data" bytea DEFAULT '' NOT NULL
    );

    CREATE INDEX "ci_sessions_timestamp" ON "ci_sessions" ("timestamp");

.. note:: The ``id`` value contains the session cookie name (``Config\Session::$cookieName``)
    and the session ID and a delimiter. It should be increased as needed, for example,
    when using long session IDs.

Adding Primary Key
^^^^^^^^^^^^^^^^^^

You will also need to add a PRIMARY KEY **depending on your $matchIP
setting**. The examples below work both on MySQL and PostgreSQL::

    // When $matchIP = true
    ALTER TABLE ci_sessions ADD PRIMARY KEY (id, ip_address);

    // When $matchIP = false
    ALTER TABLE ci_sessions ADD PRIMARY KEY (id);

    // To drop a previously created primary key (use when changing the setting)
    ALTER TABLE ci_sessions DROP PRIMARY KEY;

.. important:: If you don't add the correct primary key, the following error
    may occur::

        Uncaught mysqli_sql_exception: Duplicate entry 'ci_session:***' for key 'ci_sessions.PRIMARY'

Changing Database Group
^^^^^^^^^^^^^^^^^^^^^^^

The default database group is used by default.
You can change the database group to use by changing the ``$DBGroup`` property
in the **app/Config/Session.php** file to the name of the group to use:

.. literalinclude:: sessions/040.php

Setting Up Database Table with Command
^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^^

If you'd rather not do all of this by hand, you can use the ``make:migration --session`` command
from the cli to generate a migration file for you:

.. code-block:: console

  php spark make:migration --session
  php spark migrate

This command will take the ``$savePath`` and ``$matchIP`` settings into account
when it generates the code.

.. _sessions-redishandler-driver:

RedisHandler Driver
===================

.. note:: Since Redis doesn't have a locking mechanism exposed, locks for
    this driver are emulated by a separate value that is kept for up
    to 300 seconds. With ``v4.3.2`` or above, you can connect ``Redis`` with **TLS** protocol.

Redis is a storage engine typically used for caching and popular because
of its high performance, which is also probably your reason to use the
'RedisHandler' session driver.

The downside is that it is not as ubiquitous as relational databases and
requires the `phpredis <https://github.com/phpredis/phpredis>`_ PHP
extension to be installed on your system, and that one doesn't come
bundled with PHP.
Chances are, you're only be using the RedisHandler driver only if you're already
both familiar with Redis and using it for other purposes.

Configure RedisHandler
----------------------

Just as with the 'FileHandler' and 'DatabaseHandler' drivers, you must also configure
the storage location for your sessions via the
``$savePath`` setting.
The format here is a bit different and complicated at the same time. It is
best explained by the *phpredis* extension's README file, so we'll simply
link you to it:

    https://github.com/phpredis/phpredis

.. important:::: CodeIgniter's Session library does NOT use the actual 'redis'
    ``session.save_handler``. Take note **only** of the path format in
    the link above.

For the most common case however, a simple ``host:port`` pair should be
sufficient:

.. literalinclude:: sessions/041.php

.. _sessions-memcachedhandler-driver:

MemcachedHandler Driver
=======================

.. note:: Since Memcached doesn't have a locking mechanism exposed, locks
    for this driver are emulated by a separate value that is kept for
    up to 300 seconds.

The 'MemcachedHandler' driver is very similar to the 'RedisHandler' one in all of its
properties, except perhaps for availability, because PHP's `Memcached
<https://www.php.net/memcached>`_ extension is distributed via PECL and some
Linux distributions make it available as an easy to install package.

Other than that, and without any intentional bias towards Redis, there's
not much different to be said about Memcached - it is also a popular
product that is usually used for caching and famed for its speed.

However, it is worth noting that the only guarantee given by Memcached
is that setting value X to expire after Y seconds will result in it being
deleted after Y seconds have passed (but not necessarily that it won't
expire earlier than that time). This happens very rarely, but should be
considered as it may result in loss of sessions.

Configure MemcachedHandler
--------------------------

The ``$savePath`` format is fairly straightforward here,
being just a ``host:port`` pair:

.. literalinclude:: sessions/042.php

Bonus Tip
---------

Multi-server configuration with an optional *weight* parameter as the
third colon-separated (``:weight``) value is also supported, but we have
to note that we haven't tested if that is reliable.

If you want to experiment with this feature (on your own risk), simply
separate the multiple server paths with commas:

.. literalinclude:: sessions/043.php
