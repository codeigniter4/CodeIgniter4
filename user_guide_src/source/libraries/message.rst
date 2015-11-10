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

