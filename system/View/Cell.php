<?php namespace CodeIgniter\View;

use CodeIgniter\Cache\CacheInterface;

/**
 * Class Cell
 *
 * A simple class that can call any other class that can be loaded,
 * and echo out it's result. Intended for displaying small blocks of
 * content within views that can be managed by other libraries and
 * not require they are loaded within controller.
 *
 * Used with the helper function, it's use will look like:
 *
 * 		viewCell('\Some\Class::method', 'limit=5 sort=asc', 60, 'cache-name');
 *
 * Parameters are matched up with the callback method's arguments of the same name:
 *
 * 		class Class {
 *			function method($limit, $sort)
 * 		}
 *
 * Alternatively, the params will be passed into the callback method as a simple array
 * if matching params are not found.
 *
 * 		class Class {
 *			function method(array $params=null)
 * 		}
 *
 * @package CodeIgniter\View
 */
class Cell
{
	/**
	 * Instance of the current Cache Instance
	 *
	 * @var CacheInterface
	 */
	protected $cache;

	//--------------------------------------------------------------------

	public function __construct(CacheInterface $cache)
	{
		$this->cache = $cache;
	}

	//--------------------------------------------------------------------

	/**
	 * @param string      $library
	 * @param null        $params
	 * @param int         $ttl
	 * @param string|null $cacheName
	 *
	 * @return string
	 */
	public function render(string $library, $params = null, int $ttl = 0, string $cacheName = null): string
	{
		list($class, $method) = $this->determineClass($library);

		// Is it cached?
		$cacheName = ! empty($cacheName)
			? $cacheName
			: $class.$method.md5(serialize($params));

		if (! empty($this->cache) && $output = $this->cache->get($cacheName))
		{
			return $output;
		}

		// Not cached - so grab it...
		$instance = new $class();

		if (! method_exists($instance, $method))
		{
			throw new \InvalidArgumentException("{$class}::{$method} is not a valid method.");
		}

		// Try to match up the parameter list we were provided
		// with the parameter name in the callback method.
		$paramArray = $this->prepareParams($params);
		$refMethod  = new \ReflectionMethod($instance, $method);
		$paramCount = $refMethod->getNumberOfParameters();
		$refParams  = $refMethod->getParameters();

		if ($paramCount === 0)
		{
			if ($paramArray !== null)
			{
				throw new \InvalidArgumentException("{$class}::{$method} has no params.");
			}

			$output = $instance->{$method}();
		}
		elseif (
			($paramCount === 1)
			&& (
				(! array_key_exists($refParams[0]->name, $paramArray))
				|| (
					array_key_exists($refParams[0]->name, $paramArray)
					&& count($paramArray) !== 1
				)
			)
		)
		{
			$output = $instance->{$method}($paramArray);
		}
		else
		{
			$fireArgs = [];
			$method_params = [];

			foreach($refParams as $arg)
			{
				$method_params[$arg->name] = true;
				if (array_key_exists($arg->name, $paramArray))
				{
					$fireArgs[$arg->name] = $paramArray[$arg->name];
				}
			}

			foreach ($paramArray as $key => $val)
			{
				if (! isset($method_params[$key]))
				{
					throw new \InvalidArgumentException("{$key} is not a valid param name.");
				}
			}

			$output = call_user_func_array([$instance, $method], $fireArgs);
		}
		// Can we cache it?
		if (! empty($this->cache) && $ttl !== 0)
		{
			$this->cache->set($cacheName, $output, $ttl);
		}
		return $output;
	}

	//--------------------------------------------------------------------

	/**
	 * Parses the params attribute. If an array, returns untouched.
	 * If a string, it should be in the format "key1=value key2=value".
	 * It will be split and returned as an array.
	 *
	 * @param $params
	 *
	 * @return array|null
	 */
	public function prepareParams($params)
	{
		if (empty($params) || (! is_string($params) && ! is_array($params)))
		{
			return;
		}

		if (is_string($params))
		{
			$new_params = [];
			$separator  = ' ';

			if (strpos($params, ',') !== false)
			{
				$separator = ',';
			}

			$params = explode($separator, $params);
			unset($separator);

			foreach ($params as $p)
			{
				list($key, $val) = explode('=', $p);
				$new_params[trim($key)] = trim($val, ', ');
			}

			$params = $new_params;

			unset($new_params);
		}

		if (is_array($params) && ! count($params))
		{
			return;
		}

		return $params;
	}

	//--------------------------------------------------------------------

	/**
	 * Given the library string, attempts to determine the class and method
	 * to call.
	 *
	 * @param string $library
	 *
	 * @return array
	 */
	protected function determineClass(string $library)
	{
		// We don't want to actually call static methods
		// by default, so convert any double colons.
		$library = str_replace('::', ':', $library);

		list($class, $method) = explode(':', $library);

		if (empty($class))
		{
			throw new \InvalidArgumentException('No view cell class provided.');
		}

		if (! class_exists($class, true))
		{
			throw new \InvalidArgumentException('Unable to locate view cell class: '.$class.'.');
		}

		if (empty($method))
		{
			$method = 'index';
		}

		return [$class, $method];
	}

	//--------------------------------------------------------------------
}
