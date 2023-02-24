#############
Request Class
#############

The request class is an object-oriented representation of an HTTP request. This is meant to
work for both incoming, such as a request to the application from a browser, and outgoing requests,
like would be used to send a request from the application to a third-party application.

This class
provides the common functionality they both need, but both cases have custom classes that extend
from the Request class to add specific functionality. In practice, you will need to use these classes.

See the documentation for the :doc:`IncomingRequest Class <./incomingrequest>` and
:doc:`CURLRequest Class <../libraries/curlrequest>` for more usage details.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\HTTP

.. php:class:: Request

    .. php:method:: getIPAddress()

        :returns: The user's IP Address, if it can be detected. If the IP address
                    is not a valid IP address, then will return ``0.0.0.0``.
        :rtype:   string

        Returns the IP address for the current user. If the IP address is not valid, the method
        will return ``0.0.0.0``:

        .. literalinclude:: request/001.php

        .. important:: This method takes into account the ``Config\App::$proxyIPs`` setting and will
            return the reported client IP address by the HTTP header for the allowed IP address.

    .. php:method:: isValidIP($ip[, $which = ''])

        .. deprecated:: 4.0.5
           Use :doc:`../libraries/validation` instead.

        .. important:: This method is deprecated. It will be removed in future releases.

        :param    string $ip: IP address
        :param    string $which: IP protocol (``ipv4`` or ``ipv6``)
        :returns: true if the address is valid, false if not
        :rtype:   bool

        Takes an IP address as input and returns true or false (boolean) depending
        on whether it is valid or not.

        .. note:: The $request->getIPAddress() method above automatically validates the IP address.

            .. literalinclude:: request/002.php

        Accepts an optional second string parameter of ``ipv4`` or ``ipv6`` to specify
        an IP format. The default checks for both formats.

    .. php:method:: getMethod([$upper = false])

        .. important:: Use of the ``$upper`` parameter is deprecated. It will be removed in future releases.

        :param bool $upper: Whether to return the request method name in upper or lower case
        :returns: HTTP request method
        :rtype: string

        Returns the ``$_SERVER['REQUEST_METHOD']``, with the option to set it
        in uppercase or lowercase.

        .. literalinclude:: request/003.php

    .. php:method:: setMethod($method)

        .. deprecated:: 4.0.5
           Use :php:meth:`CodeIgniter\\HTTP\\Request::withMethod()` instead.

        :param string $method: Sets the request method. Used when spoofing the request.
        :returns: This request
        :rtype: Request

    .. php:method:: withMethod($method)

        .. versionadded:: 4.0.5

        :param string $method: Sets the request method.
        :returns: New request instance
        :rtype: Request

    .. php:method:: getServer([$index = null[, $filter = null[, $flags = null]]])

        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns: ``$_SERVER`` item value if found, null if not
        :rtype:   mixed

        This method is identical to the ``getPost()``, ``getGet()`` and ``getCookie()`` methods from the
        :doc:`IncomingRequest Class <./incomingrequest>`, only it fetches server data (``$_SERVER``):

        .. literalinclude:: request/004.php

        To return an array of multiple ``$_SERVER`` values, pass all the required keys
        as an array.

        .. literalinclude:: request/005.php

    .. php:method:: getEnv([$index = null[, $filter = null[, $flags = null]]])

        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns: ``$_ENV`` item value if found, null if not
        :rtype:   mixed

        This method is identical to the ``getPost()``, ``getGet()`` and ``getCookie()`` methods from the
        :doc:`IncomingRequest Class <./incomingrequest>`, only it fetches env data (``$_ENV``):

        .. literalinclude:: request/006.php

        To return an array of multiple ``$_ENV`` values, pass all the required keys
        as an array.

        .. literalinclude:: request/007.php

    .. php:method:: setGlobal($method, $value)

        :param    string $method: Method name
        :param    mixed  $value:  Data to be added
        :returns: This request
        :rtype:   Request

        Allows manually setting the value of PHP global, like ``$_GET``, ``$_POST``, etc.

    .. php:method:: fetchGlobal($method [, $index = null[, $filter = null[, $flags = null]]])

        :param    string    $method: Input filter constant
        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found in `PHP manual <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :rtype:   mixed

        Fetches one or more items from a global, like cookies, get, post, etc.
        Can optionally filter the input when you retrieve it by passing in a filter.
