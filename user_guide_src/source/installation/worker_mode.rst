###########
Worker Mode
###########

.. contents::
    :local:
    :depth: 2

.. versionadded:: 4.7.0

.. important:: Worker Mode is currently **experimental**. The only officially supported
    worker implementation is **FrankenPHP**, which is backed by the PHP Foundation.

************
Introduction
************

What is Worker Mode?
====================

Worker Mode is a performance optimization feature that allows CodeIgniter to handle multiple
HTTP requests within the same PHP process, instead of starting a new process for each request
like traditional PHP-FPM does.

Traditional PHP vs Worker Mode
------------------------------

**Traditional PHP (PHP-FPM)**

In traditional PHP, each HTTP request goes through this cycle:

1. Web server receives request and spawns a new PHP process
2. PHP loads and parses all required files
3. Framework bootstraps: autoloader, configuration, services, routes
4. Database connections are established
5. Request is processed and response is sent
6. All connections are closed
7. PHP process terminates, freeing all memory

This "shared nothing" architecture is simple and safe, but inefficient for high-traffic
applications because steps 2-4 repeat for every single request.

**Worker Mode**

In Worker Mode, the lifecycle changes dramatically:

1. Worker process starts and performs one-time initialization:

   - Loads and parses all required files (cached in OPcache)
   - Bootstraps framework: autoloader, configuration, services, routes
   - Establishes database and cache connections

2. For each incoming request:

   - Reuses existing connections and cached resources
   - Resets only request-specific state (superglobals, request/response objects)
   - Processes request and sends response
   - Cleans up request-specific data

3. Worker continues running, ready for the next request

This approach eliminates redundant initialization work and connection overhead,
resulting in **2-3x performance improvements** for typical database-driven applications.

How CodeIgniter Manages State
=============================

The key challenge in Worker Mode is preventing state from leaking between requests.
CodeIgniter handles this through several mechanisms:

**Services Reset**
    Most services are destroyed and recreated for each request. Only services listed
    in ``$persistentServices`` survive between requests.

**Factories Reset**
    All Factories (Models, Config instances) are reset between requests,
    ensuring fresh instances without stale data.

**Superglobals Isolation**
    Request data (``$_GET``, ``$_POST``, ``$_SERVER``, etc.) is properly isolated
    per request through the Superglobals service.

**Connection Persistence**
    Database and cache connections are validated at request start and reused if healthy.
    Unhealthy connections are automatically re-established.

***************
Getting Started
***************

Installation
============

1. Install FrankenPHP following the `official documentation <https://frankenphp.dev/>`_

   You can use a static binary or Docker. Here, we will assume we use a static binary,
   which can be `downloaded directly <https://github.com/php/frankenphp/releases/>`_.

2. Install the Worker Mode template files using the spark command:

   .. code-block:: console

       php spark worker:install

   This command creates two files:

   - **Caddyfile** - FrankenPHP configuration file with worker mode enabled
   - **public/frankenphp-worker.php** - Worker entry point that handles the request loop

3. Configure your worker settings in **app/Config/WorkerMode.php** if needed.
   The defaults are recommended for most applications.

Running the Worker
==================

Start FrankenPHP using the generated Caddyfile:

.. code-block:: console

    frankenphp run

The server will start with worker mode enabled, handling requests through the worker entry point.

To run in the background (daemon mode):

.. code-block:: console

    frankenphp start

Uninstalling
============

To remove the Worker Mode template files:

.. code-block:: console

    php spark worker:uninstall

This removes the **Caddyfile** and **public/frankenphp-worker.php** files.

**********************
Performance Benchmarks
**********************

Worker Mode typically provides **2-3x performance improvements** for database-driven
applications. The actual gains depend on your application's characteristics:

===================== =====================================================================
Scenario              Expected Improvement
===================== =====================================================================
**Simple endpoints**  Applications returning minimal JSON responses show modest
                      improvements (10-30%), as there's little bootstrap overhead
                      to eliminate.
**Database queries**  Endpoints performing database queries typically see **2-3x**
                      improvements from connection reuse and reduced initialization
                      overhead.
**Complex bootstrap** Applications with many services, routes, or configuration see
                      the largest gains, as this overhead is eliminated entirely.
===================== =====================================================================

Worker Count Considerations
===========================

Total throughput scales with worker count. Match worker count to your typical concurrency
patterns and available CPU cores. Too few workers limit concurrency; too many waste memory.

First Request Latency
=====================

The initial request to each worker is slower due to bootstrap and connection establishment.
Subsequent requests benefit from cached resources and persistent connections.

*************
Configuration
*************

All worker mode configuration is managed through the **app/Config/WorkerMode.php** file.

Configuration Options
=====================

=========================== ======= ======================================================================
Option                      Type    Description
=========================== ======= ======================================================================
**$persistentServices**     array   Services that persist across requests and are not reset. Services not
                                    in this list are destroyed after each request to prevent state leakage.
                                    Default: ``['autoloader', 'locator', 'exceptions', 'commands',
                                    'codeigniter', 'superglobals', 'routes', 'cache']``
**$forceGarbageCollection** bool    Whether to force garbage collection after each request.
                                    ``true`` (default, recommended): Prevents memory leaks.
                                    ``false``: Relies on PHP's automatic garbage collection.
=========================== ======= ======================================================================

Persistent Services
===================

The ``$persistentServices`` array controls which services survive between requests.
The default configuration includes:

================ ==========================================================================
Service          Purpose
================ ==========================================================================
``autoloader``   PSR-4 autoloading configuration. Safe to persist as class maps don't change.
``locator``      File locator for finding framework files. Caches file paths for performance.
``exceptions``   Exception handler. Stateless, safe to reuse.
``commands``     CLI commands registry. Only used during worker startup.
``codeigniter``  Main application instance. Orchestrates the request/response cycle.
``superglobals`` Superglobals wrapper. Properly isolated per request internally.
``routes``       Router configuration. Route definitions don't change between requests.
``cache``        Cache service. Maintains connections to cache backends (Redis, Memcached).
================ ==========================================================================

.. warning:: Adding services to ``$persistentServices`` without understanding their
    state management can cause data leakage between requests. Only persist services
    that are truly stateless or manage their own request isolation.

**********************
Optimize Configuration
**********************

The ``app/Config/Optimize.php`` configuration options (Config Caching and File Locator Caching)
should **NOT** be used with Worker Mode.

These optimizations were designed for traditional PHP where each request starts fresh. In Worker
Mode, the persistent process already provides these benefits naturally, and enabling them can
cause issues with stale data.

.. warning:: Do not enable ``$configCacheEnabled`` or ``$locatorCacheEnabled`` in
    **app/Config/Optimize.php** when using Worker Mode.

OPcache Settings
================

Worker Mode benefits significantly from OPcache. Ensure it's enabled and properly configured:

.. code-block:: ini

    opcache.enable=1
    opcache.memory_consumption=256
    opcache.max_accelerated_files=20000
    opcache.validate_timestamps=0  ; In production only

************************
Important Considerations
************************

State Management
================

Since the PHP process persists across requests, careful attention to state management is essential:

- **Avoid static properties** for request-specific data, as these persist between requests
- **Be cautious with singletons** that might retain request state
- **Clean up resources** like file handles and database cursors after each request
- **Don't store user data** in class properties that persist across requests

Registrars
==========

Config registrars (``Config/Registrar.php`` files) work correctly in Worker Mode.
Registrar files are discovered once when the worker starts, and registrar logic is applied
every time a Config class is instantiated. Each request gets a fresh Config instance with
registrars applied.

Memory Management
=================

Monitor memory usage:

.. code-block:: console

    ps aux | grep frankenphp

If memory grows continuously:

- Verify garbage collection is enabled (``$forceGarbageCollection = true``)
- Ensure resources are properly closed after use
- Unset large objects when no longer needed
- Check for circular references that prevent garbage collection

Debugging
=========

Worker Mode can make debugging more challenging since the process persists across requests:

- **Restart workers after code changes** - workers don't auto-reload modified files
- **Check logs** in **writable/logs/** for connection and state-related issues
- **Use logging liberally** to trace request flow across the persistent process

Session and Cache Handlers
==========================

**File-based handlers** may experience file locking contention between workers.
For production environments, consider using:

- **Redis** or **Memcached** for sessions and cache
- These provide better concurrency and automatic connection persistence

Database Connections
====================

Database connections are automatically managed:

- Connections persist across requests for performance
- Connections are validated at the start of each request
- Failed connections are automatically re-established
- Uncommitted transactions are automatically rolled back with a warning logged
