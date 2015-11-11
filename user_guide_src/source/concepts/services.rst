########
Services
########

Introduction
============

All of the classes within CodeIgniter are provided as "services". This simply means that, instead
of hard-coding a class name to load, the classes to call are defined within a very simple
configuration file. This file acts as a form of factory to create new instances of the required class.

A quick example will probably make things clearer, so imagine that you need to pull in an instance
of the Bechmark/Timer class. In the old days, you would simply create a new instance of that class::

	$timer = new CodeIgniter\Debug\Timer();

And this works great. Until you decide that you want to user a different timer class in its place.
Maybe this one has some advanced reporting the default timer does not provide. In order to do this,
you now have to locate all of the locations in your application that you have used the timer class.
Since you might have left them in place to keep a performance log of your application constantly
running, this might be a time-consuming and error-prone way to handle this. That's where services
come in handy.

Instead of creating the instance ourself, we let another, central class create an instance of the
class for us. This class is kept very simple. It only contains a method for each class that we want
to use as a service. The method typically returns a new instance of that class, passing any dependencies
it might have into it. Then, we would replace our timer creation code with code that calls this new class::

	$timer = App\Config\Services::timer();

When you need to change the implementation used, you can modify the services configuration file, and
the change happens automatically throughout your application without you having to do anything. Now
 you just need to take advantage of any new functionality and you're good to go. Very simple and
 error-resistant.

Defining Services
=================

To make services work well, you have to be able to rely on each class having a constant API, or
`interface <http://php.net/manual/en/language.oop5.interfaces.php>`_, to use. Almost all of
CodeIgniter's classes provide an interface that they adhere to. When you want to extend or replace
core classes, you only need to ensure you meet the requirements of the interface and you know that
the classes are compatible.

For example, the ``RouterCollection`` class implements the ``RouterCollectionInterface``. When you
want to create a replacement that provides a different way to create routes, you just need to
create a new class that implements the ``RouterCollectionInterface``::

	class MyRouter implements \CodeIgniter\Router\RouteCollectionInterface
	{
	    // Implement required methods here.
	}

Finally, modify ``/application/config/services.php`` to create a new instance of ``MyRouter``
instead of ``CodeIgniter\\Router\\RouterCollection``::

	public static function routes()
	{
	    return new \App\Router\MyRouter();
	}

	//--------------------------------------------------------------------


Allowing Parameters
-------------------

In some instances, you will want the option to pass a setting to the class during instantiation.
Since the services file is a very simple class, you can very simply make this work.

A good example is the ``renderer`` service. By default, we want this class to be able
to find the views at ``APPPATH.views/``. We want the developer to have the option of
changing that path, though, if their needs require it. So the class accepts the ``$viewPath``
as a constructor parameter. The service method looks like this::

	public static function renderer($viewPath=APPPATH.'views/')
	{
	    return new \CodeIgniter\View\View($viewPath);
	}

This sets the default path in the method constructor, but allows for easily changing
the path it uses::

	$renderer = \App\Config\Services::renderer('/shared/views');

Singleton Classes
-----------------

There are occasions where you need to require that only a single instance of a service
is created. While this is typically discouraged, there are cases this is valid need,
and simply handled with a static class property. The Logger is a perfect example of this::

	class Services
	{
		static protected $logger;

		public static function logger()
		{
			// We only ever want a single instance of the logger.
			if (empty(static::$logger))
			{
				static::$logger = new \PSR\Log\Logger(new \App\Config\LoggerConfig());
			}

		    return static::$logger;
		}
	}

First, a new static class property is created to store an instance of the Logger class.
Inside the ``logger()`` method, it checks to see if a class has already been created.
If not, we'll create a new instance and store it with the class. Then it returns the
instance. With this setup, only a single instance of the Logger class will ever be created.

