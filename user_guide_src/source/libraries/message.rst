=============
HTTP Messages
=============

The Message class provides an interface to the portions of an HTTP message that are common to both
requests and responses, including the message body, protocol version, utilities for working with
the headers, and methods for handling content negotiation.

This class is the parent class that both the :doc:`Request Class </libraries/request>` and the
:doc:`Response Class </libraries/response>` extend from. As such, some methods, such as the content
negotiation methods, may apply only to a request or response, and not the other one, but they have
been included here to keep the header methods together.

What is Content Negotiation?
============================

At it's heart Content Negotiation is simply a part of the HTTP specification that allows a single
resource to serve more than one type of content, allowing the clients to request the type of
data that works best for them.

A classic example of this is a browser than cannot display PNG files can request only GIF or
JPEG images. When the server receives the request, it looks at the available file types the client
is requesting and selects the best match from the image formats that it supports, in this case
likely choosing a JPEG image to return.

This same negotiation can happen with four types of data:

* **Media/Document Type** - this could be image format, or HTML vs. XML or JSON.
* **Character Set** - The character set the returned document should be set in. Typically is UTF-8.
* **Document Encoding** - Typically the type of compression used on the results.
* **Document Language** - For sites that support multiple languages, this helps determine which to return.

***************
Class Reference
***************

.. php:class:: CodeIgniter\\HTTP\\Message

	.. php:method:: body()

		:returns: The current message body
		:rtype: string

		Returns the current message body, if any has been set. If not body exists, returns null.::

			echo $message->body();

	.. php:method:: setBody([$str])

	   :param  string  $str: The body of the message.
	   :returns: the Message instance to allow methods to be chained together.
	   :rtype: CodeIgniter\\HTTP\\Message instance.

		Sets the body of the current request.

	.. php:method:: populateHeaders()

		:returns: void

		Scans and parses the headers found in the SERVER data and stores it for later access.
		This is used by the :doc:`IncomingRequest Class </libraries/incomingrequest>` to make
		 the current request's headers available.

		 The headers are any SERVER data that starts with ``HTTP_``, like ``HTTP_HOST``. Each message
		 is converted from it's standard uppercase and underscore format to a ucwords and dash format.
		 The preceeding ``HTTP_`` is removed from the string. So ``HTTP_ACCEPT_LANGUAGE`` becomes
		 ``Accept-Language``.

	.. php:method:: headers()

		:returns: An array of all of the headers found.
		:rtype: array

		Returns an array of all headers found or previously set.

	.. php:method:: header([$name[, $filter = null]])

		:param  string  $name: The name of the header you want to retrieve the value of.
		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns: The current value of the header. If the header has multiple values, they will be returned as an array.
		:rtype: string|array|null

		Allows you to retrieve the current value of a single message header. ``$name`` is the case-insensitive header name.
		While the header is converted internally as described above, you can access the header with any type of case.::

			// These are all the same:
			$message->header('HOST');
			$message->header('Host');
			$message->header('host');

		If the header has multiple values, the values will return as an array of values. You can use the ``headerLine()``
		method to retrieve the values as a string.::

			echo $message->header('Accept-Language');

			// Outputs something like:
			[
				'en',
				'en-US'
			]

		You can filter the header by passing a filter value in as the second parameter::

			$message->header('Document-URI', FILTER_SANITIZE_URL);

	.. php:method:: headerLine($name)

		:param  string $name: The name of the header to retrieve.
		:returns: A string representing the header value.
		:rtype: string

		Returns the value(s) of the header as a string. This method allows you to easily get a string representation
		of the header values when the header has multiple values. The values are appropriately joined::

			echo $message->headerLine('Host');

			// Outputs:
			en, en-US

	.. php:method:: setHeader([$name[, $value]])

		:param string $name: The name of the header to set the value for.
		:param mixed  $value: The value to set the header to.
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Sets the value of a single header. ``$name`` is the case-insensitive name of the header. If the header
		doesn't already exist in the collection, it will be created. The ``$value`` can be either a string
		or an array of strings.::

			$message->setHeader('Host', 'codeigniter.com');

	.. php:method:: removeHeader([$name])

		:param string $name: The name of the header to remove.
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Removes the header from the Message. ``$name`` is the case-insensitive name of the header::

			$message->remove('Host');

