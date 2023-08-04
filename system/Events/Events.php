<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Events;

use Config\Modules;
use Config\Services;

/**
 * Events
 */
class Events
{
    public const PRIORITY_LOW    = 200;
    public const PRIORITY_NORMAL = 100;
    public const PRIORITY_HIGH   = 10;

    /**
     * The list of listeners.
     *
     * @var array
     */
    protected static $listeners = [];

    /**
     * Flag to let us know if we've read from the Config file(s)
     * and have all of the defined events.
     *
     * @var bool
     */
    protected static $initialized = false;

    /**
     * If true, events will not actually be fired.
     * Useful during testing.
     *
     * @var bool
     */
    protected static $simulate = false;

    /**
     * Stores information about the events
     * for display in the debug toolbar.
     *
     * @var array<array<string, float|string>>
     */
    protected static $performanceLog = [];

    /**
     * A list of found files.
     *
     * @var string[]
     */
    protected static $files = [];

    /**
     * Ensures that we have a events file ready.
     *
     * @return void
     */
    public static function initialize()
    {
        // Don't overwrite anything....
        if (static::$initialized) {
            return;
        }

        $config = config(Modules::class);
        $events = APPPATH . 'Config' . DIRECTORY_SEPARATOR . 'Events.php';
        $files  = [];

        if ($config->shouldDiscover('events')) {
            $files = Services::locator()->search('Config/Events.php');
        }

        $files = array_filter(array_map(static function (string $file) {
            if (is_file($file)) {
                return realpath($file) ?: $file;
            }

            return false; // @codeCoverageIgnore
        }, $files));

        static::$files = array_unique(array_merge($files, [$events]));

        foreach (static::$files as $file) {
            include $file;
        }

        static::$initialized = true;
    }

    /**
     * Registers an action to happen on an event. The action can be any sort
     * of callable:
     *
     *  Events::on('create', 'myFunction');               // procedural function
     *  Events::on('create', ['myClass', 'myMethod']);    // Class::method
     *  Events::on('create', [$myInstance, 'myMethod']);  // Method on an existing instance
     *  Events::on('create', function() {});              // Closure
     *
     * @param string   $eventName
     * @param callable $callback
     * @param int      $priority
     *
     * @return void
     */
    public static function on($eventName, $callback, $priority = self::PRIORITY_NORMAL)
    {
        if (! isset(static::$listeners[$eventName])) {
            static::$listeners[$eventName] = [
                true, // If there's only 1 item, it's sorted.
                [$priority],
                [$callback],
            ];
        } else {
            static::$listeners[$eventName][0]   = false; // Not sorted
            static::$listeners[$eventName][1][] = $priority;
            static::$listeners[$eventName][2][] = $callback;
        }
    }

    /**
     * Runs through all subscribed methods running them one at a time,
     * until either:
     *  a) All subscribers have finished or
     *  b) a method returns false, at which point execution of subscribers stops.
     *
     * @param string $eventName
     * @param mixed  $arguments
     */
    public static function trigger($eventName, ...$arguments): bool
    {
        // Read in our Config/Events file so that we have them all!
        if (! static::$initialized) {
            static::initialize();
        }

        $listeners = static::listeners($eventName);

        foreach ($listeners as $listener) {
            $start = microtime(true);

            $result = static::$simulate === false ? $listener(...$arguments) : true;

            if (CI_DEBUG) {
                static::$performanceLog[] = [
                    'start' => $start,
                    'end'   => microtime(true),
                    'event' => strtolower($eventName),
                ];
            }

            if ($result === false) {
                return false;
            }
        }

        return true;
    }

    /**
     * Returns an array of listeners for a single event. They are
     * sorted by priority.
     *
     * @param string $eventName
     */
    public static function listeners($eventName): array
    {
        if (! isset(static::$listeners[$eventName])) {
            return [];
        }

        // The list is not sorted
        if (! static::$listeners[$eventName][0]) {
            // Sort it!
            array_multisort(static::$listeners[$eventName][1], SORT_NUMERIC, static::$listeners[$eventName][2]);

            // Mark it as sorted already!
            static::$listeners[$eventName][0] = true;
        }

        return static::$listeners[$eventName][2];
    }

    /**
     * Removes a single listener from an event.
     *
     * If the listener couldn't be found, returns FALSE, else TRUE if
     * it was removed.
     *
     * @param string $eventName
     */
    public static function removeListener($eventName, callable $listener): bool
    {
        if (! isset(static::$listeners[$eventName])) {
            return false;
        }

        foreach (static::$listeners[$eventName][2] as $index => $check) {
            if ($check === $listener) {
                unset(
                    static::$listeners[$eventName][1][$index],
                    static::$listeners[$eventName][2][$index]
                );

                return true;
            }
        }

        return false;
    }

    /**
     * Removes all listeners.
     *
     * If the event_name is specified, only listeners for that event will be
     * removed, otherwise all listeners for all events are removed.
     *
     * @param string|null $eventName
     *
     * @return void
     */
    public static function removeAllListeners($eventName = null)
    {
        if ($eventName !== null) {
            unset(static::$listeners[$eventName]);
        } else {
            static::$listeners = [];
        }
    }

    /**
     * Sets the path to the file that routes are read from.
     *
     * @return void
     */
    public static function setFiles(array $files)
    {
        static::$files = $files;
    }

    /**
     * Returns the files that were found/loaded during this request.
     *
     * @return string[]
     */
    public static function getFiles()
    {
        return static::$files;
    }

    /**
     * Turns simulation on or off. When on, events will not be triggered,
     * simply logged. Useful during testing when you don't actually want
     * the tests to run.
     *
     * @return void
     */
    public static function simulate(bool $choice = true)
    {
        static::$simulate = $choice;
    }

    /**
     * Getter for the performance log records.
     *
     * @return array<array<string, float|string>>
     */
    public static function getPerformanceLogs()
    {
        return static::$performanceLog;
    }
}
