<?php

/**
 * CodeIgniter
 *
 * An open source application development framework for PHP
 *
 * This content is released under the MIT License (MIT)
 *
 * Copyright (c) 2014-2019 British Columbia Institute of Technology
 * Copyright (c) 2019-2020 CodeIgniter Foundation
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @package    CodeIgniter
 * @author     CodeIgniter Dev Team
 * @copyright  2019-2020 CodeIgniter Foundation
 * @license    https://opensource.org/licenses/MIT	MIT License
 * @link       https://codeigniter.com
 * @since      Version 4.0.0
 * @filesource
 */

namespace CodeIgniter\Events;

use Config\Services;

define('EVENT_PRIORITY_LOW', 200);
define('EVENT_PRIORITY_NORMAL', 100);
define('EVENT_PRIORITY_HIGH', 10);

/**
 * Events
 */
class Events
{

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
	 * @var boolean
	 */
	protected static $initialized = false;

	/**
	 * If true, events will not actually be fired.
	 * Useful during testing.
	 *
	 * @var boolean
	 */
	protected static $simulate = false;

	/**
	 * Stores information about the events
	 * for display in the debug toolbar.
	 *
	 * @var array
	 */
	protected static $performanceLog = [];

	/**
	 * A list of found files.
	 *
	 * @var array
	 */
	protected static $files = [];

	//--------------------------------------------------------------------

	/**
	 * Ensures that we have a events file ready.
	 */
	public static function initialize()
	{
		// Don't overwrite anything....
		if (static::$initialized)
		{
			return;
		}

		$config = config('Modules');

		$files = [APPPATH . 'Config/Events.php'];

		if ($config->shouldDiscover('events'))
		{
			$locator = Services::locator();
			$files   = $locator->search('Config/Events.php');
		}

		static::$files = $files;

		foreach (static::$files as $file)
		{
			if (is_file($file))
			{
				include $file;
			}
		}

		static::$initialized = true;
	}

	//--------------------------------------------------------------------

	/**
	 * Registers an action to happen on an event. The action can be any sort
	 * of callable:
	 *
	 *  Events::on('create', 'myFunction');               // procedural function
	 *  Events::on('create', ['myClass', 'myMethod']);    // Class::method
	 *  Events::on('create', [$myInstance, 'myMethod']);  // Method on an existing instance
	 *  Events::on('create', function() {});              // Closure
	 *
	 * @param string   $event_name
	 * @param callable $callback
	 * @param integer  $priority
	 */
	public static function on($event_name, $callback, $priority = EVENT_PRIORITY_NORMAL)
	{
		if (! isset(static::$listeners[$event_name]))
		{
			static::$listeners[$event_name] = [
				true, // If there's only 1 item, it's sorted.
				[$priority],
				[$callback],
			];
		}
		else
		{
			static::$listeners[$event_name][0]   = false; // Not sorted
			static::$listeners[$event_name][1][] = $priority;
			static::$listeners[$event_name][2][] = $callback;
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Runs through all subscribed methods running them one at a time,
	 * until either:
	 *  a) All subscribers have finished or
	 *  b) a method returns false, at which point execution of subscribers stops.
	 *
	 * @param string $eventName
	 * @param mixed  $arguments
	 *
	 * @return boolean
	 */
	public static function trigger($eventName, ...$arguments): bool
	{
		// Read in our Config/events file so that we have them all!
		if (! static::$initialized)
		{
			static::initialize();
		}

		$listeners = static::listeners($eventName);

		foreach ($listeners as $listener)
		{
			$start = microtime(true);

			$result = static::$simulate === false ? call_user_func($listener, ...$arguments) : true;

			if (CI_DEBUG)
			{
				static::$performanceLog[] = [
					'start' => $start,
					'end'   => microtime(true),
					'event' => strtolower($eventName),
				];
			}

			if ($result === false)
			{
				return false;
			}
		}

		return true;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns an array of listeners for a single event. They are
	 * sorted by priority.
	 *
	 * If the listener could not be found, returns FALSE, or TRUE if
	 * it was removed.
	 *
	 * @param string $event_name
	 *
	 * @return array
	 */
	public static function listeners($event_name): array
	{
		if (! isset(static::$listeners[$event_name]))
		{
			return [];
		}

		// The list is not sorted
		if (! static::$listeners[$event_name][0])
		{
			// Sort it!
			array_multisort(static::$listeners[$event_name][1], SORT_NUMERIC, static::$listeners[$event_name][2]);

			// Mark it as sorted already!
			static::$listeners[$event_name][0] = true;
		}

		return static::$listeners[$event_name][2];
	}

	//--------------------------------------------------------------------

	/**
	 * Removes a single listener from an event.
	 *
	 * If the listener couldn't be found, returns FALSE, else TRUE if
	 * it was removed.
	 *
	 * @param string   $event_name
	 * @param callable $listener
	 *
	 * @return boolean
	 */
	public static function removeListener($event_name, callable $listener): bool
	{
		if (! isset(static::$listeners[$event_name]))
		{
			return false;
		}

		foreach (static::$listeners[$event_name][2] as $index => $check)
		{
			if ($check === $listener)
			{
				unset(static::$listeners[$event_name][1][$index]);
				unset(static::$listeners[$event_name][2][$index]);

				return true;
			}
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Removes all listeners.
	 *
	 * If the event_name is specified, only listeners for that event will be
	 * removed, otherwise all listeners for all events are removed.
	 *
	 * @param string|null $event_name
	 */
	public static function removeAllListeners($event_name = null)
	{
		if (! is_null($event_name))
		{
			unset(static::$listeners[$event_name]);
		}
		else
		{
			static::$listeners = [];
		}
	}

	//--------------------------------------------------------------------

	/**
	 * Sets the path to the file that routes are read from.
	 *
	 * @param array $files
	 */
	public static function setFiles(array $files)
	{
		static::$files = $files;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the files that were found/loaded during this request.
	 *
	 * @return mixed
	 */
	public function getFiles()
	{
		return static::$files;
	}

	//--------------------------------------------------------------------

	/**
	 * Turns simulation on or off. When on, events will not be triggered,
	 * simply logged. Useful during testing when you don't actually want
	 * the tests to run.
	 *
	 * @param boolean $choice
	 */
	public static function simulate(bool $choice = true)
	{
		static::$simulate = $choice;
	}

	//--------------------------------------------------------------------

	/**
	 * Getter for the performance log records.
	 *
	 * @return array
	 */
	public static function getPerformanceLogs()
	{
		return static::$performanceLog;
	}

	//--------------------------------------------------------------------
}
