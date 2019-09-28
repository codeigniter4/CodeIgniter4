=============
HTTP Messages
=============

The Message class provides an interface to the portions of an HTTP message that are common to both
requests and responses, including the message body, protocol version, utilities for working with
the headers, and methods for handling content negotiation.

This class is the parent class that both the :doc:`Request Class </incoming/request>` and the
:doc:`Response Class </outgoing/response>` extend from. As such, some methods, such as the content
negotiation methods, may apply only to a request or response, and not the other one, but they have
been included here to keep the header methods together.

What is Content Negotiation?
============================

At it's heart Content Negotiation is simply a part of the HTTP specification that allows a single
resource to serve more than one type of content, allowing the clients to request the type of
data that works best for them.

A classic example of this is a browser that cannot display PNG files can request only GIF or
JPEG images. When the getServer receives the request, it looks at the available file types the client
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

		Returns the current message body, if any has been set. If not body exists, returns null::

			echo $message->body();

	.. php:method:: setBody([$str])

	   :param  string  $str: The body of the message.
	   :returns: the Message instance to allow methods to be chained together.
	   :rtype: CodeIgniter\\HTTP\\Message instance.

		Sets the body of the current request.

	.. php:method:: populateHeaders()

		:returns: void

		Scans and parses the headers found in the SERVER data and stores it for later access.
		This is used by the :doc:`IncomingRequest Class </incoming/incomingrequest>` to make
		the current request's headers available.

                The headers are any SERVER data that starts with ``HTTP_``, like ``HTTP_HOST``. Each message
		is converted from it's standard uppercase and underscore format to a ucwords and dash format.
		The preceding ``HTTP_`` is removed from the string. So ``HTTP_ACCEPT_LANGUAGE`` becomes
		``Accept-Language``.

	.. php:method:: getHeaders()

		:returns: An array of all of the headers found.
		:rtype: array

		Returns an array of all headers found or previously set.

	.. php:method:: getHeader([$name[, $filter = null]])

		:param  string  $name: The name of the header you want to retrieve the value of.
		:param  int  $filter: The type of filter to apply. A list of filters can be found `here <http://php.net/manual/en/filter.filters.php>`_.
		:returns: The current value of the header. If the header has multiple values, they will be returned as an array.
		:rtype: string|array|null

		Allows you to retrieve the current value of a single message header. ``$name`` is the case-insensitive header name.
		While the header is converted internally as described above, you can access the header with any type of case::

			// These are all the same:
			$message->getHeader('HOST');
			$message->getHeader('Host');
			$message->getHeader('host');

		If the header has multiple values, the values will return as an array of values. You can use the ``headerLine()``
		method to retrieve the values as a string::

			echo $message->getHeader('Accept-Language');

			// Outputs something like:
			[
				'en',
				'en-US'
			]

		You can filter the header by passing a filter value in as the second parameter::

			$message->getHeader('Document-URI', FILTER_SANITIZE_URL);

	.. php:method:: headerLine($name)

		:param  string $name: The name of the header to retrieve.
		:returns: A string representing the header value.
		:rtype: string

		Returns the value(s) of the header as a string. This method allows you to easily get a string representation
		of the header values when the header has multiple values. The values are appropriately joined::

			echo $message->headerLine('Accept-Language');

			// Outputs:
			en, en-US

	.. php:method:: setHeader([$name[, $value]])
                :noindex:

		:param string $name: The name of the header to set the value for.
		:param mixed  $value: The value to set the header to.
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Sets the value of a single header. ``$name`` is the case-insensitive name of the header. If the header
		doesn't already exist in the collection, it will be created. The ``$value`` can be either a string
		or an array of strings::

			$message->setHeader('Host', 'codeigniter.com');

	.. php:method:: removeHeader([$name])

		:param string $name: The name of the header to remove.
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Removes the header from the Message. ``$name`` is the case-insensitive name of the header::

			$message->remove('Host');

	.. php:method:: appendHeader([$name[, $value]]))

		:param string $name:  The name of the header to modify
		:param mixed  $value: The value to add to the header.
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Adds a value to an existing header. The header must already be an array of values instead of a single string.
		If it is a string then a LogicException will be thrown.
		::

			$message->appendHeader('Accept-Language', 'en-US; q=0.8');

	.. php:method:: protocolVersion()

		:returns: The current HTTP protocol version
		:rtype: string

		Returns the message's current HTTP protocol. If none has been set, will return ``null``. Acceptable values
		are ``1.0`` and ``1.1``.

	.. php:method:: setProtocolVersion($version)

		:param string $version: The HTTP protocol version
		:returns: The current message instance
		:rtype: CodeIgniter\\HTTP\\Message

		Sets the HTTP protocol version this Message uses. Valid values are ``1.0`` or ``1.1``::

			$message->setProtocolVersion('1.1');

	.. php:method:: negotiateMedia($supported[, $strictMatch=false])

		:param array $supported: An array of media types the application supports
		:param bool $strictMatch: Whether it should force an exact match to happen.
		:returns: The supported media type that best matches what is requested.
		:rtype: string

		Parses the ``Accept`` header and compares with the application's supported media types to determine
		the best match. Returns the appropriate media type. The first parameter is an array of application supported
		media types that should be compared against header values::

			$supported = [
				'image/png',
				'image/jpg',
				'image/gif'
			];
			$imageType = $message->negotiateMedia($supported);

		The ``$supported`` array should be structured so that the application's preferred format is the first in the
		array, with the rest following in descending order of priority. If no match can be made between the header
		values and the supported values, the first element of the array will be returned.

		Per the `RFC <http://tools.ietf.org/html/rfc7231#section-5.3>`_ the match has the option of returning a
		default value, like this method does, or to return an empty string. If you need to have an exact match and
		would like an empty string returned instead, pass ``true`` as the second parameter::

			// Returns empty string if no match.
			$imageType = $message->negotiateMedia($supported, true);

		The matching process takes into account the priorities and specificity of the RFC. This means that the more
		specific header values will have a higher order of precedence, unless modified by a different ``q`` value.
		For more details, please read the `appropriate section of the RFC <http://tools.ietf.org/html/rfc7231#section-5.3.2>`_.

	.. php:method:: negotiateCharset($supported)

		:param array $supported: An array of character sets the application supports.
		:returns: The supported character set that best matches what is required.
		:rtype: string

		This is used identically to the ``negotiateMedia()`` method, except that it matches against the ``Accept-Charset``
		header string::

			$supported = [
				'utf-8',
				'iso-8895-9'
			];
			$charset = $message->negotiateCharset($supported);

		If no match is found, the system will default to ``utf-8``.

	.. php:method:: negotiateEncoding($supported)

		:param array $supported: An array of character encodings the application supports.
		:returns: The supported character set that best matches what is required.
		:rtype: string

		Determines the best match between the application-supported values and the ``Accept-Encoding`` header value.
		If no match is found, will return the first element of the ``$supported`` array::

			$supported = [
				'gzip',
				'compress'
			];
			$encoding = $message->negotiateEncoding($supported);

	.. php:method:: negotiateLanguage($supported)

		:param array $supported: An array of languages the application supports.
		:returns: The supported language that best matches what is required.
		:rtype: string

		Determines the best match between the application-supported languages and the ``Accept-Language`` header value.
		If no match is found, will return the first element of the ``$supported`` array::

			$supported = [
				'en',
				'fr',
				'x-pig-latin'
			];
			$language = $message->negotiateLanguage($supported);

		More information about the language tags is available in `RFC 1766 <https://www.ietf.org/rfc/rfc1766.txt>`_.

