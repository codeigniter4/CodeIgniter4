###########################
Connecting to your Database
###########################

You can connect to your database by adding this line of code in any
function where it is needed, or in your class constructor to make the
database available globally in that class.

::

	$db = \Config\Database::connect();

If the above function does **not** contain any information in the first
parameter it will connect to the default group specified in your database config
file. For most people, this is the preferred method of use.

A convenience method exists that is purely a wrapper around the above line
and is provided for your convenience::

    $db = db_connect();

Available Parameters
--------------------

#. The database group name, a string that must match the config class' property name. Default value is $config->defaultGroup.
#. TRUE/FALSE (boolean). Whether to return the shared connection (see
   Connecting to Multiple Databases below).

Manually Connecting to a Database
---------------------------------

The first parameter of this function can **optionally** be used to
specify a particular database group from your config file. Examples:

To choose a specific group from your config file you can do this::

	$db = \Config\Database::connect('group_name');

Where group_name is the name of the connection group from your config
file.

Multiple Connections to Same Database
-------------------------------------

By default, the ``connect()`` method will return the same instance of the
database connection every time. If you need to have a separate connection
to the same database, send ``false`` as the second parameter::

	$db = \Config\Database::connect('group_name', false);

Connecting to Multiple Databases
================================

If you need to connect to more than one database simultaneously you can
do so as follows::

	$db1 = \Config\Database::connect('group_one');
	$db  = \Config\Database::connect('group_two');

Note: Change the words "group_one" and "group_two" to the specific
group names you are connecting to.

.. note:: You don't need to create separate database configurations if you
	only need to use a different database on the same connection. You
	can switch to a different database when you need to, like this:

	| $db->setDatabase($database2_name);

Connecting with Custom Settings
===============================

You can pass in an array of database settings instead of a group name to get
a connection that uses your custom settings. The array passed in must be
the same format as the groups are defined in the configuration file::

    $custom = [
		'DSN'      => '',
		'hostname' => 'localhost',
		'username' => '',
		'password' => '',
		'database' => '',
		'DBDriver' => 'MySQLi',
		'DBPrefix' => '',
		'pConnect' => false,
		'DBDebug'  => (ENVIRONMENT !== 'production'),
		'cacheOn'  => false,
		'cacheDir' => '',
		'charset'  => 'utf8',
		'DBCollat' => 'utf8_general_ci',
		'swapPre'  => '',
		'encrypt'  => false,
		'compress' => false,
		'strictOn' => false,
		'failover' => [],
		'port'     => 3306,
	];
    $db = \Config\Database::connect($custom);


Reconnecting / Keeping the Connection Alive
===========================================

If the database server's idle timeout is exceeded while you're doing
some heavy PHP lifting (processing an image, for instance), you should
consider pinging the server by using the reconnect() method before
sending further queries, which can gracefully keep the connection alive
or re-establish it.

.. important:: If you are using MySQLi database driver, the reconnect() method
	does not ping the server but it closes the connection then connects again.

::

	$db->reconnect();

Manually closing the Connection
===============================

While CodeIgniter intelligently takes care of closing your database
connections, you can explicitly close the connection.

::

	$db->close();
