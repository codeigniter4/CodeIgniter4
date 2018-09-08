<?php namespace Config;

// Cannot extend BaseConfig or looping resources occurs.
class Modules
{
	/*
	|--------------------------------------------------------------------------
	| Auto-discover Rules
	|--------------------------------------------------------------------------
	|
	| Lists the aliases of all discovery classes that will be active
	| and used during the current application request. If it is not
	| listed here, only the base application elements will be used.
	*/
	public $enabled = [
		'events',
		'registrars',
		'routes',
		'services',
		'views'
	];

	/*
	|--------------------------------------------------------------------------
	| Cache Results?
	|--------------------------------------------------------------------------
	|
	| If true, the results of all discoveries will be cached and will be
	| not be searched for until the cache is cleared, increasing performance
	| at the cost of any additional discovery.
	|
	| This is a good setting to use in production where changes are infrequent.
	*/
	public $cache = false;

	/**
	 * Should the application auto-discover the requested resources.
	 *
	 * Valid values are:
	 *  - events
	 *  - registrars
	 *  - routes
	 *  - services
	 *  - views
	 *
	 * @param string $alias
	 *
	 * @return bool
	 */
	public function shouldDiscover(string $alias)
	{
		$alias = strtolower($alias);

		return in_array($alias, $this->enabled);
	}
}
