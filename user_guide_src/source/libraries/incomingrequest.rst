=====================
IncomingRequest Class
=====================

The IncomingRequest class provides an object-oriented representation of an HTTP request from a client, like a browser.
It extends from, and has access to all the methods of the :doc:`Request </libraries/request>` and :doc:`Message </libraries/message>`
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
pass the type of filter to use in as the last parameter of any of these methods. The native ``filter_var()`` and
``filter_input`` functions are used for the filtering. Head over to the PHP manual for a list of `valid
filter types <http://php.net/manual/en/filter.filters.php>`_.

Filter a POST variable would look like this::

	$email = $request->post('email', FILTER_SANITIZE_EMAIL);

***************
Class Reference
***************

.. note:: In addition to the methods listed here, this class inherits the methods from the
	:doc:`Request Class </libraries/request>` and the :doc:`Message Class </libraries/message>`.

The methods provided by the parent classes that are available are:

* :meth:`CodeIgniter\\HTTP\\Request::ipAddress`
* :meth:`CodeIgniter\\HTTP\\Request::validIP`
* :meth:`CodeIgniter\\HTTP\\Request::method`
* :meth:`CodeIgniter\\HTTP\\Request::server`
* :meth:`CodeIgniter\\HTTP\\Request::server`
* :meth:`CodeIgniter\\HTTP\\Message::body`
* :meth:`CodeIgniter\\HTTP\\Message::setBody`
* :meth:`CodeIgniter\\HTTP\\Message::populateHeaders`
* :meth:`CodeIgniter\\HTTP\\Message::headers`
* :meth:`CodeIgniter\\HTTP\\Message::header`
* :meth:`CodeIgniter\\HTTP\\Message::headerLine`
* :meth:`CodeIgniter\\HTTP\\Message::setHeader`
* :meth:`CodeIgniter\\HTTP\\Message::removeHeader`
* :meth:`CodeIgniter\\HTTP\\Message::appendHeader`
* :meth:`CodeIgniter\\HTTP\\Message::protocolVersion`
* :meth:`CodeIgniter\\HTTP\\Message::setProtocolVersion`
* :meth:`CodeIgniter\\HTTP\\Message::negotiateMedia`
* :meth:`CodeIgniter\\HTTP\\Message::negotiateCharset`
* :meth:`CodeIgniter\\HTTP\\Message::negotiateEncoding`
* :meth:`CodeIgniter\\HTTP\\Message::negotiateLanguage`
* :meth:`CodeIgniter\\HTTP\\Message::negotiateLanguage`

.. php:class:: CodeIgniter\\HTTP\\IncomingRequest

	.. php:method:: isCLI()

		:returns: True if the request was initiated from the command line, otherwise false.
		:rtype: bool

	.. php:method:: isAJAX()

		:returns: True if the request is an AJAX request, otherwise false.
		:rtype: bool

	.. php:method:: isSecure()

		:returns: True if the request is an HTTPS request, otherwise false.
		:rtype: bool

	.. php:method:: post([$index = null[, $filter = null])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		The first parameter will contain the name of the POST item you are looking for::

			$request->post('some_data');

		The method returns null if the item you are attempting to retrieve
		does not exist.

		The second optional parameter lets you run the data through the PHP's
		filters. Pass in the desired filter type as the second parameter::

			$request->post('some_data', FILTER_SANITIZE_STRING);

		To return an array of all POST items call without any parameters.

		To return all POST items and pass them through the filter, set the
		first parameter to null while setting the second parameter to the filter
		you want to use.::

			$request->post(null, FILTER_SANITIZE_STRING); // returns all POST items with string sanitation

		To return an array of multiple  POST parameters, pass all the required keys as an array.::

			$request->post(['field1', 'field2']);

		Same rule applied here, to retrieve the parameters with filtering, set the second parameter to
		the filter type to apply.::

			$request->post(['field1', 'field2'], FILTER_SANITIZE_STRING);

	.. php:method:: get([$index = null[, $filter = null]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		This method is identical to ``post()``, only it fetches GET data.

	.. php:method:: postGet([$index = null[, $filter = null]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		This method works pretty much the same way as ``post()`` and ``get()``, only combined.
		It will search through both POST and GET streams for data, looking first in POST, and
		then in GET::

			$request->postGet('field1');

	.. php:method:: getPost([$index = null[, $filter = null]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		This method works pretty much the same way as ``post()`` and ``get()``, only combined.
		It will search through both POST and GET streams for data, looking first in GET, and
		then in POST::

			$request->getPost('field1');

	.. php:method:: cookie([$index = null[, $filter = NULL]])

		:param	mixed	$index: COOKIE name
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:	$_COOKIE if no parameters supplied, otherwise the COOKIE value if found or null if not
		:rtype:	mixed

		This method is identical to ``post()`` and ``get()``, only it fetches cookie data::

			$request->cookie('some_cookie');
			$request->cookie('some_cookie, FILTER_SANITIZE_STRING); // with filter

		To return an array of multiple cookie values, pass all the required keys as an array.::

			$request->cookie(array('some_cookie', 'some_cookie2'));

		.. note:: Unlike the :doc:`Cookie Helper <../helpers/cookie_helper>`
			function :php:func:`get_cookie()`, this method does NOT prepend
			your configured ``$config['cookie_prefix']`` value.

	.. php:method:: server($index[, $filter = null])

		:param	mixed	$index: Value name
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:	$_SERVER item value if found, NULL if not
		:rtype:	mixed

		This method is identical to the ``post()``, ``get()`` and ``cookie()``
		methods, only it fetches server data (``$_SERVER``)::

			$request->server('some_data');

		To return an array of multiple ``$_SERVER`` values, pass all the required keys
		as an array.
		::

			$request->server(['SERVER_PROTOCOL', 'REQUEST_URI']);

	.. php:method:: userAgent([$filter = null])

		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns:  The User Agent string, as found in the SERVER data, or null if not found.
		:rtype: mixed

		This method returns the User Agent string from the SERVER data.::

			$request->userAgent();

	.. php:method:: setCookie($name = ''[, $value = ''[, $expire = ''[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = FALSE[, $httponly = FALSE]]]]]]])

		:param	mixed	$name: Cookie name or an array of parameters
		:param	string	$value: Cookie value
		:param	int	$expire: Cookie expiration time in seconds
		:param	string	$domain: Cookie domain
		:param	string	$path: Cookie path
		:param	string	$prefix: Cookie name prefix
		:param	bool	$secure: Whether to only transfer the cookie through HTTPS
		:param	bool	$httponly: Whether to only make the cookie accessible for HTTP requests (no JavaScript)
		:rtype:	void


		Sets a cookie containing the values you specify. There are two ways to
		pass information to this method so that a cookie can be set: Array
		Method, and Discrete Parameters:

		**Array Method**

		Using this method, an associative array is passed to the first
		parameter::

			$cookie = array(
				'name'   => 'The Cookie Name',
				'value'  => 'The Value',
				'expire' => '86500',
				'domain' => '.some-domain.com',
				'path'   => '/',
				'prefix' => 'myprefix_',
				'secure' => TRUE
			);

			$request->set_cookie($cookie);

		**Notes**

		Only the name and value are required. To delete a cookie set it with the
		expiration blank.

		The expiration is set in **seconds**, which will be added to the current
		time. Do not include the time, but rather only the number of seconds
		from *now* that you wish the cookie to be valid. If the expiration is
		set to zero the cookie will only last as long as the browser is open.

		For site-wide cookies regardless of how your site is requested, add your
		URL to the **domain** starting with a period, like this:
		.your-domain.com

		The path is usually not needed since the method sets a root path.

		The prefix is only needed if you need to avoid name collisions with
		other identically named cookies for your server.

		The secure boolean is only needed if you want to make it a secure cookie
		by setting it to TRUE.

		**Discrete Parameters**

		If you prefer, you can set the cookie by passing data using individual
		parameters::

			$request->set_cookie($name, $value, $expire, $domain, $path, $prefix, $secure);

