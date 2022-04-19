###################
Server Requirements
###################

`PHP <https://www.php.net/>`_ version 7.4 or newer is required, with the
`*intl* extension <https://www.php.net/manual/en/intl.requirements.php>`_ and `*mbstring* extension <https://www.php.net/manual/en/mbstring.requirements.php>`_
installed.

The following PHP extensions should be enabled on your server:

  - ``php-json``
  - ``php-mysqlnd`` (if you use MySQL)
  - ``php-xml``

In order to use the :doc:`CURLRequest </libraries/curlrequest>`, you will need
`libcurl <https://www.php.net/manual/en/curl.requirements.php>`_ installed.

A database is required for most web application programming.
Currently supported databases are:

  - MySQL via the *MySQLi* driver (version 5.1 and above only)
  - PostgreSQL via the *Postgre* driver
  - SQLite3 via the *SQLite3* driver
  - MSSQL via the *SQLSRV* driver (version 2005 and above only)
  - Oracle via the *OCI8* driver (version 12.1 and above only)

Not all of the drivers have been converted/rewritten for CodeIgniter4.
The list below shows the outstanding ones.

  - MySQL (5.1+) via the *pdo* driver
  - Oracle via the *pdo* drivers
  - PostgreSQL via the *pdo* driver
  - MSSQL via the *pdo* driver
  - SQLite via the *sqlite* (version 2) and *pdo* drivers
  - CUBRID via the *cubrid* and *pdo* drivers
  - Interbase/Firebird via the *ibase* and *pdo* drivers
  - ODBC via the *odbc* and *pdo* drivers (you should know that ODBC is actually an abstraction layer)
