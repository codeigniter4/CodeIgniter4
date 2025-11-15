###########################
Connecting to your Database
###########################

.. contents::
    :local:
    :depth: 2

Connecting to a Database
========================

Connecting to the Default Group
-------------------------------

You can connect to your database by adding this line of code in any
function where it is needed, or in your class constructor to make the
database available globally in that class.

.. literalinclude:: connecting/001.php
    :lines: 2-

If the above function does **not** contain any information in the first
parameter, it will connect to the default group specified in your database config
file. For most people, this is the preferred method of use.

A convenience method exists that is purely a wrapper around the above line
and is provided for your convenience:

.. literalinclude:: connecting/002.php
    :lines: 2-

Available Parameters
--------------------

**\\Config\\Database::connect($group = null, bool $getShared = true): BaseConnection**

#. ``$group``: The database group name, a string that must match the config class' property name. Default value is ``Config\Database::$defaultGroup``.
#. ``$getShared``: true/false (boolean). Whether to return the shared connection (see
   Connecting to Multiple Databases below).

Connecting to Specific Group
----------------------------

The first parameter of this function can **optionally** be used to
specify a particular database group from your config file. Examples:

To choose a specific group from your config file you can do this:

.. literalinclude:: connecting/003.php
    :lines: 2-

Where ``group_name`` is the name of the connection group from your config
file.

Multiple Connections to Same Database
-------------------------------------

By default, the ``connect()`` method will return the same instance of the
database connection every time. If you need to have a separate connection
to the same database, send ``false`` as the second parameter:

.. literalinclude:: connecting/004.php
    :lines: 2-

Connecting to Multiple Databases
================================

If you need to connect to more than one database simultaneously you can
do so as follows:

.. literalinclude:: connecting/005.php
    :lines: 2-

Note: Change the words ``group_one`` and ``group_two`` to the specific
group names you are connecting to.

.. note:: You don't need to create separate database configurations if you
    only need to use a different database on the same connection. You
    can switch to a different database when you need to, like this:
    ``$db->setDatabase($database2_name);``

Connecting with Custom Settings
===============================

You can pass in an array of database settings instead of a group name to get
a connection that uses your custom settings. The array passed in must be
the same format as the groups are defined in the configuration file:

.. literalinclude:: connecting/006.php
    :lines: 2-

Reconnecting / Keeping the Connection Alive
===========================================

If the database server's idle timeout is exceeded while you're doing
some heavy PHP lifting (processing an image, for instance), you should
consider pinging the server by using the ``reconnect()`` method before
sending further queries, which can gracefully keep the connection alive
or re-establish it.

.. important:: If you are using MySQLi database driver, the ``reconnect()`` method
    does not ping the server but it closes the connection then connects again.

.. literalinclude:: connecting/007.php
    :lines: 2-

Manually Closing the Connection
===============================

While CodeIgniter intelligently takes care of closing your database
connections, you can explicitly close the connection.

.. literalinclude:: connecting/008.php
    :lines: 2-
