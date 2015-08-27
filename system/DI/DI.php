<?php namespace CodeIgniter\DI;

/**
 * Class DI
 *
 * This class acts as both a singleton and a registry for the
 * "services" available.
 *
 * Services are specified through the
 * application/config/services.php config file. Any service
 * listed there can be accessed by its alias through $CI->{service alias}
 *
 * Example:
 *      A service with alias 'logger' could be access like:
 *
 *      $di = CodeIgniter\DI\DI::getInstance();
 *      $di->logger->log();
 *
 * You can bulk register a number of services when you get the instance
 * for the first time, by passing an array of alias/class names (or closures)
 * in the getInstance() method.
 *
 * Example:
 *      $config = [
 *          'routes' => '\CodeIgniter\Router\RouteCollection',
 *          'router' => function ($di) {
 *              return new \CodeIgniter\Router\Router( $di->routes );
 *          }
 *      ];
 *
 *      $container = \CodeIgniter\DI\DI::getInstance( $config );
 *
 * New services can always be added later with the register(), save_instance(),
 * and __get() methods.
 *
 * @author Lonnie Ezell (lonnie@newmythmedia.com)
 */
class DI
{

	/**
	 * Stores the map of service provider
	 * name and class names.
	 *
	 * @var
	 */
	protected $services = [];

	/**
	 * Holds all instantiated singleton
	 * objects.
	 *
	 * @var array
	 */
	protected $instances = [];

	/**
	 * Holds all of our simple parameters.
	 *
	 * @var array
	 */
	protected $parameters = [];

	/**
	 * An instance of this class for static usage.
	 *
	 * @var
	 */
	protected static $instance;

	//--------------------------------------------------------------------

	/**
	 * The constructor is kept private to ensure that
	 * this class can only be used as a singleton DI container.
	 */
	private function __construct(array $config = [])
	{
		$this->services = $config;
	}

	//--------------------------------------------------------------------

	/**
	 * Returns the singleton instance of this class.
	 *
	 * Provides a simple way to access the class elsewhere in the
	 * application. Care should be used, though, as this could simply
	 * defer the dependency onto this container and still keep your
	 * class dependant on this DI class.
	 *
	 * A preferred way would be to request the DI class in your
	 * classes constructor.
	 *
	 * Example:
	 *      public function __construct(DI $di)
	 *      {
	 *          $this->di = $di;
	 *      }
	 */
	public static function getInstance(array $config = [])
	{
		if (empty(static::$instance))
		{
			static::$instance = new DI($config);
		}

		return static::$instance;
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Service Providers
	//--------------------------------------------------------------------

	/**
	 * Registers a specific class name with the corresponding alias
	 * for the dependency inversion.
	 *
	 * @param $name
	 * @param $class
	 *
	 * @return $this
	 */
	public function register($alias, $class)
	{
		$alias = strtolower($alias);

		if (array_key_exists($alias, $this->instances))
		{
			throw new \RuntimeException('You cannot register a provider when an instance of that class has already been created.');
		}

		$this->services[$alias] = $class;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Unregisters a single service provider. If an instance of that
	 * provider has already been created, it will be destroyed.
	 *
	 * @param $alias
	 *
	 * @return $this
	 */
	public function unregister($alias, $remove_instances = false)
	{
		$alias = strtolower($alias);

		if ($remove_instances && array_key_exists($alias, $this->instances))
		{
			$this->instances[$alias] = null;
			unset($this->instances[$alias]);
		}

		unset($this->services[$alias]);

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Registers an instantiated class as a Service Provider.
	 *
	 * todo Register savedInstance class with the services array.
	 *
	 * @param $alias
	 * @param $class
	 *
	 * @return $this
	 */
	public function saveInstance($alias, &$class)
	{
		$alias = strtolower($alias);

		if (array_key_exists($alias, $this->instances))
		{
			unset($this->instances[$alias]);
		}

		$this->instances[$alias] = $class;

		return $this;
	}

	//--------------------------------------------------------------------

	/**
	 * Creates a new instance of the service provider with $alias, and
	 * return it. Does not check for an existing instance but always
	 * returns a new one.
	 *
	 * @param      $alias
	 * @param bool $use_singletons
	 *
	 * @return null
	 */
	public function make($alias, $use_singletons = false)
	{
		$alias = strtolower($alias);

		if ( ! array_key_exists($alias, $this->services))
		{
			throw new \InvalidArgumentException('No Service is registered for alias: '.$alias);
		}

		// The provider could be either a string (namespaced class)
		// or a Closure that returns an instance of the desired class.
		$service = $this->services[$alias];

		if (is_string($service))
		{
			if ( ! class_exists($service, true))
			{
				throw new \RuntimeException('Unable to locate the Service Provider: '.$this->services[$alias]);
			}

			return $this->inject($service, $use_singletons);
		}

		else if (is_callable($service))
		{
			return $service($this);
		}

		return null;
	}

	//--------------------------------------------------------------------

	/**
	 * Allows you to create a new instance of an object as a singleton,
	 * passing in arguments.
	 *
	 * @param $alias
	 *
	 * @return mixed
	 */
	public function single($alias)
	{
		$alias = strtolower($alias);

		// Return the existing object if it exists.
		if ( ! empty($this->instances[$alias]) && is_object($this->instances[$alias]))
		{
			return $this->instances[$alias];
		}

		// Die if we don't know what class to use.
		if (empty($this->services[$alias]))
		{
			throw new \InvalidArgumentException('Unable to find class with alias: '.$alias);
		}

		$instance = $this->make($alias, true);

		$this->instances[$alias] =& $instance;

		return $this->instances[$alias];
	}

	//--------------------------------------------------------------------

	//--------------------------------------------------------------------
	// Magic
	//--------------------------------------------------------------------

	/**
	 * Attempts to locate a service provider that exists with an alias
	 * matching $name and return it.
	 *
	 * If a parameter exists by that name, it will be returned as the value.
	 *
	 * Example:
	 *      $di->load    will search for the provider with the alias 'load'
	 *                   and create an instance, if it doesn't exist
	 *
	 *      $di->
	 *
	 * @param $alias
	 *
	 * @return null
	 */
	public function __get($alias)
	{
		// If a parameter exists by this name,
		// we'll return it.
		if (array_key_exists($alias, $this->parameters))
		{
			return $this->parameters[$alias];
		}

		// Otherwise, return a singleton of the class.
		// An exception will be thrown if the class cannot be found.
		return $this->single($alias);
	}

	//--------------------------------------------------------------------

	/**
	 * Allows you to store parameters inside the container
	 * that can be used later. They cannot share the same name
	 * as any of the aliases for the services, since that would
	 * lead to major problems and debugging issues.
	 *
	 * Example:
	 *      $di->session_id = 'MySessionID';
	 *      echo $di->session_id;
	 *
	 * @param $key
	 * @param $value
	 */
	public function __set($key, $value)
	{
		if (array_key_exists($key, $this->services))
		{
			throw new \InvalidArgumentException('You cannot store a parameter with the same name as a registered service.');
		}

		$this->parameters[$key] = $value;
	}

	//--------------------------------------------------------------------

	/**
	 * Provides a chance for callable parameters to be ran.
	 *
	 * Example:
	 *      $di->say = function ($verb) { return $verb; }
	 *      echo $di->say();
	 *
	 * @param $name
	 * @param $arguments
	 *
	 * @return bool|mixed
	 */
	public function __call($name, $arguments)
	{
		if (array_key_exists($name, $this->parameters) &&
		    is_callable($this->parameters[$name])
		)
		{
			return call_user_func_array($this->parameters[$name], $arguments);
		}

		return false;
	}

	//--------------------------------------------------------------------

	/**
	 * Determines the classes needed, creates instances (or uses existing
	 * instances, if exists) to pass in constructor and returns
	 * a new instance of the desired service.
	 *
	 * @param $service
	 *
	 * @return null|object
	 */
	protected function inject($service, $single = false)
	{
		$mirror      = new \ReflectionClass($service);
		$constructor = $mirror->getConstructor();

		$params = null;

		if (empty($constructor))
		{
			return new $service();
		}

		$params = $this->getParams($constructor, $single);

		// No params means we simply create a new
		// instance of the class and return it...
		if (is_null($params))
		{
			return new $service();
		}

		// Still here - then return an instance
		// with those params as arguments
		return $mirror->newInstanceArgs($params);
	}

	//--------------------------------------------------------------------

	/**
	 * Given a reflection method, will get or create an array of objects
	 * ready to be inserted into the class' constructor.
	 *
	 * If $single is true, will return a singleton version of dependencies
	 * else will create a new class.
	 *
	 * @param \ReflectionMethod $mirror
	 * @param bool              $single
	 *
	 * @return array
	 */
	protected function getParams(\ReflectionMethod $mirror, $single = false)
	{
		$params = [];

		foreach ($mirror->getParameters() as $param)
		{
			$alias = strtolower($param->name);

			// Is this a mapped alias?
			if ( ! empty($this->services[$alias]))
			{
				$params[] = $single ? $this->single($alias) : $this->make($alias);
				continue;
			}

			// Is this a normal class we can give them?
			$class = $param->getClass()->name;

			if (class_exists($class))
			{
				$params[] = new $class();
			}

			$params[] = null;
		}

		return $params;
	}

	//--------------------------------------------------------------------

	protected function reset()
	{
		$this->parameters = [];
		$this->instances  = [];
		$this->services   = [];

		static::$instance = null;
		unset(static::$instance);
	}

	//--------------------------------------------------------------------

}
