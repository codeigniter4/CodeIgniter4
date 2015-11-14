#################
CURLRequest Class
#################

The ``CURLRequest`` class is a lightweight HTTP client based on CURL that allows you to talk to other
web sites and servers. It can be used to get the contents of a Google search, retrieve a web page or image,
or communicate with an API, among many other things.

This class is modeled after the `Guzzle HTTP Client <http://docs.guzzlephp.org/en/latest/>`_ library since
it is one of the more widely used libraries. Where possible, the syntax has been kept the same so that if
your application needs something a little more powerful than what this library provides, you will have
to change very little to move over to use Guzzle.

:note: This class requires the `cURL Library <http://php.net/manual/en/book.curl.php>`_ to be installed
in your version of PHP. This is a very common library that is typically available but not all hosts
will provide it, so please check with your host to verify if you run into problems.

*******************
Loading the Library
*******************

The library can be loaded either manually or through the :doc:`Services class </concepts/services>`.

To load with the Services class call the ``curlrequest()` method::

	$client = CodeIgniter\HTTP\Services::curlrequest();

You can pass in an array of default options as the first parameter to modify how cURL will handle the request.
The options are described later in this document.::

	$options = [
		'base_uri' => 'http://example.com/api/v1/',
		'timeout' => 3
	];
	$client = App\Config\Services::curlrequest($options);

When creating the class manually, you need to pass a few dependencies in. The first parameter is an
instance of the ``App\Config\AppConfig`` class. The second parameter is a URI instance. The third
parameter is a Response object. The fourth parameter is the optional ``$options`` array.::

	$client = new CodeIgniter\HTTP\CURLRequest(
		new App\Config\AppConfig(),
		new CodeIgniter\HTTP\URI(),
		new CodeIgniter\HTTP\Response(),
		$options
	);

************************
Working with the Library
************************

Working with CURL requests is simply a matter of creating the Request and getting a
:doc:`Response object </libraries/response>` back. It is meant to handle the communications. After that
you have complete control over how the information is handled.

Making Requests
===============

Most communication is done through the ``request()`` method, which fires off the request, and then returns
a Response instance to you. This takes the HTTP method, the url and an array of options as the parameters.
::

	$client = Services::curlrequest();

	$response = $client->request('GET', 'https://api.github.com/user', [
		'auth' => ['user', 'pass']
	]);

Since the response is an instance of ``CodeIgniter\HTTP\Response`` you have all of the normal information
available to you::

	echo $response->statusCode();
	echo $response->body();
	echo $response->header('Content-Type');
	$language = $response->negotiateLanguage(['en', 'fr']);

While the ``request()`` method is the most flexible, you can also use the following shortcut methods. They
each take the URL as the first parameter and an array of options as the second.::

* $client->get('http://example.com');
* $client->delete('http://example.com');
* $client->head('http://example.com');
* $client->options('http://example.com');
* $client->patch('http://example.com');
* $client->put('http://example.com');
* $client->post('http://example.com');

Base URI
--------

A ``base_uri`` can be set as one of the options during the instantiation of the class. This allows you to
set a base URI, and then make all requests with that client using relative URLs. This is especially handy
when working with APIs.

	$client = Services::curlrequest([
		'base_uri' => 'https://example.com/api/v1/'
	]);

	// GET http:example.com/api/v1/photos
	$client->get('photos');

	// GET http:example.com/api/v1/photos/13
	$client->delete('photos/13');

When a relative URI is provided to the ``request()`` method or any of the shortcut methods, it will be combined
with the base_uri according to the rules described by
`RFC 2986, section 2 <http://tools.ietf.org/html/rfc3986#section-5.2>`_. To save you some time, here are some
examples of how the combinations are resolved.

	===================   ==============   ======================
	base_uri              URI              Result
	===================   ==============   ======================
	http://foo.com        /bar             http://foo.com/bar
	http://foo.com/foo    /bar             http://foo.com/bar
	http://foo.com/foo    bar              http://foo.com/bar
	http://foo.com/foo/   bar              http://foo.com/foo/bar
	http://foo.com        http://baz.com   http://baz.com
	http://foo.com/?bar   bar              http://foo.com/bar
	===================   ==============   ======================

Using Responses
===============

Each ``request()`` call returns a Respons object that contains a lot of useful information and some helpful
methods. The most commonly used methods let you determine the response itself.

You can get the status code and reason phrase of the response::

	$code = $response->statusCode();    // 200
	$reason = $response->reason();      // OK

You can retrieve headers from the response::

	// Get a header
	echo $response->header('Content-type');

	// Get all headers
	foreach ($repsonse->headers() as $name => $value)
	{
		echo $name .': '. $response->headerLine($name) ."\n";
	}

The body can be retrieved using the ``body()`` method::

	$body = $response->body();

The body is the raw body provided by the remote server. If the content type requires formatting, you will need
to ensure that your script handles that::

	if (strpos($response->header('content-type'), 'application/json') !== false)
	{
		$body = json_decode($body);
	}

===============
Request Options
===============

This section describes all of the available options you may pass into the constructor, the ``request()`` method,
or any of the shortcut methods.

