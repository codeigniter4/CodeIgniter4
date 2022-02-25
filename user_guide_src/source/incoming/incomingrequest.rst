IncomingRequest Class
*********************

The IncomingRequest class provides an object-oriented representation of an HTTP request from a client, like a browser.
It extends from, and has access to all the methods of the :doc:`Request </incoming/request>` and :doc:`Message </incoming/message>`
classes, in addition to the methods listed below.

.. contents::
    :local:
    :depth: 2

Accessing the Request
---------------------

An instance of the request class already populated for you if the current class is a descendant of
``CodeIgniter\Controller`` and can be accessed as a class property:

.. literalinclude:: incomingrequest/001.php

If you are not within a controller, but still need access to the application's Request object, you can
get a copy of it through the :doc:`Services class </concepts/services>`:

.. literalinclude:: incomingrequest/002.php

It's preferable, though, to pass the request in as a dependency if the class is anything other than
the controller, where you can save it as a class property:

.. literalinclude:: incomingrequest/003.php

Determining Request Type
------------------------

A request could be of several types, including an AJAX request or a request from the command line. This can
be checked with the ``isAJAX()`` and ``isCLI()`` methods:

.. literalinclude:: incomingrequest/004.php

.. note:: The ``isAJAX()`` method depends on the ``X-Requested-With`` header,
    which in some cases is not sent by default in XHR requests via JavaScript (i.e., fetch).
    See the :doc:`AJAX Requests </general/ajax>` section on how to avoid this problem.

You can check the HTTP method that this request represents with the ``method()`` method:

.. literalinclude:: incomingrequest/005.php

By default, the method is returned as a lower-case string (i.e., 'get', 'post', etc). You can get an
uppercase version by wrapping the call in ``str_to_upper()``::

    // Returns 'GET'
    $method = str_to_upper($request->getMethod());

You can also check if the request was made through and HTTPS connection with the ``isSecure()`` method:

.. literalinclude:: incomingrequest/006.php

Retrieving Input
----------------

You can retrieve input from $_SERVER, $_GET, $_POST, and $_ENV through the Request object.
The data is not automatically filtered and returns the raw input data as passed in the request. The main
advantages to using these methods instead of accessing them directly ($_POST['something']), is that they
will return null if the item doesn't exist, and you can have the data filtered. This lets you conveniently
use data without having to test whether an item exists first. In other words, normally you might do something
like this:

.. literalinclude:: incomingrequest/007.php

With CodeIgniter's built-in methods you can simply do this:

.. literalinclude:: incomingrequest/008.php

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


.. literalinclude:: incomingrequest/009.php

By default, this will return any objects in the JSON data as objects. If you want that converted to associative
arrays, pass in ``true`` as the first parameter.

The second and third parameters match up to the ``depth`` and ``options`` arguments of the
`json_decode <https://www.php.net/manual/en/function.json-decode.php>`_ PHP function.

If the incoming request has a ``CONTENT_TYPE`` header set to "application/json", you can also use ``getVar()`` to get
the JSON stream. Using ``getVar()`` in this way will always return an object.

**Get Specific Data from JSON**

You can get a specific piece of data from a JSON stream by passing a variable name into ``getVar()`` for the
data that you want or you can use "dot" notation to dig into the JSON to get data that is not on the root level.


.. literalinclude:: incomingrequest/010.php


If you want the result to be an associative array instead of an object, you can use ``getJsonVar()`` instead and pass
true in the second parameter. This function can also be used if you can't guarantee that the incoming request will have the
correct ``CONTENT_TYPE`` header.


.. literalinclude:: incomingrequest/011.php

.. note:: See the documentation for ``dot_array_search()`` in the ``Array`` helper for more information on "dot" notation.

**Retrieving Raw data (PUT, PATCH, DELETE)**

Finally, you can grab the contents of php://input as a raw stream with ``getRawInput()``:

.. literalinclude:: incomingrequest/012.php

This will retrieve data and convert it to an array. Like this:

.. literalinclude:: incomingrequest/013.php

**Filtering Input Data**

To maintain security of your application, you will want to filter all input as you access it. You can
pass the type of filter to use as the second parameter of any of these methods. The native ``filter_var()``
function is used for the filtering. Head over to the PHP manual for a list of `valid
filter types <https://www.php.net/manual/en/filter.filters.php>`_.

Filtering a POST variable would look like this:

.. literalinclude:: incomingrequest/014.php

All of the methods mentioned above support the filter type passed in as the second parameter, with the
exception of ``getJSON()``.

Retrieving Headers
------------------

You can get access to any header that was sent with the request with the ``headers()`` method, which returns
an array of all headers, with the key as the name of the header, and the value is an instance of
``CodeIgniter\HTTP\Header``:

.. literalinclude:: incomingrequest/015.php

If you only need a single header, you can pass the name into the ``header()`` method. This will grab the
specified header object in a case-insensitive manner if it exists. If not, then it will return ``null``:

.. literalinclude:: incomingrequest/016.php

You can always use ``hasHeader()`` to see if the header existed in this request:

.. literalinclude:: incomingrequest/017.php

If you need the value of header as a string with all values on one line, you can use the ``getHeaderLine()`` method:

.. literalinclude:: incomingrequest/018.php

If you need the entire header, with the name and values in a single string, simply cast the header as a string:

.. literalinclude:: incomingrequest/019.php

The Request URL
---------------

You can retrieve a :doc:`URI </libraries/uri>` object that represents the current URI for this request through the
``$request->getUri()`` method. You can cast this object as a string to get a full URL for the current request:

.. literalinclude:: incomingrequest/020.php

The object gives you full abilities to grab any part of the request on it's own:

.. literalinclude:: incomingrequest/021.php

You can work with the current URI string (the path relative to your baseURL) using the ``getPath()`` and ``setPath()`` methods.
Note that this relative path on the shared instance of ``IncomingRequest`` is what the :doc:`URL Helper </helpers/url_helper>`
functions use, so this is a helpful way to "spoof" an incoming request for testing:

.. literalinclude:: incomingrequest/022.php

Uploaded Files
--------------

Information about all uploaded files can be retrieved through ``$request->getFiles()``, which returns an array of
``CodeIgniter\HTTP\Files\UploadedFile`` instance. This helps to ease the pain of working with uploaded files,
and uses best practices to minimize any security risks.

.. literalinclude:: incomingrequest/023.php

See :ref:`Working with Uploaded Files <uploaded-files-accessing-files>` for the details.

You can retrieve a single file uploaded on its own, based on the filename given in the HTML file input:

.. literalinclude:: incomingrequest/024.php

You can retrieve an array of same-named files uploaded as part of a
multi-file upload, based on the filename given in the HTML file input:

.. literalinclude:: incomingrequest/025.php

.. note:: The files here correspond to ``$_FILES``. Even if a user just clicks submit button of a form and does not upload any file, the file will still exist. You can check that the file was actually uploaded by the ``isValid()`` method in UploadedFile. See :ref:`verify-a-file` for more details.

Content Negotiation
-------------------

You can easily negotiate content types with the request through the ``negotiate()`` method:

.. literalinclude:: incomingrequest/026.php

See the :doc:`Content Negotiation </incoming/content_negotiation>` page for more details.

Class Reference
===============

.. note:: In addition to the methods listed here, this class inherits the methods from the
    :doc:`Request Class </incoming/request>` and the :doc:`Message Class </incoming/message>`.

The methods provided by the parent classes that are available are:

* :meth:`CodeIgniter\\HTTP\\Request::getIPAddress`
* :meth:`CodeIgniter\\HTTP\\Request::isValidIP`
* :meth:`CodeIgniter\\HTTP\\Request::getMethod`
* :meth:`CodeIgniter\\HTTP\\Request::setMethod`
* :meth:`CodeIgniter\\HTTP\\Request::getServer`
* :meth:`CodeIgniter\\HTTP\\Request::getEnv`
* :meth:`CodeIgniter\\HTTP\\Request::setGlobal`
* :meth:`CodeIgniter\\HTTP\\Request::fetchGlobal`
* :meth:`CodeIgniter\\HTTP\\Message::getBody`
* :meth:`CodeIgniter\\HTTP\\Message::setBody`
* :meth:`CodeIgniter\\HTTP\\Message::appendBody`
* :meth:`CodeIgniter\\HTTP\\Message::populateHeaders`
* :meth:`CodeIgniter\\HTTP\\Message::headers`
* :meth:`CodeIgniter\\HTTP\\Message::header`
* :meth:`CodeIgniter\\HTTP\\Message::hasHeader`
* :meth:`CodeIgniter\\HTTP\\Message::getHeaderLine`
* :meth:`CodeIgniter\\HTTP\\Message::setHeader`
* :meth:`CodeIgniter\\HTTP\\Message::removeHeader`
* :meth:`CodeIgniter\\HTTP\\Message::appendHeader`
* :meth:`CodeIgniter\\HTTP\\Message::prependHeader`
* :meth:`CodeIgniter\\HTTP\\Message::getProtocolVersion`
* :meth:`CodeIgniter\\HTTP\\Message::setProtocolVersion`

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
        :param  int     $filter: The type of filter to apply. A list of filters can be found
                        `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:   $_REQUEST if no parameters supplied, otherwise the REQUEST value if found, or null if not
        :rtype: mixed|null

        The first parameter will contain the name of the REQUEST item you are looking for:

        .. literalinclude:: incomingrequest/027.php

        The method returns null if the item you are attempting to retrieve
        does not exist.

        The second optional parameter lets you run the data through the PHP's
        filters. Pass in the desired filter type as the second parameter:

        .. literalinclude:: incomingrequest/028.php

        To return an array of all POST items call without any parameters.

        To return all POST items and pass them through the filter, set the
        first parameter to null while setting the second parameter to the filter
        you want to use:

        .. literalinclude:: incomingrequest/029.php

        To return an array of multiple POST parameters, pass all the required keys as an array:

        .. literalinclude:: incomingrequest/030.php

        Same rule applied here, to retrieve the parameters with filtering, set the second parameter to
        the filter type to apply:

        .. literalinclude:: incomingrequest/031.php

    .. php:method:: getGet([$index = null[, $filter = null[, $flags = null]]])

        :param  string  $index: The name of the variable/key to look for.
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:       $_GET if no parameters supplied, otherwise the GET value if found, or null if not
        :rtype: mixed|null

        This method is identical to ``getVar()``, only it fetches GET data.

    .. php:method:: getPost([$index = null[, $filter = null[, $flags = null]]])

        :param  string  $index: The name of the variable/key to look for.
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:       $_POST if no parameters supplied, otherwise the POST value if found, or null if not
        :rtype: mixed|null

            This method is identical to ``getVar()``, only it fetches POST data.

    .. php:method:: getPostGet([$index = null[, $filter = null[, $flags = null]]])

        :param  string  $index: The name of the variable/key to look for.
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:       $_POST if no parameters supplied, otherwise the POST value if found, or null if not
        :rtype: mixed|null

        This method works pretty much the same way as ``getPost()`` and ``getGet()``, only combined.
        It will search through both POST and GET streams for data, looking first in POST, and
        then in GET:

        .. literalinclude:: incomingrequest/032.php

    .. php:method:: getGetPost([$index = null[, $filter = null[, $flags = null]]])

        :param  string  $index: The name of the variable/key to look for.
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:       $_POST if no parameters supplied, otherwise the POST value if found, or null if not
        :rtype: mixed|null

        This method works pretty much the same way as ``getPost()`` and ``getGet()``, only combined.
        It will search through both POST and GET streams for data, looking first in GET, and
        then in POST:

        .. literalinclude:: incomingrequest/033.php

    .. php:method:: getCookie([$index = null[, $filter = null[, $flags = null]]])
        :noindex:

        :param    mixed    $index: COOKIE name
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:        $_COOKIE if no parameters supplied, otherwise the COOKIE value if found or null if not
        :rtype:    mixed

        This method is identical to ``getPost()`` and ``getGet()``, only it fetches cookie data:

        .. literalinclude:: incomingrequest/034.php

        To return an array of multiple cookie values, pass all the required keys as an array:

        .. literalinclude:: incomingrequest/035.php

        .. note:: Unlike the :doc:`Cookie Helper <../helpers/cookie_helper>`
            function :php:func:`get_cookie()`, this method does NOT prepend
            your configured ``$config['cookie_prefix']`` value.

    .. php:method:: getServer([$index = null[, $filter = null[, $flags = null]]])
        :noindex:

        :param    mixed    $index: Value name
        :param  int     $filter: The type of filter to apply. A list of filters can be
                        found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param  int     $flags: Flags to apply. A list of flags can be found
                        `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns:        $_SERVER item value if found, null if not
        :rtype:    mixed

        This method is identical to the ``getPost()``, ``getGet()`` and ``getCookie()``
        methods, only it fetches getServer data (``$_SERVER``):

        .. literalinclude:: incomingrequest/036.php

        To return an array of multiple ``$_SERVER`` values, pass all the required keys
        as an array.

        .. literalinclude:: incomingrequest/037.php

    .. php:method:: getUserAgent([$filter = null])

        :param  int $filter: The type of filter to apply. A list of filters can be
                    found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :returns:  The User Agent string, as found in the SERVER data, or null if not found.
        :rtype: mixed

        This method returns the User Agent string from the SERVER data:

        .. literalinclude:: incomingrequest/038.php

    .. php:method:: getPath()

        :returns:        The current URI path relative to ``$_SERVER['SCRIPT_NAME']``
        :rtype:    string

        This is the safest method to determine the "current URI", since ``IncomingRequest::$uri``
        may not be aware of the complete App configuration for base URLs.

    .. php:method:: setPath($path)

        :param    string    $path: The relative path to use as the current URI
        :returns:        This Incoming Request
        :rtype:    IncomingRequest

        Used mostly just for testing purposes, this allows you to set the relative path
        value for the current request instead of relying on URI detection. This will also
        update the underlying ``URI`` instance with the new path.
