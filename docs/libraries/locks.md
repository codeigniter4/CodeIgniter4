# Atomic Locks

!!! success "Available from version 4.8.0"

Atomic locks provide a simple way to prevent the same task from running concurrently across requests, CLI commands, or workers that share the same cache storage.

Locks are advisory. Your code must acquire the lock before entering the critical section, and release it when the work is finished.

## Configuration

The Locks library uses the Cache service. The cache handler must support owner-aware lock operations. The built-in **File**, **Redis**, and **Predis** cache handlers support atomic lock operations. The **Memcached** cache handler also supports locks, with Memcached-specific limitations described below.

!!! note "Note"
    Locks are most useful when all competing processes share the same cache storage. The File handler is suitable for a single server. For multiple application servers, use a shared handler such as Redis.

!!! important "Important"
    Locks are stored in the configured cache handler. Clearing or flushing that cache storage, for example with `cache()->clean()` or a Redis `FLUSHDB`, may remove active locks. Avoid clearing shared lock storage while lock-protected work is running, or use a dedicated cache store for locks when that separation is important.

!!! note "Note"
    Memcached lock support requires the `memcached` PHP extension. The older `memcache` extension does not provide the CAS operations used for owner-aware refresh and best-effort release. Memcached does not provide an atomic compare-and-delete command, so releasing a Memcached lock cannot provide the same owner-checked atomic release guarantee as Redis or Predis. A delayed release may delete a lock that expired and was acquired by another owner. Memcached locks may also be lost if the Memcached server restarts, evicts keys, or flushes its cache.

!!! note "Note"
    File-backed locks clear released and expired lock contents, but may leave empty lock files in the cache directory. These files do not represent active locks and may be removed by normal cache cleanup when no lock-protected work is running.

## Example Usage

You can create a lock through the `locks` service. The second argument is the lock TTL, in seconds. The TTL prevents abandoned locks from being held forever if a process exits unexpectedly.

```php
<?php

$lock = service('locks')->create('reports.daily-export', 300);

if (! $lock->acquire()) {
    return;
}

try {
    // Run the work that must not overlap.
} finally {
    $lock->release();
}
```

!!! warning "Warning"
    If the work takes longer than the lock TTL, another process may acquire the same lock while the first process is still running. For long-running work, choose a TTL that comfortably covers the operation, call `refresh()` while the lock is held, or check `isAcquired()` before performing irreversible side effects.

### Running a Callback

The `run()` method acquires the lock, runs the callback, and releases the lock in a `finally` block.

```php
<?php

$result = service('locks')
    ->create('reports.daily-export', 300)
    ->run(static fn () => build_daily_report());
```

If the lock cannot be acquired, `run()` returns `false` and the callback is not called.

### Blocking

The `block()` method waits up to the given number of seconds for the lock to become available:

```php
<?php

$lock = service('locks')->create('imports.customer-feed', 300);

if ($lock->block(10)) {
    try {
        import_customer_feed();
    } finally {
        $lock->release();
    }
}
```

### Restoring a Lock by Owner

Each acquired lock has an owner token. You may pass this token to another process and restore the lock there, for example to release a lock from a queued worker that continues work started by the current request.

```php
<?php

$lock = service('locks')->create('exports.monthly', 300);

if ($lock->acquire()) {
    queue_export_job($lock->owner());
}

// Later, in another process:
$restored = service('locks')->restore('exports.monthly', $owner);
$restored->release();
```

## Locks and Cache Handlers

The default File cache handler supports locks, so locks work without additional configuration in a standard application.

If the configured cache handler does not support locks, resolving the `locks` service or constructing a lock manager throws a `CodeIgniter\Lock\Exceptions\LockException`.

Custom cache handlers can support locks by implementing `CodeIgniter\Cache\LockStoreProviderInterface` and returning a `CodeIgniter\Cache\LockStoreInterface` instance. This keeps lock support opt-in and does not require all cache handlers to implement lock operations.

Custom lock stores must implement owner-aware acquisition, release, refresh, force release, and owner lookup methods. `acquireLock()` should atomically claim the lock for an owner token and TTL. `releaseLock()` and `refreshLock()` must only affect the lock when the supplied owner token still matches the current owner. `forceReleaseLock()` intentionally ignores ownership, and `getLockOwner()` should return `null` when the lock is absent or expired.

## Class Reference

## CodeIgniter\Lock

### LockManager

#### create(string $name[, int $ttl = 300[, ?string $owner = null]])

- **Parameters**  
    - **$name** `string` The logical lock name.
    - **$ttl** `int` Number of seconds before the lock expires.
    - **$owner** `string|null` Optional owner token.

- **Returns**: A lock instance.

- **Return type**: `LockInterface`

Creates a lock for the given logical name.

#### restore(string $name, string $owner[, int $ttl = 300])

- **Parameters**  
    - **$name** `string` The logical lock name.
    - **$owner** `string` The owner token.
    - **$ttl** `int` Number of seconds before the lock expires.

- **Returns**: A lock instance.

- **Return type**: `LockInterface`

Restores a lock instance for an existing owner token.

### LockInterface

#### acquire()

- **Returns**: `true` if the lock was acquired, `false` otherwise.
- **Return type**: `bool`

#### block(int $seconds)

- **Parameters**  
    - **$seconds** `int` Maximum number of seconds to wait.

- **Returns**: `true` if the lock was acquired, `false` otherwise.

- **Return type**: `bool`

#### run(Closure $callback[, int $waitSeconds = 0])

- **Parameters**  
    - **$callback** `Closure` The callback to run while the lock is held.
    - **$waitSeconds** `int` Maximum number of seconds to wait.

- **Returns**: The callback result, or `false` if the lock was not acquired.

- **Return type**: `mixed`

#### release()

- **Returns**: `true` if the lock was released by its owner.
- **Return type**: `bool`

#### forceRelease()

- **Returns**: `true` if the lock was force released.
- **Return type**: `bool`

Releases the lock without checking the owner token.

#### refresh([?int $ttl = null])

- **Parameters**  
    - **$ttl** `int|null` Number of seconds before the lock expires.

- **Returns**: `true` if the owned lock was refreshed.

- **Return type**: `bool`

#### isAcquired()

- **Returns**: `true` if this lock instance still owns the lock.
- **Return type**: `bool`

#### owner()

- **Returns**: The owner token.
- **Return type**: `string`
