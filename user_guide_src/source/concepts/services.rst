########
Services
########

.. contents::
    :local:
    :depth: 2

Introduction
============

All of the classes within CodeIgniter are provided as "services". This simply means that, instead
of hard-coding a class name to load, the classes to call are defined within a very simple
configuration file. This file acts as a type of factory to create new instances of the required class.

A quick example will probably make things clearer, so imagine that you need to pull in an instance
of the Timer class. The simplest method would simply be to create a new instance of that class::

	$timer = new \CodeIgniter\Debug\Timer();

And this works great. Until you decide that you want to use a different timer class in its place.
Maybe this one has some advanced reporting the default timer does not provide. In order to do this,
you now have to locate all of the locations in your application that you have used the timer class.
Since you might have left them in place to keep a performance log of your application constantly
running, this might be a time-consuming and error-prone way to handle this. That's where services
come in handy.

Instead of creating the instance ourself, we let a central class create an instance of the
class for us. This class is kept very simple. It only contains a method for each class that we want
to use as a service. The method typically returns a shared instance of that class, passing any dependencies
it might have into it. Then, we would replace our timer creation code with code that calls this new class::

	$timer = \Config\Services::timer();

When you need to change the implementation used, you can modify the services configuration file, and
the change happens automatically throughout your application without you having to do anything. Now
you just need to take advantage of any new functionality and you're good to go. Very simple and
error-resistant.

.. note:: It is recommended to only create services within controllers. Other files, like models and libraries should have the dependencies either passed into the constructor or through a setter method.


Convenience Functions
---------------------

Two functions have been provided for getting a service. These functions are always available.

The first is ``service()`` which returns a new instance of the requested service. The only
required parameter is the service name. This is the same as the method name within the Services
file always returns a SHARED instance of the class, so calling the function multiple times should
always return the same instance::

	$logger = service('logger');

If the creation method requires additional parameters, they can be passed after the service name::

	$renderer = service('renderer', APPPATH.'views/');

The second function, ``single_service()`` works just like ``service()`` but returns a new instance of
the class::

	$logger = single_service('logger');

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

Finally, modify **/app/Config/Services.php** to create a new instance of ``MyRouter``
instead of ``CodeIgniter\Router\RouterCollection``::

	public static function routes()
	{
		return new \App\Router\MyRouter();
	}

Allowing Parameters
-------------------

In some instances, you will want the option to pass a setting to the class during instantiation.
Since the services file is a very simple class, it is easy to make this work.

A good example is the ``renderer`` service. By default, we want this class to be able
to find the views at ``APPPATH.views/``. We want the developer to have the option of
changing that path, though, if their needs require it. So the class accepts the ``$viewPath``
as a constructor parameter. The service method looks like this::

	public static function renderer($viewPath=APPPATH.'views/')
	{
		return new \CodeIgniter\View\View($viewPath);
	}

This sets the default path in the constructor method, but allows for easily changing
the path it uses::

	$renderer = \Config\Services::renderer('/shared/views');

Shared Classes
-----------------

There are occasions where you need to require that only a single instance of a service
is created. This is easily handled with the ``getSharedInstance()`` method that is called from within the
factory method. This handles checking if an instance has been created and saved
within the class, and, if not, creates a new one. All of the factory methods provide a
``$getShared = true`` value as the last parameter. You should stick to the method also::

    class Services
    {
        public static function routes($getShared = false)
        {
            if (! $getShared)
            {
                return new \CodeIgniter\Router\RouteCollection();
            }

            return static::getSharedInstance('routes');
        }
    }

Service Discovery
-----------------

CodeIgniter can automatically discover any Config\\Services.php files you may have created within any defined namespaces.
This allows simple use of any module Services files. In order for custom Services files to be discovered, they must
meet these requirements:

- Its namespace must be defined in ``Config\Autoload.php``
- Inside the namespace, the file must be found at ``Config\Services.php``
- It must extend ``CodeIgniter\Config\BaseService``

A small example should clarify this.

Imagine that you've created a new directory, ``Blog`` in your root directory. This will hold a **blog module** with controllers,
models, etc, and you'd like to make some of the classes available as a service. The first step is to create a new file:
``Blog\Config\Services.php``. The skeleton of the file should be::

    <?php namespace Blog\Config;

    use CodeIgniter\Config\BaseService;

    class Services extends BaseService
    {
        public static function postManager()
        {
            ...
        }
    }

Now you can use this file as described above. When you want to grab the posts service from any controller, you
would simply use the framework's ``Config\Services`` class to grab your service::

    $postManager = Config\Services::postManager();

.. note:: If multiple Services files have the same method name, the first one found will be the instance returned.
