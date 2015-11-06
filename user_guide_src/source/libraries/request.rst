=====================
IncomingRequest Class
=====================

The IncomingRequest class provides an object-oriented representation of an HTTP request from a client, like a browser.
It extends from, and has access to all the methods of the :doc:`Request <request>` and :doc:`Message <message>`_
classes, in addition to the methods listed below.

Accessing the Request
=====================

An instance of the request class already populated for you if the current class is a descendant of
``CodeIgniter\Controller`` and can be accessed as a class property::

	class UserController extends CodeIgniter\Controller
	{
		public function index()
		{
			if ($this->request->isAJAX())
			{
				. . .
			}
		}
	}

If you are not within a controller, but still need access to the application's Request object, you can
get a copy of it through the :doc:`Dependency Injection container </concepts/dicontainer>`::

	$request = DI()->single('request');

It's preferable, though, to pass the request in as a dependency if the class is anything other than
the controller, where you can save it as a class property::

	use CodeIgniter\HTTP\RequestInterface;

	class SomeClass
	{
		protected $request;

		public function __construct(RequestInterface $request)
		{
			$this->request = $request;
		}
	}

	$someClass = new SomeClass(DI()->single('request'));


Determining Request Type
========================

A request could be of several types, including an AJAX request or a request from the command line. This can
be checked with the ``isAJAX()`` and ``isCLI()`` methods::

	// Check for AJAX request.
	if ($request->isAJAX())
	{
		. . .
	}

	// Check for CLI Request
	if ($request->isCLI())
	{
		. . .
	}


You can check the HTTP method that this request represents with the ``method()`` method::

	// Returns 'get'
	$method = $request->method();

By default, the method is returned as a lower-case string (i.e. 'get', 'post', etc). You can get an
uppercase version by passing in ``true`` as the only parameter::

	// Returns 'GET'
	$method = $request->method(true);



Retrieving Input
================

You can retrieve input from $_SERVER, $_GET, $_POST, $_ENV, and $_SESSION through the Request object.
The data is not automatically filtered and returns the raw input data as passed in the request. The main
advantages to using these methods instead of accessing them directly ($_POST['something']), is that they
will return null if the item doesn't exist, and you can have the data filtered. This lets you conveniently
use data without having to test whether an item exists first. In other words, normally you might do something
like this::

	$something = isset($_POST['something']) ? $_POST['something'] : NULL;

With CodeIgniter’s built in methods you can simply do this::

	$something = $request->post('something');

The main methods are:

* ``$request->get()``
* ``$request->post()``
* ``$request->server()``
* ``$request->cookie()``

Filtering Input Data
--------------------

To maintain security of your application, you will want to filter all input as you access it. You can
pass a type of filter to use in as the last parameter of any of these methods. The native ``filter_var()`` and
``filter_input`` functions are used for the filtering. Head over to the PHP manual for a list of `valid
filter types <http://php.net/manual/en/filter.filters.php>`_.

Filter a POST variable would look like this::

	$email = $request->post('email', FILTER_SANITIZE_EMAIL);

***************
Class Reference
***************

.. php:class:: CodeIgniter\HTTP\IncomingRequest

	.. php:method:: isCLI()

		:returns: True if the request was initiated from the command line, otherwise false.
		:rtype: bool

	.. php:method:: isAJAX()

		:returns: True if the request is an AJAX request, otherwise false.
		:rtype: bool

	.. php:method:: post([$index = null[, $filter = null])

		:param  string  The name of the variable/key to look for.
		:param  int     The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		The first parameter will contain the name of the POST item you are
			looking for::

			$request->post('some_data');

		The method returns null if the item you are attempting to retrieve
		does not exist.

		The second optional parameter lets you run the data through the PHP's
		filters. Pass in the desired filter type as the second parameter::

			$request->post('some_data', FILTER_SANITIZE_STRING);

		To return an array of all POST items call without any parameters.