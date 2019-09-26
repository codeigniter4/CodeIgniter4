IncomingRequest Class
*********************

The IncomingRequest class provides an object-oriented representation of an HTTP request from a client, like a browser.
It extends from, and has access to all the methods of the :doc:`Request </incoming/request>` and :doc:`Message </incoming/message>`
classes, in addition to the methods listed below.

.. contents::
    :local:
    :depth: 2

Accessing the Request
----------------------------------------------------------------------------

An instance of the request class already populated for you if the current class is a descendant of
``CodeIgniter\Controller`` and can be accessed as a class property::

        <?php namespace App\Controllers;

        user CodeIgniter\Controller;

	class UserController extends Controller
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
get a copy of it through the :doc:`Services class </concepts/services>`::

	$request = \Config\Services::request();

It's preferable, though, to pass the request in as a dependency if the class is anything other than
the controller, where you can save it as a class property::

	<?php 
        use CodeIgniter\HTTP\RequestInterface;

	class SomeClass
	{
		protected $request;

		public function __construct(RequestInterface $request)
		{
			$this->request = $request;
		}
	}

	$someClass = new SomeClass(\Config\Services::request());

Determining Request Type
----------------------------------------------------------------------------

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

	// Returns 'post'
	$method = $request->getMethod();

By default, the method is returned as a lower-case string (i.e. 'get', 'post', etc). You can get an
uppercase version by passing in ``true`` as the only parameter::

	// Returns 'GET'
	$method = $request->getMethod(true);

You can also check if the request was made through and HTTPS connection with the ``isSecure()`` method::

	if (! $request->isSecure())
	{
		force_https();
	}

Retrieving Input
----------------------------------------------------------------------------

You can retrieve input from $_SERVER, $_GET, $_POST, $_ENV, and $_SESSION through the Request object.
The data is not automatically filtered and returns the raw input data as passed in the request. The main
advantages to using these methods instead of accessing them directly ($_POST['something']), is that they
will return null if the item doesn't exist, and you can have the data filtered. This lets you conveniently
use data without having to test whether an item exists first. In other words, normally you might do something
like this::

	$something = isset($_POST['foo']) ? $_POST['foo'] : NULL;

With CodeIgniter’s built in methods you can simply do this::

	$something = $request->getVar('foo');

The ``getVar()`` method will pull from $_REQUEST, so will return any data from $_GET, $POST, or $_COOKIE. While this
is convenient, you will often need to use a more specific method, like:

* ``$request->getGet()``
* ``$request->getPost()``
* ``$request->getServer()``
* ``$request->getCookie()``

In addition, there are a few utility methods for retrieving information from either $_GET or $_POST, while
maintaining the ability to control the order you look for it:

* ``$request->getPostGet()`` - checks $_POST first, then $_GET
* ``$request->getGetPost()`` - checks $_GET first, then $_POST

**Getting JSON data**

You can grab the contents of php://input as a JSON stream with ``getJSON()``.

.. note::  This has no way of checking if the incoming data is valid JSON or not, you should only use this
    method if you know that you're expecting JSON.

::

	$json = $request->getJSON();

By default, this will return any objects in the JSON data as objects. If you want that converted to associative
arrays, pass in ``true`` as the first parameter.

The second and third parameters match up to the ``depth`` and ``options`` arguments of the
`json_decode <http://php.net/manual/en/function.json-decode.php>`_ PHP function.

**Retrieving Raw data (PUT, PATCH, DELETE)**

Finally, you can grab the contents of php://input as a raw stream with ``getRawInput()``::

	$data = $request->getRawInput();

This will retrieve data and convert it to an array. Like this::

	var_dump($request->getRawInput());

	[
		'Param1' => 'Value1',
		'Param2' => 'Value2'
	]

**Filtering Input Data**

To maintain security of your application, you will want to filter all input as you access it. You can
pass the type of filter to use as the last parameter of any of these methods. The native ``filter_var()``
function is used for the filtering. Head over to the PHP manual for a list of `valid
filter types <http://php.net/manual/en/filter.filters.php>`_.

Filtering a POST variable would look like this::

	$email = $request->getVar('email', FILTER_SANITIZE_EMAIL);

All of the methods mentioned above support the filter type passed in as the last parameter, with the
exception of ``getJSON()``.

Retrieving Headers
----------------------------------------------------------------------------

You can get access to any header that was sent with the request with the ``getHeaders()`` method, which returns
an array of all headers, with the key as the name of the header, and the value is an instance of
``CodeIgniter\HTTP\Header``::

	var_dump($request->getHeaders());

	[
		'Host'          => CodeIgniter\HTTP\Header,
		'Cache-Control' => CodeIgniter\HTTP\Header,
		'Accept'        => CodeIgniter\HTTP\Header,
	]

If you only need a single header, you can pass the name into the ``getHeader()`` method. This will grab the
specified header object in a case-insensitive manner if it exists. If not, then it will return ``null``::

	// these are all equivalent
	$host = $request->getHeader('host');
	$host = $request->getHeader('Host');
	$host = $request->getHeader('HOST');

You can always use ``hasHeader()`` to see if the header existed in this request::

	if ($request->hasHeader('DNT'))
	{
		// Don't track something...
	}

If you need the value of header as a string with all values on one line, you can use the ``getHeaderLine()`` method::

    // Accept-Encoding: gzip, deflate, sdch
    echo 'Accept-Encoding: '.$request->getHeaderLine('accept-encoding');

If you need the entire header, with the name and values in a single string, simply cast the header as a string::

	echo (string)$header;

The Request URL
----------------------------------------------------------------------------

You can retrieve a :doc:`URI </libraries/uri>` object that represents the current URI for this request through the
``$request->uri`` property. You can cast this object as a string to get a full URL for the current request::

	$uri = (string)$request->uri;

The object gives you full abilities to grab any part of the request on it's own::

	$uri = $request->uri;

	echo $uri->getScheme();         // http
	echo $uri->getAuthority();      // snoopy:password@example.com:88
	echo $uri->getUserInfo();       // snoopy:password
	echo $uri->getHost();           // example.com
	echo $uri->getPort();           // 88
	echo $uri->getPath();           // /path/to/page
	echo $uri->getQuery();          // foo=bar&bar=baz
	echo $uri->getSegments();       // ['path', 'to', 'page']
	echo $uri->getSegment(1);       // 'path'
	echo $uri->getTotalSegments();  // 3

Uploaded Files
----------------------------------------------------------------------------

Information about all uploaded files can be retrieved through ``$request->getFiles()``, which returns a
:doc:`FileCollection </libraries/uploaded_files>` instance. This helps to ease the pain of working with uploaded files,
and uses best practices to minimize any security risks.
::

	$files = $request->getFiles();

	// Grab the file by name given in HTML form
	if ($files->hasFile('uploadedFile')
	{
		$file = $files->getFile('uploadedfile');

		// Generate a new secure name
		$name = $file->getRandomName();

		// Move the file to it's new home
		$file->move('/path/to/dir', $name);

		echo $file->getSize('mb');      // 1.23
		echo $file->getExtension();     // jpg
		echo $file->getType();          // image/jpg
	}

You can retrieve a single file uploaded on its own, based on the filename given in the HTML file input::

	$file = $request->getFile('uploadedfile');

You can retrieve an array of same-named files uploaded as part of a 
multi-file upload, based on the filename given in the HTML file input::

	$files = $request->getFileMultiple('uploadedfile');

Content Negotiation
----------------------------------------------------------------------------

You can easily negotiate content types with the request through the ``negotiate()`` method::

	$language    = $request->negotiate('language', ['en-US', 'en-GB', 'fr', 'es-mx']);
	$imageType   = $request->negotiate('media', ['image/png', 'image/jpg']);
	$charset     = $request->negotiate('charset', ['UTF-8', 'UTF-16']);
	$contentType = $request->negotiate('media', ['text/html', 'text/xml']);
	$encoding    = $request->negotiate('encoding', ['gzip', 'compress']);

See the :doc:`Content Negotiation </incoming/content_negotiation>` page for more details.

Class Reference
===========================================================================

.. note:: In addition to the methods listed here, this class inherits the methods from the
	:doc:`Request Class </incoming/request>` and the :doc:`Message Class </incoming/message>`.

The methods provided by the parent classes that are available are:

* :meth:`CodeIgniter\\HTTP\\Request::getIPAddress`
* :meth:`CodeIgniter\\HTTP\\Request::validIP`
* :meth:`CodeIgniter\\HTTP\\Request::getMethod`
* :meth:`CodeIgniter\\HTTP\\Request::getServer`
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

	.. php:method:: getVar([$index = null[, $filter = null[, $flags = null]]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:   $_REQUEST if no parameters supplied, otherwise the REQUEST value if found, or null if not
		:rtype: mixed|null

		The first parameter will contain the name of the REQUEST item you are looking for::

			$request->getVar('some_data');

		The method returns null if the item you are attempting to retrieve
		does not exist.

		The second optional parameter lets you run the data through the PHP's
		filters. Pass in the desired filter type as the second parameter::

			$request->getVar('some_data', FILTER_SANITIZE_STRING);

		To return an array of all POST items call without any parameters.

		To return all POST items and pass them through the filter, set the
		first parameter to null while setting the second parameter to the filter
		you want to use::

			$request->getVar(null, FILTER_SANITIZE_STRING); // returns all POST items with string sanitation

		To return an array of multiple POST parameters, pass all the required keys as an array::

			$request->getVar(['field1', 'field2']);

		Same rule applied here, to retrieve the parameters with filtering, set the second parameter to
		the filter type to apply::

			$request->getVar(['field1', 'field2'], FILTER_SANITIZE_STRING);

	.. php:method:: getGet([$index = null[, $filter = null[, $flags = null]]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:   $_GET if no parameters supplied, otherwise the GET value if found, or null if not
		:rtype: mixed|null

		This method is identical to ``getVar()``, only it fetches GET data.

	.. php:method:: getPost([$index = null[, $filter = null[, $flags = null]]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

			This method is identical to ``getVar()``, only it fetches POST data.

	.. php:method:: getPostGet([$index = null[, $filter = null[, $flags = null]]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		This method works pretty much the same way as ``getPost()`` and ``getGet()``, only combined.
		It will search through both POST and GET streams for data, looking first in POST, and
		then in GET::

			$request->getPostGet('field1');

	.. php:method:: getGetPost([$index = null[, $filter = null[, $flags = null]]])

		:param  string  $index: The name of the variable/key to look for.
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:   $_POST if no parameters supplied, otherwise the POST value if found, or null if not
		:rtype: mixed|null

		This method works pretty much the same way as ``getPost()`` and ``getGet()``, only combined.
		It will search through both POST and GET streams for data, looking first in GET, and
		then in POST::

			$request->getGetPost('field1');

	.. php:method:: getCookie([$index = null[, $filter = null[, $flags = null]]])

                :noindex:
		:param	mixed	$index: COOKIE name
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:	$_COOKIE if no parameters supplied, otherwise the COOKIE value if found or null if not
		:rtype:	mixed

		This method is identical to ``getPost()`` and ``getGet()``, only it fetches cookie data::

			$request->getCookie('some_cookie');
			$request->getCookie('some_cookie', FILTER_SANITIZE_STRING); // with filter

		To return an array of multiple cookie values, pass all the required keys as an array::

			$request->getCookie(['some_cookie', 'some_cookie2']);

		.. note:: Unlike the :doc:`Cookie Helper <../helpers/cookie_helper>`
			function :php:func:`get_cookie()`, this method does NOT prepend
			your configured ``$config['cookie_prefix']`` value.

	.. php:method:: getServer([$index = null[, $filter = null[, $flags = null]]])

		:param	mixed	$index: Value name
		:param  int     $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:param  int     $flags: Flags to apply. A list of flags can be found `here <http://php.net/manual/en/filter.filters.flags.php>`__.
		:returns:	$_SERVER item value if found, NULL if not
		:rtype:	mixed

		This method is identical to the ``getPost()``, ``getGet()`` and ``getCookie()``
		methods, only it fetches getServer data (``$_SERVER``)::

			$request->getServer('some_data');

		To return an array of multiple ``$_SERVER`` values, pass all the required keys
		as an array.
		::

			$request->getServer(['SERVER_PROTOCOL', 'REQUEST_URI']);

	.. php:method:: getUserAgent([$filter = null])

		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`__.
		:returns:  The User Agent string, as found in the SERVER data, or null if not found.
		:rtype: mixed

		This method returns the User Agent string from the SERVER data::

			$request->getUserAgent();
