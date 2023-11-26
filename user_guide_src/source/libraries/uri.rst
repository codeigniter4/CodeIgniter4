*****************
Working with URIs
*****************

CodeIgniter provides an object oriented solution for working with URI's in your application. Using this makes it
simple to ensure that the structure is always correct, no matter how complex the URI might be, as well as adding
relative URI to an existing one and have it resolved safely and correctly.

.. contents::
    :local:
    :depth: 2

======================
Creating URI instances
======================

Creating a URI instance is as simple as creating a new class instance.

When you create the new instance, you can pass a full or partial URL in the constructor and it will be parsed
into its appropriate sections:

.. literalinclude:: uri/001.php
    :lines: 2-

Alternatively, you can use the :php:func:`service()` function to return an instance for you:

.. literalinclude:: uri/003.php
    :lines: 2-

Since v4.4.0, if you don't pass a URL, it returns the current URI:

.. literalinclude:: uri/002.php
    :lines: 2-

.. note:: The above code returns the ``SiteURI`` instance, that extends the ``URI``
    class. The ``URI`` class is for general URIs, but the ``SiteURI`` class is
    for your site URIs.

The Current URI
---------------

Many times, all you really want is an object representing the current URL of this request.
You can use the :php:func:`current_url()` function available in the :doc:`../helpers/url_helper`:

.. literalinclude:: uri/004.php
    :lines: 2-

You must pass ``true`` as the first parameter, otherwise, it will return the string representation of the current URL.

This URI is based on the path (relative to your ``baseURL``) as determined by the current request object and
your settings in ``Config\App`` (``baseURL``, ``indexPage``, and ``forceGlobalSecureRequests``).

Assuming that you're in a controller that extends ``CodeIgniter\Controller``, you
can also get the current SiteURI instance:

.. literalinclude:: uri/005.php
    :lines: 2-

===========
URI Strings
===========

Many times, all you really want is to get a string representation of a URI. This is easy to do by simply casting
the URI as a string:

.. literalinclude:: uri/006.php

If you know the pieces of the URI and just want to ensure it's all formatted correctly, you can generate a string
using the URI class' static ``createURIString()`` method:

.. literalinclude:: uri/007.php

.. important:: When ``URI`` is cast to a string, it will attempt to adjust project URLs to the
    settings defined in ``Config\App``. If you need the exact, unaltered string representation
    then use ``URI::createURIString()`` instead.

=============
The URI Parts
=============

Once you have a URI instance, you can set or retrieve any of the various parts of the URI. This section will provide
details on what those parts are, and how to work with them.

Scheme
------

The scheme is frequently 'http' or 'https', but any scheme is supported, including 'file', 'mailto', etc.

.. literalinclude:: uri/008.php

Authority
---------

Many URIs contain several elements that are collectively known as the 'authority'. This includes any user info,
the host and the port number. You can retrieve all of these pieces as one single string with the ``getAuthority()``
method, or you can manipulate the individual parts.

.. literalinclude:: uri/009.php

By default, this will not display the password portion since you wouldn't want to show that to anyone. If you want
to show the password, you can use the ``showPassword()`` method. This URI instance will continue to show that password
until you turn it off again, so always make sure that you turn it off as soon as you are finished with it:

.. literalinclude:: uri/010.php

If you do not want to display the port, pass in ``true`` as the only parameter:

.. literalinclude:: uri/011.php

.. note:: If the current port is the default port for the scheme it will never be displayed.

UserInfo
--------

The userinfo section is simply the username and password that you might see with an FTP URI. While you can get
this as part of the Authority, you can also retrieve it yourself:

.. literalinclude:: uri/012.php

By default, it will not display the password, but you can override that with the ``showPassword()`` method:

.. literalinclude:: uri/013.php

Host
----

The host portion of the URI is typically the domain name of the URL. This can be easily set and retrieved with the
``getHost()`` and ``setHost()`` methods:

.. literalinclude:: uri/014.php

Port
----

The port is an integer number between 0 and 65535. Each scheme has a default value associated with it.

.. literalinclude:: uri/015.php

When using the ``setPort()`` method, the port will be checked that it is within the valid range and assigned.

Path
----

The path are all of the segments within the site itself. As expected, the ``getPath()`` and ``setPath()`` methods
can be used to manipulate it:

.. literalinclude:: uri/016.php

.. note:: When setting the path this way, or any other way the class allows, it is sanitized to encode any dangerous
    characters, and remove dot segments for safety.

.. note:: Since v4.4.0, the ``SiteURI::getRoutePath()`` method,
    returns the URI path relative to baseURL, and the ``SiteURI::getPath()``
    method always returns the full URI path with leading ``/``.

Query
-----

The query data can be manipulated through the class using simple string representations.

Getting/Setting Query
^^^^^^^^^^^^^^^^^^^^^

Query values can only
be set as a string currently.

.. literalinclude:: uri/017.php

The ``setQuery()`` method overwrite any existing query variables.

.. note:: Query values cannot contain fragments. An InvalidArgumentException will be thrown if it does.

Setting Query from Array
^^^^^^^^^^^^^^^^^^^^^^^^

You can set query values using an array:

.. literalinclude:: uri/018.php

The ``setQueryArray()`` method overwrite any existing query variables.

Adding Query Value
^^^^^^^^^^^^^^^^^^

You can add a value to the
query variables collection without destroying the existing query variables with the ``addQuery()`` method. The first
parameter is the name of the variable, and the second parameter is the value:

.. literalinclude:: uri/019.php

Filtering Query Values
^^^^^^^^^^^^^^^^^^^^^^

You can filter the query values returned by passing an options array to the ``getQuery()`` method, with either an
*only* or an *except* key:

.. literalinclude:: uri/020.php

This only changes the values returned during this one call. If you need to modify the URI's query values more permanently,

Changing Query Values
^^^^^^^^^^^^^^^^^^^^^

you can use the ``stripQuery()`` and ``keepQuery()`` methods to change the actual object's query variable collection:

.. literalinclude:: uri/021.php

.. note:: By default ``setQuery()`` and ``setQueryArray()`` methods uses native ``parse_str()`` function to prepare data.
    If you want to use more liberal rules (which allow key names to contain dots), you can use a special method
    ``useRawQueryString()`` beforehand.

Fragment
--------

Fragments are the portion at the end of the URL, preceded by the pound-sign (``#``). In HTML URLs these are links
to an on-page anchor. Media URI's can make use of them in various other ways.

.. literalinclude:: uri/022.php

============
URI Segments
============

Each section of the path between the slashes is a single segment.

.. note:: In the case of your site URI, URI Segments mean only the URI path part
    relative to the baseURL. If your baseURL contains sub folders, the values
    will be different from the current URI path.

The URI class provides a simple way to determine
what the values of the segments are. The segments start at 1 being the furthest left of the path.

.. literalinclude:: uri/023.php

You can also set a different default value for a particular segment by using the second parameter of the ``getSegment()`` method. The default is empty string.

.. literalinclude:: uri/024.php

.. note:: You can get the last +1 segment. When you try to get the last +2 or
    more segment, an exception will be thrown by default. You could prevent
    throwing exceptions with the ``setSilent()`` method.

You can get a count of the total segments:

.. literalinclude:: uri/025.php

Finally, you can retrieve an array of all of the segments:

.. literalinclude:: uri/026.php

===========================
Disable Throwing Exceptions
===========================

By default, some methods of this class may throw an exception. If you want to disable it, you can set a special flag
that will prevent throwing exceptions.

.. literalinclude:: uri/027.php
