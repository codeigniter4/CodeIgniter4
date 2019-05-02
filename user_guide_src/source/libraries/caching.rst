##############
Caching Driver
##############

CodeIgniter features wrappers around some of the most popular forms of
fast and dynamic caching. All but file-based caching require specific
server requirements, and a Fatal Exception will be thrown if server
requirements are not met.

.. contents::
    :local:
    :depth: 2

*************
Example Usage
*************

The following example shows a common usage pattern within your controllers.

::

	if ( ! $foo = cache('foo'))
	{
		echo 'Saving to the cache!<br />';
		$foo = 'foobarbaz!';

		// Save into the cache for 5 minutes
		cache()->save('foo', $foo, 300);
	}

	echo $foo;

You can grab an instance of the cache engine directly through the Services class::

    $cache = \Config\Services::cache();

    $foo = $cache->get('foo');

=====================
Configuring the Cache
=====================

All configuration for the cache engine is done in **app/Config/Cache.php**. In that file,
the following items are available.

**$handler**

The is the name of the handler that should be used as the primary handler when starting up the engine.
Available names are: dummy, file, memcached, redis, wincache.

**$backupHandler**

In the case that the first choice $hanlder is not available, this is the next cache handler to load.
This is commonly the **file** handler since the file system is always available, but may not fit
more complex, multi-server setups.

**$prefix**

If you have more than one application using the same cache storage, you can add a custom prefix
here that is prepended to all key names.

**$path**

This is used by the ``file`` handler to show where it should save the cache files to.

**$memcached**

This is an array of servers that will be used when using the ``Memcache(d)`` handler.

**$redis**

The settings for the Redis server that you wish to use when using the ``Redis`` handler.

***************
Class Reference
***************

.. php:method:: isSupported()

	:returns:	TRUE if supported, FALSE if not
	:rtype:	bool

.. php:method:: get($key)

	:param	string	$key: Cache item name
	:returns:	Item value or NULL if not found
	:rtype:	mixed

	This method will attempt to fetch an item from the cache store. If the
	item does not exist, the method will return NULL.

	Example::

		$foo = $cache->get('my_cached_item');

.. php:method:: save($key, $data[, $ttl = 60[, $raw = FALSE]])

	:param	string	$key: Cache item name
	:param	mixed	$data: the data to save
	:param	int	$ttl: Time To Live, in seconds (default 60)
	:param	bool	$raw: Whether to store the raw value
	:returns:	TRUE on success, FALSE on failure
	:rtype:	string

	This method will save an item to the cache store. If saving fails, the
	method will return FALSE.

	Example::

		$cache->save('cache_item_id', 'data_to_cache');

.. note:: The ``$raw`` parameter is only utilized by Memcache,
		  in order to allow usage of ``increment()`` and ``decrement()``.

.. php:method:: delete($key)

	:param	string	$key: name of cached item
	:returns:	TRUE on success, FALSE on failure
	:rtype:	bool

	This method will delete a specific item from the cache store. If item
	deletion fails, the method will return FALSE.

	Example::

		$cache->delete('cache_item_id');

.. php:method:: increment($key[, $offset = 1])

	:param	string	$key: Cache ID
	:param	int	$offset: Step/value to add
	:returns:	New value on success, FALSE on failure
   	:rtype:	mixed

	Performs atomic incrementation of a raw stored value.

	Example::

		// 'iterator' has a value of 2

		$cache->increment('iterator'); // 'iterator' is now 3

		$cache->increment('iterator', 3); // 'iterator' is now 6

.. php:method:: decrement($key[, $offset = 1])

	:param	string	$key: Cache ID
	:param	int	$offset: Step/value to reduce by
	:returns:	New value on success, FALSE on failure
	:rtype:	mixed

	Performs atomic decrementation of a raw stored value.

	Example::

		// 'iterator' has a value of 6

		$cache->decrement('iterator'); // 'iterator' is now 5

		$cache->decrement('iterator', 2); // 'iterator' is now 3

.. php:method:: clean()

	:returns:	TRUE on success, FALSE on failure
	:rtype:	bool

	This method will 'clean' the entire cache. If the deletion of the
	cache files fails, the method will return FALSE.

	Example::

			$cache->clean();

.. php:method:: cache_info()

	:returns:	Information on the entire cache database
	:rtype:	mixed

	This method will return information on the entire cache.

	Example::

		var_dump($cache->cache_info());

.. note:: The information returned and the structure of the data is dependent
		  on which adapter is being used.

.. php:method:: getMetadata($key)

	:param	string	$key: Cache item name
	:returns:	Metadata for the cached item
	:rtype:	mixed

	This method will return detailed information on a specific item in the
	cache.

	Example::

		var_dump($cache->getMetadata('my_cached_item'));

.. note:: The information returned and the structure of the data is dependent
          on which adapter is being used.

*******
Drivers
*******

==================
File-based Caching
==================

Unlike caching from the Output Class, the driver file-based caching
allows for pieces of view files to be cached. Use this with care, and
make sure to benchmark your application, as a point can come where disk
I/O will negate positive gains by caching. This requires a writable cache directory to be really writable (0777).

=================
Memcached Caching
=================

Multiple Memcached servers can be specified in the cache configuration file.

For more information on Memcached, please see
`http://php.net/memcached <http://php.net/memcached>`_.

================
WinCache Caching
================

Under Windows, you can also utilize the WinCache driver.

For more information on WinCache, please see
`http://php.net/wincache <http://php.net/wincache>`_.

=============
Redis Caching
=============

Redis is an in-memory key-value store which can operate in LRU cache mode.
To use it, you need `Redis server and phpredis PHP extension <https://github.com/phpredis/phpredis>`_.

Config options to connect to redis server must be stored in the app/Config/redis.php file.
Available options are::

	$config['host'] = '127.0.0.1';
	$config['password'] = NULL;
	$config['port'] = 6379;
	$config['timeout'] = 0;
	$config['database'] = 0;

For more information on Redis, please see
`http://redis.io <http://redis.io>`_.

===========
Dummy Cache
===========

This is a caching backend that will always 'miss.' It stores no data,
but lets you keep your caching code in place in environments that don't
support your chosen cache.
