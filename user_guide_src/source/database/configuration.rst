######################
Database Configuration
######################

.. contents::
    :local:
    :depth: 2

***********
Config File
***********

CodeIgniter has a config file that lets you store your database
connection values (username, password, database name, etc.). The config
file is located at **app/Config/Database.php**. You can also set
database connection values in the **.env** file. See below for more details.

The config settings are stored in a class property that is an array with this
prototype:

.. literalinclude:: configuration/001.php

The name of the class property is the connection name, and can be used
while connecting to specify a group name.

.. note:: The default location of the SQLite3 database is in the ``writable`` folder.
    If you want to change the location, you must set the full path to the new folder.

DSN
===

Some database drivers (such as PDO, PostgreSQL, Oracle, ODBC) might
require a full DSN string to be provided. If that is the case, you
should use the 'DSN' configuration setting, as if you're using the
driver's underlying native PHP extension, like this:

.. literalinclude:: configuration/002.php

.. note:: If you do not specify a DSN string for a driver that requires it, CodeIgniter
    will try to build it with the rest of the provided settings.

You can also set a Data Source Name in universal manner (URL like). In that case DSNs must have this prototype:

.. literalinclude:: configuration/003.php

To override default config values when connecting with a universal version of the DSN string,
add the config variables as a query string:

.. literalinclude:: configuration/004.php

.. note:: If you provide a DSN string and it is missing some valid settings (e.g., the
    database character set), which are present in the rest of the configuration
    fields, CodeIgniter will append them.

Failovers
=========

You can also specify failovers for the situation when the main connection cannot connect for some reason.
These failovers can be specified by setting the failover for a connection like this:

.. literalinclude:: configuration/005.php

You can specify as many failovers as you like.

You may optionally store multiple sets of connection
values. If, for example, you run multiple environments (development,
production, test, etc.) under a single installation, you can set up a
connection group for each, then switch between groups as needed. For
example, to set up a "test" environment you would do this:

.. literalinclude:: configuration/006.php

Then, to globally tell the system to use that group you would set this
variable located in the config file:

.. literalinclude:: configuration/007.php

.. note:: The name 'test' is arbitrary. It can be anything you want. By
    default we've used the word "default" for the primary connection,
    but it too can be renamed to something more relevant to your project.

defaultGroup
============

You could modify the config file to detect the environment and automatically
update the `defaultGroup` value to the correct one by adding the required logic
within the class' constructor:

.. literalinclude:: configuration/008.php

**************************
Configuring with .env File
**************************

You can also save your configuration values within a **.env** file with the current server's
database settings. You only need to enter the values that change from what is in the
default group's configuration settings. The values should be name following this format, where
``default`` is the group name::

    database.default.username = 'root';
    database.default.password = '';
    database.default.database = 'ci4';

**********************
Explanation of Values:
**********************

=============== ===========================================================================================================
 Name Config    Description
=============== ===========================================================================================================
**dsn**         The DSN connect string (an all-in-one configuration sequence).
**hostname**    The hostname of your database server. Often this is 'localhost'.
**username**    The username used to connect to the database.
**password**    The password used to connect to the database.
**database**    The name of the database you want to connect to.
**DBDriver**    The database type. e.g.,: ``MySQLi``, ``Postgres``, etc. The case must match the driver name
**DBPrefix**    An optional table prefix which will added to the table name when running
                :doc:`Query Builder <query_builder>` queries. This permits multiple CodeIgniter
                installations to share one database.
**pConnect**    true/false (boolean) - Whether to use a persistent connection.
**DBDebug**     true/false (boolean) - Whether database errors should be displayed.
**charset**     The character set used in communicating with the database.
**DBCollat**    The character collation used in communicating with the database (``MySQLi`` only)
**swapPre**     A default table prefix that should be swapped with ``DBPrefix``. This is useful for distributed
                applications where you might run manually written queries, and need the prefix to still be
                customizable by the end user.
**schema**      The database schema, default value varies by driver. Used by ``Postgres`` and ``SQLSRV`` drivers.
**encrypt**     Whether or not to use an encrypted connection.
                ``SQLSRV`` drivers accept true/false
                ``MySQLi`` drivers accept an array with the following options:
                * ``ssl_key``    - Path to the private key file
                * ``ssl_cert``   - Path to the public key certificate file
                * ``ssl_ca``     - Path to the certificate authority file
                * ``ssl_capath`` - Path to a directory containing trusted CA certificates in PEM format
                * ``ssl_cipher`` - List of *allowed* ciphers to be used for the encryption, separated by colons (``:``)
                * ``ssl_verify`` - true/false; Whether to verify the server certificate or not (``MySQLi`` only)
**compress**    Whether or not to use client compression (``MySQLi`` only).
**strictOn**    true/false (boolean) - Whether to force "Strict Mode" connections, good for ensuring strict SQL
                while developing an application.
**port**        The database port number. To use this value you have to add a line to the database config array.

                .. literalinclude:: configuration/009.php
**foreignKeys** true/false (boolean) - Whether or not to enable Foreign Key constraint (``SQLite3`` only).

                .. important:: SQLite3 Foreign Key constraint is disabled by default.
                    See `SQLite documentation <https://www.sqlite.org/pragma.html#pragma_foreign_keys>`_.
                    To enforce Foreign Key constraint, set this config item to true.
=============== ===========================================================================================================

.. note:: Depending on what database platform you are using (MySQL, PostgreSQL,
    etc.) not all values will be needed. For example, when using SQLite you
    will not need to supply a username or password, and the database name
    will be the path to your database file. The information above assumes
    you are using MySQL.
