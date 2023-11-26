###################
Server Requirements
###################

.. contents::
    :local:
    :depth: 2

***************************
PHP and Required Extensions
***************************

`PHP <https://www.php.net/>`_ version 7.4 or newer is required, with the following PHP extensions are enabled:

  - `intl <https://www.php.net/manual/en/intl.requirements.php>`_
  - `mbstring <https://www.php.net/manual/en/mbstring.requirements.php>`_
  - `json <https://www.php.net/manual/en/json.requirements.php>`_

.. warning:: The end of life date for PHP 7.4 was November 28, 2022. If you are
    still using PHP 7.4, you should upgrade immediately. The end of life date
    for PHP 8.0 will be November 26, 2023.

***********************
Optional PHP Extensions
***********************

The following PHP extensions should be enabled on your server:

  - `mysqlnd <https://www.php.net/manual/en/mysqlnd.install.php>`_ (if you use MySQL)
  - `curl <https://www.php.net/manual/en/curl.requirements.php>`_ (if you use :doc:`CURLRequest </libraries/curlrequest>`)
  - `imagick <https://www.php.net/manual/en/imagick.requirements.php>`_ (if you use :doc:`Image </libraries/images>` class ImageMagickHandler)
  - `gd <https://www.php.net/manual/en/image.requirements.php>`_ (if you use :doc:`Image </libraries/images>` class GDHandler)
  - `simplexml <https://www.php.net/manual/en/simplexml.requirements.php>`_ (if you format XML)

The following PHP extensions are required when you use a Cache server:

  - `memcache <https://www.php.net/manual/en/memcache.requirements.php>`_ (if you use :doc:`Cache </libraries/caching>` class MemcachedHandler with Memcache)
  - `memcached <https://www.php.net/manual/en/memcached.requirements.php>`_ (if you use :doc:`Cache </libraries/caching>` class MemcachedHandler with Memcached)
  - `redis <https://github.com/phpredis/phpredis>`_ (if you use :doc:`Cache </libraries/caching>` class RedisHandler)

The following PHP extensions are required when you use PHPUnit:

   - `dom <https://www.php.net/manual/en/dom.requirements.php>`_ (if you use :doc:`TestResponse </testing/response>` class)
   - `libxml <https://www.php.net/manual/en/libxml.requirements.php>`_ (if you use :doc:`TestResponse </testing/response>` class)
   - `xdebug <https://xdebug.org/docs/install>`_ (if you use ``CIUnitTestCase::assertHeaderEmitted()``)

.. _requirements-supported-databases:

*******************
Supported Databases
*******************

A database is required for most web application programming.
Currently supported databases are:

  - MySQL via the ``MySQLi`` driver (version 5.1 and above only)
  - PostgreSQL via the ``Postgre`` driver (version 7.4 and above only)
  - SQLite3 via the ``SQLite3`` driver
  - Microsoft SQL Server via the ``SQLSRV`` driver (version 2005 and above only)
  - Oracle Database via the ``OCI8`` driver (version 12.1 and above only)

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
