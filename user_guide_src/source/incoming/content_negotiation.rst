###################
Content Negotiation
###################

.. contents::
    :local:
    :depth: 2

****************************
What is Content Negotiation?
****************************

Content negotiation is a way to determine what type of content to return to the client based on what the client
can handle, and what the server can handle. This can be used to determine whether the client is wanting HTML or JSON
returned, whether the image should be returned as a JPEG or PNG, what type of compression is supported and more. This
is done by analyzing four different headers which can each support multiple value options, each with their own priority.

Trying to match this up manually can be pretty challenging. CodeIgniter provides the ``Negotiator`` class that
can handle this for you.

At it's heart Content Negotiation is simply a part of the HTTP specification that allows a single
resource to serve more than one type of content, allowing the clients to request the type of
data that works best for them.

A classic example of this is a browser that cannot display PNG files can request only GIF or
JPEG images. When the server receives the request, it looks at the available file types the client
is requesting and selects the best match from the image formats that it supports, in this case
likely choosing a JPEG image to return.

This same negotiation can happen with four types of data:

* **Media/Document Type** - this could be image format, or HTML vs. XML or JSON.
* **Character Set** - The character set the returned document should be set in. Typically is UTF-8.
* **Document Encoding** - Typically the type of compression used on the results.
* **Document Language** - For sites that support multiple languages, this helps determine which to return.

*****************
Loading the Class
*****************

You can load an instance of the class manually through the Service class:

.. literalinclude:: content_negotiation/001.php

This will grab the current request instance and automatically inject it into the Negotiator class.

This class does not need to be loaded on it's own. Instead, it can be accessed through this request's ``IncomingRequest``
instance. While you cannot access it directly this way, you can easily access all of methods through the ``negotiate()``
method:

.. literalinclude:: content_negotiation/002.php

When accessed this way, the first parameter is the type of content you're trying to find a match for, while the
second is an array of supported values.

***********
Negotiating
***********

In this section, we will discuss the 4 types of content that can be negotiated and show how that would look using
both of the methods described above to access the negotiator.

Media
=====

The first aspect to look at is handling 'media' negotiations. These are provided by the ``Accept`` header and
is one of the most complex headers available. A common example is the client telling the server what format it
wants the data in. This is especially common in APIs. For example, a client might request JSON formatted data
from an API endpoint::

    GET /foo HTTP/1.1
    Accept: application/json

The server now needs to provide a list of what type of content it can provide. In this example, the API might
be able to return data as raw HTML, JSON, or XML. This list should be provided in order of preference:

.. literalinclude:: content_negotiation/003.php

In this case, both the client and the server can agree on formatting the data as JSON so 'json' is returned from
the negotiate method. By default, if no match is found, the first element in the ``$supported`` array would be returned.
In some cases, though, you might need to enforce the format to be a strict match. If you pass ``true`` as the
final value, it will return an empty string if no match is found:

.. literalinclude:: content_negotiation/004.php

Language
========

Another common usage is to determine the language the content should be served in. If you are running only a single
language site, this obviously isn't going to make much difference, but any site that can offer up multiple translations
of content will find this useful, since the browser will typically send the preferred language along in the ``Accept-Language``
header::

    GET /foo HTTP/1.1
    Accept-Language: fr; q=1.0, en; q=0.5

In this example, the browser would prefer French, with a second choice of English. If your website supports English
and German you would do something like:

.. literalinclude:: content_negotiation/005.php

In this example, 'en' would be returned as the current language. If no match is found, it will return the first element
in the ``$supported`` array, so that should always be the preferred language.

Encoding
========

The ``Accept-Encoding`` header contains the character sets the client prefers to receive, and is used to
specify the type of compression the client supports::

    GET /foo HTTP/1.1
    Accept-Encoding: compress, gzip

Your web server will define what types of compression you can use. Some, like Apache, only support **gzip**:

.. literalinclude:: content_negotiation/006.php

See more at `Wikipedia <https://en.wikipedia.org/wiki/HTTP_compression>`_.

Character Set
=============

The desired character set is passed through the ``Accept-Charset`` header::

    GET /foo HTTP/1.1
    Accept-Charset: utf-16, utf-8

By default, if no matches are found, **utf-8** will be returned:

.. literalinclude:: content_negotiation/007.php
