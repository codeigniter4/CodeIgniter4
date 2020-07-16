#################
CURLRequest Class
#################

The ``CURLRequest`` class is a lightweight HTTP client based on CURL that allows you to talk to other
web sites and servers. It can be used to get the contents of a Google search, retrieve a web page or image,
or communicate with an API, among many other things.

.. contents::
    :local:
    :depth: 2

This class is modeled after the `Guzzle HTTP Client <http://docs.guzzlephp.org/en/latest/>`_ library since
it is one of the more widely used libraries. Where possible, the syntax has been kept the same so that if
your application needs something a little more powerful than what this library provides, you will have
to change very little to move over to use Guzzle.

.. note:: This class requires the `cURL Library <https://www.php.net/manual/en/book.curl.php>`_ to be installed
    in your version of PHP. This is a very common library that is typically available but not all hosts
    will provide it, so please check with your host to verify if you run into problems.

*******************
Loading the Library
*******************

The library can be loaded either manually or through the :doc:`Services class </concepts/services>`.

To load with the Services class call the ``curlrequest()`` method::

	$client = \Config\Services::curlrequest();

You can pass in an array of default options as the first parameter to modify how cURL will handle the request.
The options are described later in this document::

	$options = [
		'baseURI' => 'http://example.com/api/v1/',
		'timeout'  => 3
	];
	$client = \Config\Services::curlrequest($options);

When creating the class manually, you need to pass a few dependencies in. The first parameter is an
instance of the ``Config\App`` class. The second parameter is a URI instance. The third
parameter is a Response object. The fourth parameter is the optional ``$options`` array::

	$client = new \CodeIgniter\HTTP\CURLRequest(
		new \Config\App(),
		new \CodeIgniter\HTTP\URI(),
		new \CodeIgniter\HTTP\Response(new \Config\App()),
		$options
	);

************************
Working with the Library
************************

Working with CURL requests is simply a matter of creating the Request and getting a
:doc:`Response object </outgoing/response>` back. It is meant to handle the communications. After that
you have complete control over how the information is handled.

Making Requests
===============

Most communication is done through the ``request()`` method, which fires off the request, and then returns
a Response instance to you. This takes the HTTP method, the url and an array of options as the parameters.
::

	$client = \Config\Services::curlrequest();

	$response = $client->request('GET', 'https://api.github.com/user', [
		'auth' => ['user', 'pass']
	]);

Since the response is an instance of ``CodeIgniter\HTTP\Response`` you have all of the normal information
available to you::

	echo $response->getStatusCode();
	echo $response->getBody();
	echo $response->getHeader('Content-Type');
	$language = $response->negotiateLanguage(['en', 'fr']);

While the ``request()`` method is the most flexible, you can also use the following shortcut methods. They
each take the URL as the first parameter and an array of options as the second::

    $client->get('http://example.com');
    $client->delete('http://example.com');
    $client->head('http://example.com');
    $client->options('http://example.com');
    $client->patch('http://example.com');
    $client->put('http://example.com');
    $client->post('http://example.com');

Base URI
--------

A ``baseURI`` can be set as one of the options during the instantiation of the class. This allows you to
set a base URI, and then make all requests with that client using relative URLs. This is especially handy
when working with APIs::

	$client = \Config\Services::curlrequest([
		'baseURI' => 'https://example.com/api/v1/'
	]);

	// GET http:example.com/api/v1/photos
	$client->get('photos');

	// GET http:example.com/api/v1/photos/13
	$client->delete('photos/13');

When a relative URI is provided to the ``request()`` method or any of the shortcut methods, it will be combined
with the baseURI according to the rules described by
`RFC 2986, section 2 <https://tools.ietf.org/html/rfc3986#section-5.2>`_. To save you some time, here are some
examples of how the combinations are resolved.

	=====================   ================   ========================
	baseURI                 URI                Result
	=====================   ================   ========================
	`http://foo.com`        /bar               `http://foo.com/bar`
	`http://foo.com/foo`    /bar               `http://foo.com/bar`
	`http://foo.com/foo`    bar                `http://foo.com/bar`
	`http://foo.com/foo/`   bar                `http://foo.com/foo/bar`
	`http://foo.com`        `http://baz.com`   `http://baz.com`
	`http://foo.com/?bar`   bar                `http://foo.com/bar`
	=====================   ================   ========================

Using Responses
===============

Each ``request()`` call returns a Response object that contains a lot of useful information and some helpful
methods. The most commonly used methods let you determine the response itself.

You can get the status code and reason phrase of the response::

	$code   = $response->getStatusCode();    // 200
	$reason = $response->getReason();      // OK

You can retrieve headers from the response::

	// Get a header line
	echo $response->getHeaderLine('Content-Type');

	// Get all headers
	foreach ($response->getHeaders() as $name => $value)
	{
		echo $name .': '. $response->getHeaderLine($name) ."\n";
	}

The body can be retrieved using the ``getBody()`` method::

	$body = $response->getBody();

The body is the raw body provided by the remote getServer. If the content type requires formatting, you will need
to ensure that your script handles that::

	if (strpos($response->getHeader('content-type'), 'application/json') !== false)
	{
		$body = json_decode($body);
	}

***************
Request Options
***************

This section describes all of the available options you may pass into the constructor, the ``request()`` method,
or any of the shortcut methods.

allow_redirects
===============

By default, cURL will follow all "Location:" headers the remote servers send back. The ``allow_redirects`` option
allows you to modify how that works.

If you set the value to ``false``, then it will not follow any redirects at all::

	$client->request('GET', 'http://example.com', ['allow_redirects' => false]);

Setting it to ``true`` will apply the default settings to the request::

	$client->request('GET', 'http://example.com', ['allow_redirects' => true]);

	// Sets the following defaults:
	'max'       => 5, // Maximum number of redirects to follow before stopping
	'strict'    => true, // Ensure POST requests stay POST requests through redirects
	'protocols' => ['http', 'https'] // Restrict redirects to one or more protocols

You can pass in array as the value of the ``allow_redirects`` option to specify new settings in place of the defaults::

	$client->request('GET', 'http://example.com', ['allow_redirects' => [
		'max'       => 10,
		'protocols' => ['https'] // Force HTTPS domains only.
	]]);

.. note:: Following redirects does not work when PHP is in safe_mode or open_basedir is enabled.

auth
====

Allows you to provide Authentication details for `HTTP Basic <https://www.ietf.org/rfc/rfc2069.txt>`_ and
`Digest <https://www.ietf.org/rfc/rfc2069.txt>`_ and authentication. Your script may have to do extra to support
Digest authentication - this simply passes the username and password along for you. The value must be an
array where the first element is the username, and the second is the password. The third parameter should be
the type of authentication to use, either ``basic`` or ``digest``::

	$client->request('GET', 'http://example.com', ['auth' => ['username', 'password', 'digest']]);

body
====

There are two ways to set the body of the request for request types that support them, like PUT, OR POST.
The first way is to use the ``setBody()`` method::

	$client->setBody($body)
	       ->request('put', 'http://example.com');

The second method is by passing a ``body`` option in. This is provided to maintain Guzzle API compatibility,
and functions the exact same way as the previous example. The value must be a string::

	$client->request('put', 'http://example.com', ['body' => $body]);

cert
====

To specify the location of a PEM formatted client-side certificate, pass a string with the full path to the
file as the ``cert`` option. If a password is required, set the value to an array with the first element
as the path to the certificate, and the second as the password::

    $client->request('get', '/', ['cert' => ['/path/getServer.pem', 'password']);

connect_timeout
===============

By default, CodeIgniter does not impose a limit for cURL to attempt to connect to a website. If you need to
modify this value, you can do so by passing the amount of time in seconds with the ``connect_timeout`` option.
You can pass 0 to wait indefinitely::

	$response->request('GET', 'http://example.com', ['connect_timeout' => 0]);

cookie
======

This specifies the filename that CURL should use to read cookie values from, and
to save cookie values to. This is done using the CURL_COOKIEJAR and CURL_COOKIEFILE options.
An example::

	$response->request('GET', 'http://example.com', ['cookie' => WRITEPATH . 'CookieSaver.txt']);

debug
=====

When ``debug`` is passed and set to ``true``, this will enable additional debugging to echo to STDERR during the
script execution. This is done by passing CURLOPT_VERBOSE and echoing the output. So, when you're running a built-in
server via ``spark serve`` you will see the output in the console. Otherwise, the output will be written to
the server's error log.

	$response->request('GET', 'http://example.com', ['debug' => true]);

You can pass a filename as the value for debug to have the output written to a file::

	$response->request('GET', 'http://example.com', ['debug' => '/usr/local/curl_log.txt']);

delay
=====

Allows you to pause a number of milliseconds before sending the request::

	// Delay for 2 seconds
	$response->request('GET', 'http://example.com', ['delay' => 2000]);

form_params
===========

You can send form data in an application/x-www-form-urlencoded POST request by passing an associative array in
the ``form_params`` option. This will set the ``Content-Type`` header to ``application/x-www-form-urlencoded``
if it's not already set::

	$client->request('POST', '/post', [
		'form_params' => [
			'foo' => 'bar',
			'baz' => ['hi', 'there']
		]
	]);

.. note:: ``form_params`` cannot be used with the ``multipart`` option. You will need to use one or the other.
        Use ``form_params`` for ``application/x-www-form-urlencoded`` request, and ``multipart`` for ``multipart/form-data``
        requests.

headers
=======

While you can set any headers this request needs by using the ``setHeader()`` method, you can also pass an associative
array of headers in as an option. Each key is the name of a header, and each value is a string or array of strings
representing the header field values::

	$client->request('get', '/', [
		'headers' => [
			'User-Agent' => 'testing/1.0',
			'Accept'     => 'application/json',
			'X-Foo'      => ['Bar', 'Baz']
		]
	]);

If headers are passed into the constructor they are treated as default values that will be overridden later by any
further headers arrays or calls to ``setHeader()``.

http_errors
===========

By default, CURLRequest will fail if the HTTP code returned is greater than or equal to 400. You can set
``http_errors`` to ``false`` to return the content instead::

    $client->request('GET', '/status/500');
    // Will fail verbosely

    $res = $client->request('GET', '/status/500', ['http_errors' => false]);
    echo $res->getStatusCode();
    // 500

json
====

The ``json`` option is used to easily upload JSON encoded data as the body of a request. A Content-Type header
of ``application/json`` is added, overwriting any Content-Type that might be already set. The data provided to
this option can be any value that ``json_encode()`` accepts::

	$response = $client->request('PUT', '/put', ['json' => ['foo' => 'bar']]);

.. note:: This option does not allow for any customization of the ``json_encode()`` function, or the Content-Type
        header. If you need that ability, you will need to encode the data manually, passing it through the ``setBody()``
        method of CURLRequest, and set the Content-Type header with the ``setHeader()`` method.

multipart
=========

When you need to send files and other data via a POST request, you can use the ``multipart`` option, along with
the `CURLFile Class <https://www.php.net/manual/en/class.curlfile.php>`_. The values should be an associative array
of POST data to send. For safer usage, the legacy method of uploading files by prefixing their name with an `@`
has been disabled. Any files that you want to send must be passed as instances of CURLFile::

	$post_data = [
		'foo'      => 'bar',
		'userfile' => new \CURLFile('/path/to/file.txt')
	];

.. note:: ``multipart`` cannot be used with the ``form_params`` option. You can only use one or the other. Use
        ``form_params`` for ``application/x-www-form-urlencoded`` requests, and ``multipart`` for ``multipart/form-data``
        requests.

query
=====

You can pass along data to send as query string variables by passing an associative array as the ``query`` option::

	// Send a GET request to /get?foo=bar
	$client->request('GET', '/get', ['query' => ['foo' => 'bar']]);

timeout
=======

By default, cURL functions are allowed to run as long as they take, with no time limit. You can modify this with the ``timeout``
option. The value should be the number of seconds you want the functions to execute for. Use 0 to wait indefinitely::

	$response->request('GET', 'http://example.com', ['timeout' => 5]);

verify
======

This option describes the SSL certificate verification behavior. If the ``verify`` option is ``true``, it enables the
SSL certificate verification and uses the default CA bundle provided by the operating system. If set to ``false`` it
will disable the certificate verification (this is insecure, and allows man-in-the-middle attacks!). You can set it
to a string that contains the path to a CA bundle to enable verification with a custom certificate. The default value
is true::

	// Use the system's CA bundle (this is the default setting)
	$client->request('GET', '/', ['verify' => true]);

	// Use a custom SSL certificate on disk.
	$client->request('GET', '/', ['verify' => '/path/to/cert.pem']);

	// Disable validation entirely. (Insecure!)
	$client->request('GET', '/', ['verify' => false]);

version
=======

To set the HTTP protocol to use, you can pass a string or float with the version number (typically either 1.0
or 1.1, 2.0 is currently unsupported.)::

	// Force HTTP/1.0
	$client->request('GET', '/', ['version' => 1.0]);
