############
Atomic Locks
############

.. versionadded:: 4.8.0

.. contents::
    :local:
    :depth: 2

Atomic locks provide a simple way to prevent the same task from running
concurrently across requests, CLI commands, or workers that share the same
cache storage.

Locks are advisory. Your code must acquire the lock before entering the
critical section, and release it when the work is finished.

*************
Configuration
*************

The Locks library uses the Cache service. The cache handler must support atomic
lock operations. The built-in **File**, **Redis**, and **Predis** cache handlers
support locks.

.. note:: Locks are most useful when all competing processes share the same cache
    storage. The File handler is suitable for a single server. For multiple
    application servers, use a shared handler such as Redis.

.. important:: Locks are stored in the configured cache handler. Clearing or
    flushing that cache storage, for example with ``cache()->clean()`` or a
    Redis ``FLUSHDB``, may remove active locks. Avoid clearing shared lock
    storage while lock-protected work is running, or use a dedicated cache
    store for locks when that separation is important.

.. note:: File-backed locks clear released and expired lock contents, but may
    leave empty lock files in the cache directory. These files do not represent
    active locks and may be removed by normal cache cleanup when no
    lock-protected work is running.

*************
Example Usage
*************

You can create a lock through the ``locks`` service. The second argument is the
lock TTL, in seconds. The TTL prevents abandoned locks from being held forever
if a process exits unexpectedly.

.. literalinclude:: locks/001.php

.. warning:: If the work takes longer than the lock TTL, another process may
    acquire the same lock while the first process is still running. For
    long-running work, choose a TTL that comfortably covers the operation, call
    ``refresh()`` while the lock is held, or check ``isAcquired()`` before
    performing irreversible side effects.

Running a Callback
==================

The ``run()`` method acquires the lock, runs the callback, and releases the lock
in a ``finally`` block.

.. literalinclude:: locks/002.php

If the lock cannot be acquired, ``run()`` returns ``false`` and the callback is
not called.

Blocking
========

The ``block()`` method waits up to the given number of seconds for the lock to
become available:

.. literalinclude:: locks/003.php

Restoring a Lock by Owner
=========================

Each acquired lock has an owner token. You may pass this token to another
process and restore the lock there, for example to release a lock from a queued
worker that continues work started by the current request.

.. literalinclude:: locks/004.php

************************
Locks and Cache Handlers
************************

The default File cache handler supports locks, so locks work without additional
configuration in a standard application.

If the configured cache handler does not support locks, resolving the ``locks``
service or constructing a lock manager throws a
``CodeIgniter\Lock\Exceptions\LockException``.

Custom cache handlers can support locks by implementing
``CodeIgniter\Cache\LockStoreProviderInterface`` and returning a
``CodeIgniter\Cache\LockStoreInterface`` instance. This keeps lock support
opt-in and does not require all cache handlers to implement lock operations.

Custom lock stores must implement owner-aware acquisition, release, refresh,
force release, and owner lookup methods. ``acquireLock()`` should atomically
claim the lock for an owner token and TTL. ``releaseLock()`` and
``refreshLock()`` must only affect the lock when the supplied owner token still
matches the current owner. ``forceReleaseLock()`` intentionally ignores
ownership, and ``getLockOwner()`` should return ``null`` when the lock is absent
or expired.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Lock

.. php:class:: LockManager

    .. php:method:: create(string $name[, int $ttl = 300[, ?string $owner = null]])

        :param string $name: The logical lock name.
        :param int $ttl: Number of seconds before the lock expires.
        :param string|null $owner: Optional owner token.
        :returns: A lock instance.
        :rtype: LockInterface

        Creates a lock for the given logical name.

    .. php:method:: restore(string $name, string $owner[, int $ttl = 300])

        :param string $name: The logical lock name.
        :param string $owner: The owner token.
        :param int $ttl: Number of seconds before the lock expires.
        :returns: A lock instance.
        :rtype: LockInterface

        Restores a lock instance for an existing owner token.

.. php:interface:: LockInterface

    .. php:method:: acquire()

        :returns: ``true`` if the lock was acquired, ``false`` otherwise.
        :rtype: bool

    .. php:method:: block(int $seconds)

        :param int $seconds: Maximum number of seconds to wait.
        :returns: ``true`` if the lock was acquired, ``false`` otherwise.
        :rtype: bool

    .. php:method:: run(Closure $callback[, int $waitSeconds = 0])

        :param Closure $callback: The callback to run while the lock is held.
        :param int $waitSeconds: Maximum number of seconds to wait.
        :returns: The callback result, or ``false`` if the lock was not acquired.
        :rtype: mixed

    .. php:method:: release()

        :returns: ``true`` if the lock was released by its owner.
        :rtype: bool

    .. php:method:: forceRelease()

        :returns: ``true`` if the lock was force released.
        :rtype: bool

        Releases the lock without checking the owner token.

    .. php:method:: refresh([?int $ttl = null])

        :param int|null $ttl: Number of seconds before the lock expires.
        :returns: ``true`` if the owned lock was refreshed.
        :rtype: bool

    .. php:method:: isAcquired()

        :returns: ``true`` if this lock instance still owns the lock.
        :rtype: bool

    .. php:method:: owner()

        :returns: The owner token.
        :rtype: string
