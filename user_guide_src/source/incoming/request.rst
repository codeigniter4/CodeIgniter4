Request Class
*************

The request class is an object-oriented representation of an HTTP request. This is meant to
work for both incoming, such as a request to the application from a browser, and outgoing requests,
like would be used to send a request from the application to a third-party application. This class
provides the common functionality they both need, but both cases have custom classes that extend
from the Request class to add specific functionality.

See the documentation for the :doc:`IncomingRequest Class </incoming/incomingrequest>` and
:doc:`CURLRequest Class </libraries/curlrequest>` for more usage details.

Class Reference
===============

.. php:class:: CodeIgniter\\HTTP\\Request

    .. php:method:: getIPAddress()

        :returns: The user's IP Address, if it can be detected, or null. If the IP address
                    is not a valid IP address, then will return 0.0.0.0
        :rtype:   string

        Returns the IP address for the current user. If the IP address is not valid, the method
        will return '0.0.0.0'::

            echo $request->getIPAddress();

        .. important:: This method takes into account the ``App->proxyIPs`` setting and will
            return the reported HTTP_X_FORWARDED_FOR, HTTP_CLIENT_IP, HTTP_X_CLIENT_IP, or
            HTTP_X_CLUSTER_CLIENT_IP address for the allowed IP address.

    .. php:method:: isValidIP($ip[, $which = ''])

        .. important:: This method is deprecated.

        :param    string $ip: IP address
        :param    string $which: IP protocol ('ipv4' or 'ipv6')
        :returns: true if the address is valid, false if not
        :rtype:   bool

        Takes an IP address as input and returns true or false (boolean) depending
        on whether it is valid or not.

        .. note:: The $request->getIPAddress() method above automatically validates the IP address.

            ::

                if ( ! $request->isValidIP($ip)) {
                    echo 'Not Valid';
                } else {
                    echo 'Valid';
                }

        Accepts an optional second string parameter of 'ipv4' or 'ipv6' to specify
        an IP format. The default checks for both formats.

    .. php:method:: getMethod([$upper = false])

        .. important:: Use of the ``$upper`` parameter is deprecated.

        :param bool $upper: Whether to return the request method name in upper or lower case
        :returns: HTTP request method
        :rtype: string

        Returns the ``$_SERVER['REQUEST_METHOD']``, with the option to set it
        in uppercase or lowercase.
        ::

            echo $request->getMethod(true); // Outputs: POST
            echo $request->getMethod(false); // Outputs: post
            echo $request->getMethod(); // Outputs: post

    .. php:method:: setMethod($method)

        :param string $upper: Sets the request method. Used when spoofing the request.
        :returns: HTTP request method
        :rtype: Request

    .. php:method:: getServer([$index = null[, $filter = null[, $flags = null]]])

        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns: $_SERVER item value if found, null if not
        :rtype:   mixed

        This method is identical to the ``post()``, ``get()`` and ``cookie()`` methods from the
        :doc:`IncomingRequest Class </incoming/incomingrequest>`, only it fetches getServer data (``$_SERVER``)::

            $request->getServer('some_data');

        To return an array of multiple ``$_SERVER`` values, pass all the required keys
        as an array.
        ::

            $require->getServer(['SERVER_PROTOCOL', 'REQUEST_URI']);

    .. php:method:: getEnv([$index = null[, $filter = null[, $flags = null]]])

        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :returns: $_ENV item value if found, null if not
        :rtype:   mixed

        This method is identical to the ``post()``, ``get()`` and ``cookie()`` methods from the
        :doc:`IncomingRequest Class </incoming/incomingrequest>`, only it fetches getEnv data (``$_ENV``)::

            $request->getEnv('some_data');

        To return an array of multiple ``$_ENV`` values, pass all the required keys
        as an array.
        ::

            $require->getEnv(['CI_ENVIRONMENT', 'S3_BUCKET']);

    .. php:method:: setGlobal($method, $value)

        :param    string $method: Method name
        :param    mixed  $value:  Data to be added
        :returns: HTTP request method
        :rtype:    Request

        Allows manually setting the value of PHP global, like $_GET, $_POST, etc.

    .. php:method:: fetchGlobal($method [, $index = null[, $filter = null[, $flags = null]]])

        :param    string    $method: Input filter constant
        :param    mixed     $index: Value name
        :param    int       $filter: The type of filter to apply. A list of filters can be found `here <https://www.php.net/manual/en/filter.filters.php>`__.
        :param    int|array $flags: Flags to apply. A list of flags can be found `here <https://www.php.net/manual/en/filter.filters.flags.php>`__.
        :rtype:   mixed

        Fetches one or more items from a global, like cookies, get, post, etc.
        Can optionally filter the input when you retrieve it by passing in a filter.
