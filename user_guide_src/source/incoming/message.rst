#############
HTTP Messages
#############

The Message class provides an interface to the portions of an HTTP message that are common to both
requests and responses, including the message body, protocol version, utilities for working with
the headers, and methods for handling content negotiation.

This class is the parent class that both the :doc:`Request Class <../incoming/request>` and the
:doc:`Response Class <../outgoing/response>` extend from.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\HTTP

.. php:class:: Message

    .. php:method:: getBody()

        :returns: The current message body
        :rtype: mixed

        Returns the current message body, if any has been set. If not body exists, returns null:

        .. literalinclude:: message/001.php

    .. php:method:: setBody($data)

        :param  mixed  $data: The body of the message.
        :returns: the Message|Response instance to allow methods to be chained together.
        :rtype: CodeIgniter\\HTTP\\Message|CodeIgniter\\HTTP\\Response

        Sets the body of the current request.

    .. php:method:: appendBody($data)

        :param  mixed  $data: The body of the message.
        :returns: the Message|Response instance to allow methods to be chained together.
        :rtype: CodeIgniter\\HTTP\\Message|CodeIgniter\\HTTP\\Response

        Appends data to the body of the current request.

    .. php:method:: populateHeaders()

        :returns: void

        Scans and parses the headers found in the SERVER data and stores it for later access.
        This is used by the :doc:`IncomingRequest Class <../incoming/incomingrequest>` to make
        the current request's headers available.

        The headers are any SERVER data that starts with ``HTTP_``, like ``HTTP_HOST``. Each message
        is converted from it's standard uppercase and underscore format to a ucwords and dash format.
        The preceding ``HTTP_`` is removed from the string. So ``HTTP_ACCEPT_LANGUAGE`` becomes
        ``Accept-Language``.

    .. php:method:: headers()

        :returns: An array of all of the headers found.
        :rtype: array

        Returns an array of all headers found or previously set.

    .. php:method:: header($name)

        :param  string  $name: The name of the header you want to retrieve the value of.
        :returns: Returns a single header object. If multiple headers with the same name exist, then will return an array of header objects.
        :rtype: \CodeIgniter\\HTTP\\Header|array

        Allows you to retrieve the current value of a single message header. ``$name`` is the case-insensitive header name.
        While the header is converted internally as described above, you can access the header with any type of case:

        .. literalinclude:: message/002.php

        If the header has multiple values, ``getValue()`` will return as an array of values. You can use the ``getValueLine()``
        method to retrieve the values as a string:

        .. literalinclude:: message/003.php

        You can filter the header by passing a filter value in as the second parameter:

        .. literalinclude:: message/004.php

    .. php:method:: hasHeader($name)

        :param  string  $name: The name of the header you want to see if it exists.
        :returns: Returns true if it exists, false otherwise.
        :rtype: bool

    .. php:method:: getHeaderLine($name)

        :param  string $name: The name of the header to retrieve.
        :returns: A string representing the header value.
        :rtype: string

        Returns the value(s) of the header as a string. This method allows you to easily get a string representation
        of the header values when the header has multiple values. The values are appropriately joined:

        .. literalinclude:: message/005.php

    .. php:method:: setHeader($name, $value)

        :param string $name: The name of the header to set the value for.
        :param mixed  $value: The value to set the header to.
        :returns: The current Message|Response instance
        :rtype: CodeIgniter\\HTTP\\Message|CodeIgniter\\HTTP\\Response

        Sets the value of a single header. ``$name`` is the case-insensitive name of the header. If the header
        doesn't already exist in the collection, it will be created. The ``$value`` can be either a string
        or an array of strings:

        .. literalinclude:: message/006.php

    .. php:method:: removeHeader($name)

        :param string $name: The name of the header to remove.
        :returns: The current message instance
        :rtype: CodeIgniter\\HTTP\\Message

        Removes the header from the Message. ``$name`` is the case-insensitive name of the header:

        .. literalinclude:: message/007.php

    .. php:method:: appendHeader($name, $value)

        :param string $name: The name of the header to modify
        :param string  $value: The value to add to the header.
        :returns: The current message instance
        :rtype: CodeIgniter\\HTTP\\Message

        Adds a value to an existing header. The header must already be an array of values instead of a single string.
        If it is a string then a LogicException will be thrown.

        .. literalinclude:: message/008.php

    .. php:method:: prependHeader($name, $value)

        :param string $name: The name of the header to modify
        :param string  $value: The value to prepend to the header.
        :returns: The current message instance
        :rtype: CodeIgniter\\HTTP\\Message

        Prepends a value to an existing header. The header must already be an array of values instead of a single string.
        If it is a string then a LogicException will be thrown.

        .. literalinclude:: message/009.php

    .. php:method:: getProtocolVersion()

        :returns: The current HTTP protocol version
        :rtype: string

        Returns the message's current HTTP protocol. If none has been set, will
        return ``1.1``.

    .. php:method:: setProtocolVersion($version)

        :param string $version: The HTTP protocol version
        :returns: The current message instance
        :rtype: CodeIgniter\\HTTP\\Message

        Sets the HTTP protocol version this Message uses. Valid values are
        ``1.0``, ``1.1``, ``2.0`` and ``3.0``:

        .. literalinclude:: message/010.php
