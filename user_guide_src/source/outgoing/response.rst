==============
HTTP Responses
==============

The Response class extends the :doc:`HTTP Message Class </incoming/message>` with methods only appropriate for
a server responding to the client that called it.

.. contents::
    :local:
    :depth: 2

Working with the Response
=========================

A Response class is instantiated for you and passed into your controllers. It can be accessed through
``$this->response``. Many times you will not need to touch the class directly, since CodeIgniter takes care of
sending the headers and the body for you. This is great if the page successfully created the content it was asked to.
When things go wrong, or you need to send very specific status codes back, or even take advantage of the
powerful HTTP caching, it's there for you.

Setting the Output
------------------

When you need to set the output of the script directly, and not rely on CodeIgniter to automatically get it, you
do it manually with the ``setBody`` method. This is usually used in conjunction with setting the status code of
the response::

	$this->response->setStatusCode(404)
	               ->setBody($body);

The reason phrase ('OK', 'Created', 'Moved Permanently') will be automatically added, but you can add custom reasons
as the second parameter of the ``setStatusCode()`` method::

	$this->response->setStatusCode(404, 'Nope. Not here.');

You can set format an array into either JSON or XML and set the content type header to the appropriate mime with the
``setJSON`` and ``setXML`` methods. Typically, you will send an array of data to be converted::

	$data = [
		'success' => true,
		'id' => 123
	];

	return $this->response->setJSON($data);
		or
	return $this->response->setXML($data);

Setting Headers
---------------

Often, you will need to set headers to be set for the response. The Response class makes this very simple to do,
with the ``setHeader()`` method. The first parameter is the name of the header. The second parameter is the value,
which can be either a string or an array of values that will be combined correctly when sent to the client.
Using these functions instead of using the native PHP functions allows you to ensure that no headers are sent
prematurely, causing errors, and makes testing possible.
::

	$response->setHeader('Location', 'http://example.com')
	         ->setHeader('WWW-Authenticate', 'Negotiate');

If the header exists and can have more than one value, you may use the ``appendHeader()`` and ``prependHeader()``
methods to add the value to the end or beginning of the values list, respectively. The first parameter is the name
of the header, while the second is the value to append or prepend.
::

	$response->setHeader('Cache-Control', 'no-cache')
	         ->appendHeader('Cache-Control', 'must-revalidate');

Headers can be removed from the response with the ``removeHeader()`` method, which takes the header name as the only
parameter. This is not case-sensitive.
::

	$response->removeHeader('Location');

Force File Download
===================

The Response class provides a simple way to send a file to the client, prompting the browser to download the data
to your computer. This sets the appropriate headers to make it happen.

The first parameter is the **name you want the downloaded file to be named**, the second parameter is the
file data.

If you set the second parameter to NULL and ``$filename`` is an existing, readable
file path, then its content will be read instead.

If you set the third parameter to boolean TRUE, then the actual file MIME type
(based on the filename extension) will be sent, so that if your browser has a
handler for that type - it can use it.

Example::

	$data = 'Here is some text!';
	$name = 'mytext.txt';
	return $response->download($name, $data);

If you want to download an existing file from your server you'll need to
do the following::

	// Contents of photo.jpg will be automatically read
	return $response->download('/path/to/photo.jpg', NULL);

Use the optional ``setFileName()`` method to change the filename as it is sent to the client's browser::
	
	return $response->download('awkwardEncryptedFileName.fakeExt')->setFileName('expenses.csv');

.. note:: The response object MUST be returned for the download to be sent to the client. This allows the response
    to be passed through all **after** filters before being sent to the client.

HTTP Caching
============

Built into the HTTP specification are tools help the client (often the web browser) cache the results. Used correctly,
this can lend a huge performance boost to your application because it will tell the client that they don't need
to contact the getServer at all since nothing has changed. And you can't get faster than that.

This are handled through the ``Cache-Control`` and ``ETag`` headers. This guide is not the proper place for a thorough
introduction to all of the cache headers power, but you can get a good understanding over at
`Google Developers <https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching>`_
and the `Mobify Blog <https://www.mobify.com/blog/beginners-guide-to-http-cache-headers/>`_.

By default, all response objects sent through CodeIgniter have HTTP caching turned off. The options and exact
circumstances are too varied for us to be able to create a good default other than turning it off. It's simple
to set the Cache values to what you need, though, through the ``setCache()`` method::

	$options = [
		'max-age'  => 300,
		's-maxage' => 900
		'etag'     => 'abcde',
	];
	$this->response->setCache($options);

The ``$options`` array simply takes an array of key/value pairs that are, with a couple of exceptions, assigned
to the ``Cache-Control`` header. You are free to set all of the options exactly as you need for your specific
situation. While most of the options are applied to the ``Cache-Control`` header, it intelligently handles
the ``etag`` and ``last-modified`` options to their appropriate header.

Content Security Policy
=======================

One of the best protections you have against XSS attacks is to implement a Content Security Policy on the site.
This forces you to whitelist every single source of content that is pulled in from your site's HTML,
including images, stylesheets, javascript files, etc. The browser will refuse content from sources that don't meet
the whitelist. This whitelist is created within the response's ``Content-Security-Policy`` header and has many
different ways it can be configured.

This sounds complex, and on some sites, can definitely be challenging. For many simple sites, though, where all content
is served by the same domain (http://example.com), it is very simple to integrate.

As this is a complex subject, this user guide will not go over all of the details. For more information, you should
visit the following sites:

* `Content Security Policy main site <http://content-security-policy.com/>`_
* `W3C Specification <https://www.w3.org/TR/CSP>`_
* `Introduction at HTML5Rocks <http://www.html5rocks.com/en/tutorials/security/content-security-policy/>`_
* `Article at SitePoint <https://www.sitepoint.com/improving-web-security-with-the-content-security-policy/>`_

Turning CSP On
--------------

By default, support for this is off. To enable support in your application, edit the ``CSPEnabled`` value in
**app/Config/App.php**::

	public $CSPEnabled = true;

When enabled, the response object will contain an instance of ``CodeIgniter\HTTP\ContentSecurityPolicy``. The
values set in **app/Config/ContentSecurityPolicy.php** are applied to that instance, and if no changes are
needed during runtime, then the correctly formatted header is sent and you're all done.

With CSP enabled, two header lines are added to the HTTP response: a Content-Security-Policy header, with
policies identifying content types or origins that are explicitly allowed for different
contexts, and a Content-Security-Policy-Report-Only header, which identifies content types 
or origins that will be allowed but which will also be reported to the destination
of your choice.

Our implementation provides for a default treatment, changeable through the ``reportOnly()`` method.
When an additional entry is added to a CSP directive, as shown below, it will be added
to the CSP header appropriate for blocking or preventing. That can be overridden on a per
call basis, by providing an optional second parameter to the adding method call.

Runtime Configuration
---------------------

If your application needs to make changes at run-time, you can access the instance at ``$response->CSP``. The
class holds a number of methods that map pretty clearly to the appropriate header value that you need to set.
Examples are shown below, with different combinations of parameters, though all accept either a directive
name or anarray of them.::

        // specify the default directive treatment 
	$response->CSP->reportOnly(false); 
        
        // specify the origin to use if none provided for a directive
	$response->CSP->setDefaultSrc('cdn.example.com'); 
        // specify the URL that "report-only" reports get sent to
	$response->CSP->setReportURI('http://example.com/csp/reports');
        // specify that HTTP requests be upgraded to HTTPS
	$response->CSP->upgradeInsecureRequests(true);

        // add types or origins to CSP directives
        // assuming that the default treatment is to block rather than just report
	$response->CSP->addBaseURI('example.com', true); // report only
	$response->CSP->addChildSrc('https://youtube.com'); // blocked
	$response->CSP->addConnectSrc('https://*.facebook.com', false); // blocked
	$response->CSP->addFontSrc('fonts.example.com');
	$response->CSP->addFormAction('self');
	$response->CSP->addFrameAncestor('none', true); // report this one
	$response->CSP->addImageSrc('cdn.example.com');
	$response->CSP->addMediaSrc('cdn.example.com');
	$response->CSP->addManifestSrc('cdn.example.com');
	$response->CSP->addObjectSrc('cdn.example.com', false); // reject from here
	$response->CSP->addPluginType('application/pdf', false); // reject this media type
	$response->CSP->addScriptSrc('scripts.example.com', true); // allow but report requests from here
	$response->CSP->addStyleSrc('css.example.com');
	$response->CSP->addSandbox(['allow-forms', 'allow-scripts']);


The first parameter to each of the "add" methods is an appropriate string value,
or an array of them.

The ``reportOnly`` method allows you to specify the default reporting treatment
for subsequent sources, unless over-ridden. For instance, you could specify
that youtube.com was allowed, and then provide several allowed but reported sources::

    $response->addChildSrc('https://youtube.com'); // allowed
    $response->reportOnly(true);
    $response->addChildSrc('https://metube.com'); // allowed but reported
    $response->addChildSrc('https://ourtube.com',false); // allowed

Inline Content
--------------

It is possible to set a website to not protect even inline scripts and styles on its own pages, since this might have
been the result of user-generated content. To protect against this, CSP allows you to specify a nonce within the
``<style>`` and ``<script>`` tags, and to add those values to the response's header. This is a pain to handle in real
life, and is most secure when generated on the fly. To make this simple, you can include a ``{csp-style-nonce}`` or
``{csp-script-nonce}`` placeholder in the tag and it will be handled for you automatically::

	// Original
	<script {csp-script-nonce}>
	    console.log("Script won't run as it doesn't contain a nonce attribute");
	</script>

	// Becomes
	<script nonce="Eskdikejidojdk978Ad8jf">
	    console.log("Script won't run as it doesn't contain a nonce attribute");
	</script>

	// OR
	<style {csp-style-nonce}>
		. . .
	</style>

***************
Class Reference
***************

.. note:: In addition to the methods listed here, this class inherits the methods from the
	:doc:`Message Class </incoming/message>`.

The methods provided by the parent class that are available are:

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

.. php:class:: CodeIgniter\\HTTP\\Response

	.. php:method:: getStatusCode()

		:returns: The current HTTP status code for this response
		:rtype: int

		Returns the currently status code for this response. If no status code has been set, a BadMethodCallException
		will be thrown::

			echo $response->getStatusCode();

	.. php:method:: setStatusCode($code[, $reason=''])

		:param int $code: The HTTP status code
		:param string $reason: An optional reason phrase.
		:returns: The current Response instance
		:rtype: CodeIgniter\\HTTP\\Response

		Sets the HTTP status code that should be sent with this response::

		    $response->setStatusCode(404);

		The reason phrase will be automatically generated based upon the official lists. If you need to set your own
		for a custom status code, you can pass the reason phrase as the second parameter::

			$response->setStatusCode(230, "Tardis initiated");

	.. php:method:: getReason()

		:returns: The current reason phrase.
		:rtype: string

		Returns the current status code for this response. If not status has been set, will return an empty string::

			echo $response->getReason();

	.. php:method:: setDate($date)

		:param DateTime $date: A DateTime instance with the time to set for this response.
		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Sets the date used for this response. The ``$date`` argument must be an instance of ``DateTime``::

			$date = DateTime::createFromFormat('j-M-Y', '15-Feb-2016');
			$response->setDate($date);

	.. php:method:: setContentType($mime[, $charset='UTF-8'])

		:param string $mime: The content type this response represents.
		:param string $charset: The character set this response uses.
		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Sets the content type this response represents::

			$response->setContentType('text/plain');
			$response->setContentType('text/html');
			$response->setContentType('application/json');

		By default, the method sets the character set to ``UTF-8``. If you need to change this, you can
		pass the character set as the second parameter::

			$response->setContentType('text/plain', 'x-pig-latin');

	.. php:method:: noCache()

		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Sets the ``Cache-Control`` header to turn off all HTTP caching. This is the default setting
		of all response messages::

		    $response->noCache();

		    // Sets the following header:
		    Cache-Control: no-store, max-age=0, no-cache

	.. php:method:: setCache($options)

		:param array $options: An array of key/value cache control settings
		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Sets the ``Cache-Control`` headers, including ``ETags`` and ``Last-Modified``. Typical keys are:

		* etag
		* last-modified
		* max-age
		* s-maxage
		* private
		* public
		* must-revalidate
		* proxy-revalidate
		* no-transform

		When passing the last-modified option, it can be either a date string, or a DateTime object.

	.. php:method:: setLastModified($date)

		:param string|DateTime $date: The date to set the Last-Modified header to
		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Sets the ``Last-Modified`` header. The ``$date`` object can be either a string or a ``DateTime``
		instance::

			$response->setLastModified(date('D, d M Y H:i:s'));
			$response->setLastModified(DateTime::createFromFormat('u', $time));

	.. php:method:: send()
                :noindex:

		:returns: The current response instance.
		:rtype: CodeIgniter\HTTP\Response

		Tells the response to send everything back to the client. This will first send the headers,
		followed by the response body. For the main application response, you do not need to call
		this as it is handled automatically by CodeIgniter.

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

		Using this method, an associative array is passed as the first
		parameter::

			$cookie = [
				'name'   => 'The Cookie Name',
				'value'  => 'The Value',
				'expire' => '86500',
				'domain' => '.some-domain.com',
				'path'   => '/',
				'prefix' => 'myprefix_',
				'secure' => TRUE,
                                'httponly' => FALSE
			];

			$response->setCookie($cookie);

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

			$response->setCookie($name, $value, $expire, $domain, $path, $prefix, $secure, $httponly);

	.. php:method:: deleteCookie($name = ''[, $domain = ''[, $path = '/'[, $prefix = '']]])

		:param	mixed	$name: Cookie name or an array of parameters
		:param	string	$domain: Cookie domain
		:param	string	$path: Cookie path
		:param	string	$prefix: Cookie name prefix
		:rtype:	void

		Delete an existing cookie by setting its expiry to blank.

		**Notes**

		Only the name is required.

		The prefix is only needed if you need to avoid name collisions with
		other identically named cookies for your server.

		Provide a prefix if cookies should only be deleted for that subset.
                Provide a domain name if cookies should only be deleted for that domain.
                Provide a path name if cookies should only be deleted for that path.

                If any of the optional parameters are empty, then the same-named
                cookie will be deleted across all that apply.

		Example::

			$response->deleteCookie($name);

	.. php:method:: hasCookie($name = ''[, $value = null[, $prefix = '']])

		:param	mixed	$name: Cookie name or an array of parameters
		:param	string	$value: cookie value
		:param	string	$prefix: Cookie name prefix
		:rtype:	boolean

		Checks to see if the Response has a specified cookie or not.

		**Notes**

		Only the name is required. If a prefix is specified, it will be
                pre-pended to the cookie name.

                If no value is given, the method just checks for the existence
                of the named cookie. If a value is given, then the method checks
                that the cookie exists, and that it has the prescribed value.

		Example::

			if ($response->hasCookie($name)) ...

	.. php:method:: getCookie($name = ''[, $prefix = ''])
                :noindex:

		:param	mixed	$name: Cookie name
		:param	string	$prefix: Cookie name prefix
		:rtype:	boolean

		Returns the named cookie, if found, or null.

                If no name is given, returns the array of cookies.

                Each cookie is returned as an associative array.

		Example::

			$cookie = $response->getCookie($name);
