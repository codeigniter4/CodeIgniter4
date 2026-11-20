<?php

namespace Config;

/**
 * This configuration controls how CodeIgniter behaves when running
 * in worker mode (with FrankenPHP).
 */
class WorkerMode
{
    /**
     * Persistent Services
     *
     * List of service names that should persist across requests.
     * These services will NOT be reset between requests.
     *
     * Services not in this list will be reset for each request to prevent
     * state leakage.
     *
     * Recommended persistent services:
     * - `autoloader`: PSR-4 autoloading configuration
     * - `locator`: File locator
     * - `exceptions`: Exception handler
     * - `commands`: CLI commands registry
     * - `codeigniter`: Main application instance
     * - `superglobals`: Superglobals wrapper
     * - `cache`: Cache instance
     *
     * The `routes` service is intentionally not persistent. `RouteCollection`
     * reads the request host and drops the routes limited with the `hostname`
     * or `subdomain` options while the route file is loaded. Persisting it
     * would make those decisions once, from the first request the worker
     * served, so route definitions are loaded again for each request.
     *
     * @var list<string>
     */
    public array $persistentServices = [
        'autoloader',
        'locator',
        'exceptions',
        'commands',
        'codeigniter',
        'superglobals',
        'cache',
    ];

    /**
     * Reset Event Listeners
     *
     * List of event names whose listeners should be removed between requests.
     * Use this if you register event listeners inside other event callbacks
     * (rather than at the top level of Config/Events.php), which would cause
     * them to accumulate across requests in worker mode.
     *
     * @var list<string>
     */
    public array $resetEventListeners = [];

    /**
     * Force Garbage Collection
     *
     * Whether to force garbage collection after each request.
     * Helps prevent memory leaks at a small performance cost.
     */
    public bool $forceGarbageCollection = true;
}
