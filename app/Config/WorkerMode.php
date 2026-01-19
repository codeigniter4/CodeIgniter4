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
     * - `routes`: Router configuration
     * - `cache`: Cache instance
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
        'routes',
        'cache',
    ];

    /**
     * Force Garbage Collection
     *
     * Whether to force garbage collection after each request.
     * Helps prevent memory leaks at a small performance cost.
     */
    public bool $forceGarbageCollection = true;
}
