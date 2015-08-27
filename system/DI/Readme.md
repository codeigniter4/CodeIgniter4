# Dependency Injection Container

The DI Container acts as a very fast Inversion of Control container, also known as a Dependency Injection container. This allows a single instance of an object to be used in multiple areas of your application.

Like many other dependency injection containers, this class allows you to store both **parameters** and **services**. Parameters are simply variables, though typically a string that can be used during one of the service configurations.

## Service Providers
Service Providers are simply classes that provide some actions that the rest of your application might want to use. These could be simple classes like a Benchmark class, or more complex objects like database query builders, or email services, etc. 

### Basic Configuration
The DI is given it’s initial configuration as an array the first time you call `getInstance()`. This array should contain the basic list of services that it knows how to create, along with an alias for each one. At the beginning, this is simply a list of framework-provided classes that you can use in your application. You can always add your own here as it creates a simple way to swap out the actual classes used throughout your application in one central place.

	$config = [
			'benchmark' => '\CodeIgniter\Benchmark\Benchmark',
			'routes' => '\CodeIgniter\Router\RouteCollection',
			. . .
	];

	$di = \CodeIgniter\DI\DI::getInstance( $config );

The container itself is a singleton. Once the configuration array has been sent to the instance, you never need to pass it again, but they will still be there and accessible.

The services are defined in one of two ways. The first way is to simply provide the name-spaced classname as shown above. The second method allows you to manually define how the instance is generated through an anonymous function.

	$config[‘services’] = [
		'router' => function ($di) {
				return new \CodeIgniter\Router\Router( $di->routes );
		}
	];

Both of these styles can be combined in the services config array.

### Getting A New Class Instance
You can retrieve a new instance of a class at any time by calling `make()`. The only parameter is the alias of the class you want to retrieve. To get a new benchmark class instance, you would do something like: 

	$di = \CodeIgniter\DI\DI::getInstance();
	$bm = $di->make('benchmark');

Assuming that the benchmark class does not have any parameters that need to be passed into the constructor, then this is the same as: 

	$bm = new \CodeIgniter\Benchmark\Benchmark();

The only advantage here is that you can use the DI object anywhere in your code and, if you want to use a different benchmarking class, then you only need to change the name in your config array. 

### Auto Constructor Injection
If, however, your class needs one or more objects passed in the constructor, then the DI class will attempt to inject new instances of those classes into the constructor for you. These objects will first try to match an instance of a class registered to the container. If that class cannot be found in the services list, then it will try to autoload the class for you and pass in a new instance. If that is not possible, then it will throw a new `InvalidArgumentException`.

There is one bit of convention we have to adhere to, though, in order to make the mapping of services work correctly. The parameter name MUST match the alias of any mapped services in order to work correctly. An example will make this clear. 

Looking at our list of services above, we decide we need a new Router. This class requires an instance of the RouteCollection class in it’s constructor. So we must name the parameter `routes` to match the alias in our services config file. Since the RouteCollection class has an interface available you should type hint to that to ensure you get a class you can use.

Altogether it would look something like: 

	class RouteCollection implements RouteCollectionInterface { } 
	
	class Router {
		protected $collection;
	
		// The parameter name must match the alias name. 
		public function __construct(RouteCollectionInterface $routes)
		{ 
			 $this->collection = $routes;
		}
	}
	 
	$router = $di->make('router');

This only applies if the class you are requesting an instance of is just the class name. If the configuration value is a closure, then it will NOT inject your dependencies for you. 

### Getting A Singleton Instance
If you want to ensure that you always get back the same instance of a class you have two ways that you can do this. 

The first is by using the `single()` method. This method is identical to the `make()` method except that it will cache the class if it has to create it, or return an existing instance if it already has been instantiated. Note that all classes required by the constructor will also be singleton instances, if possible. 

	$bm = $di->single('benchmark');

Alternatively, you can access the class as a parameter of the DI object. This will simply call the `single()` method to grab the object.

	$bm = $di->benchmark;

### Registering New Class/Alias Pairs
During run time, you can add alias and class pairs to the DI object in addition to those already stored in the config array. This is done with the `register()` method. The first parameter is the alias to use. The second parameter is the fully name-spaced class name or an anonymous function that will return an instance of the class.

	$di->register('example', '\App\Libraries\Example');
	$di->register('example', function ($di) {
		 return new \App\Libraries\Example();
	});

### Unregistering Classes
During runtime, you can remove any registered aliases with the `unregister()` method. The first parameter is the alias to remove. The second parameter is a boolean representing whether any existing instances of this class cached in the DI object should also be deleted.

	// Will not delete existing instance
	$di->unregister('example');
	 
	// Will delete existing instance
	$di->unregister('example', true);

### Registering Existing Instances
If you already have an instance of a class that you would like to register with the container, and have it be used with the `single()` methods, then you can use the `saveInstance()` method to save that instance to the class. The first parameter is the alias to use. The second parameter is the instance to save. This will overwrite anything already at that instance. 

	$class = new MockClass();
	$di->saveInstance('example', $class);

## Parameters
Parameters can be any variable, from a string, to a number, or even an array or object. They are typically used to store values that you need to use within your service configuration array, described above. 

### Defining Parameters
Parameters are stored by assigning them to the DI object itself. However, they cannot share the same name as an existing service.

	$di->session_storage_class = 'SessionStorage';
	
They can then be retrieved as an value of the container. 

	echo $di->session_storage_class;


