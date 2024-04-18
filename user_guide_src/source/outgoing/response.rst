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

A Response class is instantiated for you and passed into your controllers. It can
be accessed through ``$this->response``. It is the same instance that
``Services::response()`` returns. We call it the global response instance.

Many times you will not need to touch the class directly, since CodeIgniter takes care of
sending the headers and the body for you. This is great if the page successfully created the content it was asked to.
When things go wrong, or you need to send very specific status codes back, or even take advantage of the
powerful HTTP caching, it's there for you.

Setting the Output
------------------

When you need to set the output of the script directly, and not rely on CodeIgniter to automatically get it, you
do it manually with the ``setBody`` method. This is usually used in conjunction with setting the status code of
the response:

.. literalinclude:: response/001.php

The reason phrase ('OK', 'Created', 'Moved Permanently') will be automatically added, but you can add custom reasons
as the second parameter of the ``setStatusCode()`` method:

.. literalinclude:: response/002.php

You can set format an array into either JSON or XML and set the content type header to the appropriate mime with the
``setJSON()`` and ``setXML()`` methods. Typically, you will send an array of data to be converted:

.. literalinclude:: response/003.php

Setting Headers
---------------

setHeader()
^^^^^^^^^^^

Often, you will need to set headers to be set for the response. The Response class makes this very simple to do,
with the ``setHeader()`` method.

The first parameter is the name of the header. The second parameter is the value,
which can be either a string or an array of values that will be combined correctly when sent to the client.

.. literalinclude:: response/004.php

Using these functions instead of using the native PHP functions allows you to ensure that no headers are sent
prematurely, causing errors, and makes testing possible.

.. note:: This method just sets headers to the response instance. So, if you create
    and return another response instance (e.g., if you call :php:func:`redirect()`),
    the headers set here will not be sent automatically.

appendHeader()
^^^^^^^^^^^^^^

If the header exists and can have more than one value, you may use the ``appendHeader()`` and ``prependHeader()``
methods to add the value to the end or beginning of the values list, respectively. The first parameter is the name
of the header, while the second is the value to append or prepend.

.. literalinclude:: response/005.php

removeHeader()
^^^^^^^^^^^^^^

Headers can be removed from the response with the ``removeHeader()`` method, which takes the header name as the only
parameter. This is not case-sensitive.

.. literalinclude:: response/006.php

.. _response-redirect:

Redirect
========

If you want to create a redirect, use the :php:func:`redirect()` function.

It returns a ``RedirectResponse`` instance. It is a different instance from the
global response instance that ``Services::response()`` returns.

.. warning:: If you set Cookies or Response Headers before you call ``redirect()``,
    they are set to the global response instance, and they are not automatically
    copied to the ``RedirectResponse`` instance. To send them, you need to call
    the ``withCookies()`` or ``withHeaders()`` method manually.

.. important:: If you want to redirect, an instance of ``RedirectResponse`` must
    be returned in a method of the :doc:`Controller <../incoming/controllers>` or
    the :doc:`Controller Filter <../incoming/filters>`. Note that the ``__construct()``
    or the ``initController()`` method cannot return any value.
    If you forget to return ``RedirectResponse``, no redirection will occur.

Redirect to a URI path
----------------------

When you want to pass a URI path (relative to baseURL), use ``redirect()->to()``:

.. literalinclude:: ./response/028.php
    :lines: 2-

.. note:: If there is a fragment in your URL that you want to remove, you can
    use the refresh parameter in the method.
    Like ``return redirect()->to('admin/home', null, 'refresh');``.

Redirect to a Defined Route
---------------------------

When you want to pass a :ref:`route name <using-named-routes>` or Controller::method
for :ref:`reverse routing <reverse-routing>`, use ``redirect()->route()``:

.. literalinclude:: ./response/029.php
    :lines: 2-

When passing an argument into the function, it is treated as a route name or
Controller::method for reverse routing, not a relative/full URI,
treating it the same as using ``redirect()->route()``:

.. literalinclude:: ./response/030.php
    :lines: 2-

Redirect Back
-------------

When you want to redirect back, use ``redirect()->back()``:

.. literalinclude:: ./response/031.php
    :lines: 2-

.. note:: ``redirect()->back()`` is not the same as browser "back" button.
    It takes a visitor to "the last page viewed during the Session" when the Session is available.
    If the Session hasn't been loaded, or is otherwise unavailable, then a sanitized version of HTTP_REFERER will be used.

Redirect with Cookies
---------------------

If you set Cookies before you call ``redirect()``, they are set to the global
response instance, and they are not automatically copied to the ``RedirectResponse``
instance.

To send the Cookies, you need to call the ``withCookies()`` method manually.

.. literalinclude:: ./response/034.php
    :lines: 2-

Redirect with Headers
---------------------

If you set Response Headers before you call ``redirect()``, they are set to the
global response instance, and they are not automatically copied to the
``RedirectResponse`` instance.

To send the Headers, you need to call the ``withHeaders()`` method manually.

.. literalinclude:: ./response/035.php
    :lines: 2-

.. _response-redirect-status-code:

Redirect Status Code
--------------------

The default HTTP status code for GET requests is 302. However, when using HTTP/1.1
or later, 303 is used for POST/PUT/DELETE requests and 307 for all other requests.

You can specify the status code:

.. literalinclude:: ./response/032.php
    :lines: 2-

.. note:: Due to a bug, in v4.3.3 or previous versions, the status code of the
    actual redirect response might be changed even if a status code was specified.
    See :ref:`ChangeLog v4.3.4 <v434-redirect-status-code>`.

If you don't know HTTP status code for redirection, it is recommended to read
`Redirections in HTTP <https://developer.mozilla.org/en-US/docs/Web/HTTP/Redirections>`_.

.. _force-file-download:

Force File Download
===================

The Response class provides a simple way to send a file to the client, prompting the browser to download the data
to your computer. This sets the appropriate headers to make it happen.

The first parameter is the **name you want the downloaded file to be named**, the second parameter is the
file data.

If you set the second parameter to null and ``$filename`` is an existing, readable
file path, then its content will be read instead.

If you set the third parameter to boolean true, then the actual file MIME type
(based on the filename extension) will be sent, so that if your browser has a
handler for that type - it can use it.

Example:

.. literalinclude:: response/007.php

If you want to download an existing file from your server you'll need to
pass ``null`` explicitly for the second parameter:

.. literalinclude:: response/008.php

Use the optional ``setFileName()`` method to change the filename as it is sent to the client's browser:

.. literalinclude:: response/009.php

.. note:: The response object MUST be returned for the download to be sent to the client. This allows the response
    to be passed through all **after** filters before being sent to the client.

.. _open-file-in-browser:

Open File in Browser
--------------------

Some browsers can display files such as PDF. To tell the browser to display the file instead of saving it, call the
``DownloadResponse::inline()`` method.

.. literalinclude:: response/033.php

HTTP Caching
============

Built into the HTTP specification are tools help the client (often the web browser) cache the results. Used correctly,
this can lead to a huge performance boost to your application because it will tell the client that they don't need
to contact the server at all since nothing has changed. And you can't get faster than that.

This is handled through the ``Cache-Control`` and ``ETag`` headers. This guide is not the proper place for a thorough
introduction to all of the cache headers power, but you can get a good understanding over at
`Google Developers <https://developers.google.com/web/fundamentals/performance/optimizing-content-efficiency/http-caching>`_.

By default, all response objects sent through CodeIgniter have HTTP caching turned off. The options and exact
circumstances are too varied for us to be able to create a good default other than turning it off. It's simple
to set the Cache values to what you need, through the ``setCache()`` method:

.. literalinclude:: response/010.php

The ``$options`` array simply takes an array of key/value pairs that are, with a couple of exceptions, assigned
to the ``Cache-Control`` header. You are free to set all of the options exactly as you need for your specific
situation. While most of the options are applied to the ``Cache-Control`` header, it intelligently handles
the ``etag`` and ``last-modified`` options to their appropriate header.

Class Reference
===============

.. note:: In addition to the methods listed here, this class inherits the methods from the
    :doc:`Message Class </incoming/message>`.

The methods inherited from the Message Class are:

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

.. php:namespace:: CodeIgniter\HTTP

.. php:class:: Response

    .. php:method:: getStatusCode()

        :returns: The current HTTP status code for this response
        :rtype: int

        Returns the currently status code for this response. If no status code has been set, a BadMethodCallException
        will be thrown:

        .. literalinclude:: response/014.php

    .. php:method:: setStatusCode($code[, $reason=''])

        :param int $code: The HTTP status code
        :param string $reason: An optional reason phrase.
        :returns: The current Response instance
        :rtype: ``CodeIgniter\HTTP\Response``

        Sets the HTTP status code that should be sent with this response:

        .. literalinclude:: response/015.php

        The reason phrase will be automatically generated based upon the official lists. If you need to set your own
        for a custom status code, you can pass the reason phrase as the second parameter:

        .. literalinclude:: response/016.php

    .. php:method:: getReasonPhrase()

        :returns: The current reason phrase.
        :rtype: string

        Returns the current status code for this response. If not status has been set, will return an empty string:

        .. literalinclude:: response/017.php

    .. php:method:: setDate($date)

        :param DateTime $date: A DateTime instance with the time to set for this response.
        :returns: The current response instance.
        :rtype: ``CodeIgniter\HTTP\Response``

        Sets the date used for this response. The ``$date`` argument must be an instance of ``DateTime``.

    .. php:method:: setContentType($mime[, $charset='UTF-8'])

        :param string $mime: The content type this response represents.
        :param string $charset: The character set this response uses.
        :returns: The current response instance.
        :rtype: ``CodeIgniter\HTTP\Response``

        Sets the content type this response represents:

        .. literalinclude:: response/019.php

        By default, the method sets the character set to ``UTF-8``. If you need to change this, you can
        pass the character set as the second parameter:

        .. literalinclude:: response/020.php

    .. php:method:: noCache()

        :returns: The current response instance.
        :rtype: ``CodeIgniter\HTTP\Response``

        Sets the ``Cache-Control`` header to turn off all HTTP caching. This is the default setting
        of all response messages:

        .. literalinclude:: response/021.php

    .. php:method:: setCache($options)

        :param array $options: An array of key/value cache control settings
        :returns: The current response instance.
        :rtype: ``CodeIgniter\HTTP\Response``

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
        :rtype: ``CodeIgniter\HTTP\Response``

        Sets the ``Last-Modified`` header. The ``$date`` object can be either a string or a ``DateTime``
        instance:

        .. literalinclude:: response/022.php

    .. php:method:: send(): Response

        :returns: The current response instance.
        :rtype: ``CodeIgniter\HTTP\Response``

        Tells the response to send everything back to the client. This will first send the headers,
        followed by the response body. For the main application response, you do not need to call
        this as it is handled automatically by CodeIgniter.

    .. php:method:: setCookie($name = ''[, $value = ''[, $expire = 0[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = false[, $httponly = false[, $samesite = null]]]]]]]])

        :param array|Cookie|string $name: Cookie name *or* associative array of all of the parameters available to this method *or* an instance of ``CodeIgniter\Cookie\Cookie``
        :param string $value: Cookie value
        :param int $expire: Cookie expiration time in seconds. If set to ``0`` the cookie will only last as long as the browser is open
        :param string $domain: Cookie domain
        :param string $path: Cookie path
        :param string $prefix: Cookie name prefix. If set to ``''``, the default value from **app/Config/Cookie.php** will be used
        :param bool $secure: Whether to only transfer the cookie through HTTPS. If set to ``null``, the default value from **app/Config/Cookie.php** will be used
        :param bool $httponly: Whether to only make the cookie accessible for HTTP requests (no JavaScript). If set to ``null``, the default value from **app/Config/Cookie.php** will be used
        :param string $samesite: The value for the SameSite cookie parameter. If set to ``''``, no SameSite attribute will be set on the cookie. If set to ``null``, the default value from **app/Config/Cookie.php** will be used
        :rtype: void

        .. note:: Prior to v4.2.7, the default values of ``$secure`` and ``$httponly`` were ``false``
            due to a bug, and these values from **app/Config/Cookie.php** were never used.

        Sets a cookie containing the values you specify to the Response instance.

        There are two ways to
        pass information to this method so that a cookie can be set: Array
        Method, and Discrete Parameters:

        **Array Method**

        Using this method, an associative array is passed as the first
        parameter:

        .. literalinclude:: response/023.php

        Only the ``name`` and ``value`` are required. To delete a cookie set it with the
        ``value`` blank.

        The ``expire`` is set in **seconds**, which will be added to the current
        time. Do not include the time, but rather only the number of seconds
        from *now* that you wish the cookie to be valid. If the ``expire`` is
        set to zero the cookie will only last as long as the browser is open.

        .. note:: But if the ``value`` is set to empty string and the ``expire`` is set to ``0``,
            the cookie will be deleted.

        For site-wide cookies regardless of how your site is requested, add your
        URL to the ``domain`` starting with a period, like this:
        .your-domain.com

        The ``path`` is usually not needed since the method sets a root path.

        The ``prefix`` is only needed if you need to avoid name collisions with
        other identically named cookies for your server.

        The ``secure`` flag is only needed if you want to make it a secure cookie
        by setting it to ``true``.

        The ``samesite`` value controls how cookies are shared between domains and sub-domains.
        Allowed values are ``'None'``, ``'Lax'``, ``'Strict'`` or a blank string ``''``.
        If set to blank string, default SameSite attribute will be set.

        **Discrete Parameters**

        If you prefer, you can set the cookie by passing data using individual
        parameters:

        .. literalinclude:: response/024.php

    .. php:method:: deleteCookie($name = ''[, $domain = ''[, $path = '/'[, $prefix = '']]])

        :param mixed $name: Cookie name or an array of parameters
        :param string $domain: Cookie domain
        :param string $path: Cookie path
        :param string $prefix: Cookie name prefix
        :rtype: void

        Delete an existing cookie.

        .. note:: This also just sets browser cookie for deleting the cookie.

        Only the ``name`` is required.

        The ``prefix`` is only needed if you need to avoid name collisions with
        other identically named cookies for your server.

        Provide a ``prefix`` if cookies should only be deleted for that subset.
        Provide a ``domain`` name if cookies should only be deleted for that domain.
        Provide a ``path`` name if cookies should only be deleted for that path.

        If any of the optional parameters are empty, then the same-named
        cookie will be deleted across all that apply.

        Example:

        .. literalinclude:: response/025.php

    .. php:method:: hasCookie($name = ''[, $value = null[, $prefix = '']])

        :param mixed $name: Cookie name or an array of parameters
        :param string $value: cookie value
        :param string $prefix: Cookie name prefix
        :rtype: bool

        Checks to see if the Response has a specified cookie or not.

        **Notes**

        Only the ``name`` is required. If a ``prefix`` is specified, it will be prepended to the cookie name.

        If no ``value`` is given, the method just checks for the existence of the named cookie.
        If a ``value`` is given, then the method checks that the cookie exists, and that it
        has the prescribed value.

        Example:

        .. literalinclude:: response/026.php

    .. php:method:: getCookie($name = ''[, $prefix = ''])

        :param string $name: Cookie name
        :param string $prefix: Cookie name prefix
        :rtype: ``Cookie|Cookie[]|null``

        Returns the named cookie, if found, or ``null``.
        If no ``name`` is given, returns the array of ``Cookie`` objects.

        Example:

        .. literalinclude:: response/027.php

    .. php:method:: getCookies()

        :rtype: ``Cookie[]``

        Returns all cookies currently set within the Response instance.
        These are any cookies that you have specifically specified to set during the current
        request only.
