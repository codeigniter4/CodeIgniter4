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

    if (! $foo = cache('foo'))
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
Available names are: dummy, file, memcached, redis, predis, wincache.

**$backupHandler**

In the case that the first choice $handler is not available, this is the next cache handler to load.
This is commonly the **file** handler since the file system is always available, but may not fit
more complex, multi-server setups.

**$prefix**

If you have more than one application using the same cache storage, you can add a custom prefix
string here that is prepended to all key names.

**$ttl**

The default number of seconds to save items when none is specified.
WARNING: This is not used by framework handlers where 60 seconds is hard-coded, but may be useful
to projects and modules. This will replace the hard-coded value in a future release.

**$file**

This is an array of settings specific to the  ``File`` handler to determine how it should save the cache files.

**$memcached**

This is an array of servers that will be used when using the ``Memcache(d)`` handler.

**$redis**

The settings for the Redis server that you wish to use when using the ``Redis`` and ``Predis`` handler.

***************
Class Reference
***************

.. php:method:: isSupported()

    :returns: ``true`` if supported, ``false`` if not
    :rtype: bool

.. php:method:: get($key): mixed

    :param string $key: Cache item name
    :returns: Item value or ``null`` if not found
    :rtype: mixed

    This method will attempt to fetch an item from the cache store. If the
    item does not exist, the method will return NULL.

    Example::

        $foo = $cache->get('my_cached_item');

.. php:method:: remember(string $key, int $ttl, Closure $callback)

    :param string $key: Cache item name
    :param int $ttl: Time to live in seconds
    :param Closure $callback: Callback to invoke when the cache item returns null
    :returns: The value of the cache item
    :rtype: mixed
    
    Gets an item from the cache. If ``null`` was returned, this will invoke the callback
    and save the result. Either way, this will return the value.

.. php:method:: save(string $key, $data[, int $ttl = 60[, $raw = false]])

    :param string $key: Cache item name
    :param mixed $data: the data to save
    :param int $ttl: Time To Live, in seconds (default 60)
    :param bool $raw: Whether to store the raw value
    :returns: ``true`` on success, ``false`` on failure
    :rtype: bool

    This method will save an item to the cache store. If saving fails, the
    method will return ``false``.

    Example::

        $cache->save('cache_item_id', 'data_to_cache');

.. note:: The ``$raw`` parameter is only utilized by Memcache,
    in order to allow usage of ``increment()`` and ``decrement()``.

.. php:method:: delete($key): bool

    :param string $key: name of cached item
    :returns: ``true`` on success, ``false`` on failure
    :rtype: bool

    This method will delete a specific item from the cache store. If item
    deletion fails, the method will return FALSE.

    Example::

        $cache->delete('cache_item_id');

.. php:method:: deleteMatching($pattern): integer

    :param string $pattern: glob-style pattern to match cached items keys
    :returns: number of deleted items
    :rtype: integer

    This method will delete multiple items from the cache store at once by
    matching their keys against a glob-style pattern. It will return the total number of deleted items.

    .. important:: This method is only implemented for File, Redis and Predis handlers.
        Due to limitations, it couldn't be implemented for Memcached and Wincache handlers.

    Example::

        $cache->deleteMatching('prefix_*'); // deletes all items of which keys start with "prefix_"
        $cache->deleteMatching('*_suffix'); // deletes all items of which keys end with "_suffix"

    For more information on glob-style syntax, please see
        `https://en.wikipedia.org/wiki/Glob_(programming) <https://en.wikipedia.org/wiki/Glob_(programming)#Syntax>`_.

.. php:method:: increment($key[, $offset = 1]): mixed

    :param string $key: Cache ID
    :param int $offset: Step/value to add
    :returns: New value on success, ``false`` on failure
    :rtype: mixed

    Performs atomic incrementation of a raw stored value.

    Example::

        // 'iterator' has a value of 2
        $cache->increment('iterator');    // 'iterator' is now 3
        $cache->increment('iterator', 3); // 'iterator' is now 6

.. php:method:: decrement($key[, $offset = 1]): mixed

    :param string $key: Cache ID
    :param int $offset: Step/value to reduce by
    :returns: New value on success, ``false`` on failure
    :rtype: mixed

    Performs atomic decrementation of a raw stored value.

    Example::

        // 'iterator' has a value of 6
        $cache->decrement('iterator');    // 'iterator' is now 5
        $cache->decrement('iterator', 2); // 'iterator' is now 3

.. php:method:: clean()

    :returns: ``true`` on success, ``false`` on failure
    :rtype: bool

    This method will 'clean' the entire cache. If the deletion of the
    cache files fails, the method will return FALSE.

    Example::

        $cache->clean();

.. php:method:: getCacheInfo()

    :returns: Information on the entire cache database
    :rtype: mixed

    This method will return information on the entire cache.

    Example::

        var_dump($cache->getCacheInfo());

.. note:: The information returned and the structure of the data is dependent
    on which adapter is being used.

.. php:method:: getMetadata(string $key)

    :param string $key: Cache item name
    :returns: Metadata for the cached item. ``null`` for missing items, or an array with at least the "expire" key for absolute epoch expiry (``null`` for never expires).
    :rtype: array|null

    This method will return detailed information on a specific item in the
    cache.

    Example::

        var_dump($cache->getMetadata('my_cached_item'));

.. note:: The information returned and the structure of the data is dependent
          on which adapter is being used. Some adapters (File, Memcached, Wincache)
          still return ``false`` for missing items.

.. php:staticmethod:: validateKey(string $key, string $prefix)

    :param string $key: Potential cache key
    :param string $prefix: Optional prefix
    :returns: The verified and prefixed key. If the key exceeds the driver's max key length it will be hashed.
    :rtype: string

    This method is used by handler methods to check that keys are valid. It will throw
    an ``InvalidArgumentException`` for non-strings, invalid characters, and empty lengths.

    Example::

        $prefixedKey = BaseHandler::validateKey($key, $prefix);


*******
Drivers
*******

==================
File-based Caching
==================

Unlike caching from the Output Class, the driver file-based caching
allows for pieces of view files to be cached. Use this with care, and
make sure to benchmark your application, as a point can come where disk
I/O will negate positive gains by caching. This requires a cache
directory to be really writable by the application.

=================
Memcached Caching
=================

Memcached servers can be specified in the cache configuration file. Available options are::

    public $memcached = [
        'host'   => '127.0.0.1',
        'port'   => 11211,
        'weight' => 1,
        'raw'    => false,
    ];

For more information on Memcached, please see
`https://www.php.net/memcached <https://www.php.net/memcached>`_.

================
WinCache Caching
================

Under Windows, you can also utilize the WinCache driver.

For more information on WinCache, please see
`https://www.php.net/wincache <https://www.php.net/wincache>`_.

=============
Redis Caching
=============

Redis is an in-memory key-value store which can operate in LRU cache mode.
To use it, you need `Redis server and phpredis PHP extension <https://github.com/phpredis/phpredis>`_.

Config options to connect to redis server stored in the cache configuration file. Available options are::

    public $redis = [
        'host'     => '127.0.0.1',
        'password' => null,
        'port'     => 6379,
        'timeout'  => 0,
        'database' => 0,
    ];

For more information on Redis, please see
`https://redis.io <https://redis.io>`_.

==============
Predis Caching
==============

Predis is a flexible and feature-complete PHP client library for the Redis key-value store.
To use it, from the command line inside your project root::

    composer require predis/predis

For more information on Redis, please see
`https://github.com/nrk/predis <https://github.com/nrk/predis>`_.

===========
Dummy Cache
===========

This is a caching backend that will always 'miss.' It stores no data,
but lets you keep your caching code in place in environments that don't
support your chosen cache.
