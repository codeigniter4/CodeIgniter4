*****************
Working with URIs
*****************

CodeIngiter provides an object oriented solution for working with URI's in your application. Using this makes it
simple to ensure that the structure is always correct, no matter how complex the URI might be, as well as adding
relative URI to an existing one and have it resolved safely and correctly.

.. contents::
    :local:
    :depth: 2

======================
Creating URI instances
======================

Creating a URI instance is as simple as creating a new class instance::

	$uri = new \CodeIgniter\HTTP\URI();

Alternatively, you can use the ``service()`` function to return an instance for you::

	$uri = service('uri');

When you create the new instance, you can pass a full or partial URL in the constructor and it will be parsed
into its appropriate sections::

	$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');
	$uri = service('uri', 'http://www.example.com/some/path');

The Current URI
---------------

Many times, all you really want is an object representing the current URL of this request. This can be accessed
in two different ways. The first is to grab it directly from the current request object. Assuming that you're in
a controller that extends ``CodeIgniter\Controller`` you can get it like::

	$uri = $this->request->uri;

Second, you can use one of the functions available in the **url_helper**::

	$uri = current_url(true);

You must pass ``true`` as the first parameter, otherwise, it will return the string representation of the current URL.

===========
URI Strings
===========

Many times, all you really want is to get a string representation of a URI. This is easy to do by simply casting
the URI as a string::

	$uri = current_url(true);
	echo (string)$uri;  // http://example.com

If you know the pieces of the URI and just want to ensure it's all formatted correctly, you can generate a string
using the URI class' static ``createURIString()`` method::

	$uriString = URI::createURIString($scheme, $authority, $path, $query, $fragment);

	// Creates: http://exmample.com/some/path?foo=bar#first-heading
	echo URI::createURIString('http', 'example.com', 'some/path', 'foo=bar', 'first-heading');

=============
The URI Parts
=============

Once you have a URI instance, you can set or retrieve any of the various parts of the URI. This section will provide
details on what those parts are, and how to work with them.

Scheme
------

The scheme is frequently 'http' or 'https', but any scheme is supported, including 'file', 'mailto', etc.
::

    $uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

    echo $uri->getScheme(); // 'http'
    $uri->setScheme('https');

Authority
---------

Many URIs contain several elements that are collectively known as the 'authority'. This includes any user info,
the host and the port number. You can retrieve all of these pieces as one single string with the ``getAuthority()``
method, or you can manipulate the individual parts.
::

	$uri = new \CodeIgniter\HTTP\URI('ftp://user:password@example.com:21/some/path');

	echo $uri->getAuthority();  // user@example.com:21

By default, this will not display the password portion since you wouldn't want to show that to anyone. If you want
to show the password, you can use the ``showPassword()`` method. This URI instance will continue to show that password
until you turn it off again, so always make sure that you turn it off as soon as you are finished with it::

	echo $uri->getAuthority();  // user@example.com:21
	echo $uri->showPassword()->getAuthority();   // user:password@example.com:21

	// Turn password display off again.
	$uri->showPassword(false);

If you do not want to display the port, pass in ``true`` as the only parameter::

	echo $uri->getAuthority(true);  // user@example.com

.. note:: If the current port is the default port for the scheme it will never be displayed.

Userinfo
--------

The userinfo section is simply the username and password that you might see with an FTP URI. While you can get
this as part of the Authority, you can also retrieve it yourself::

	echo $uri->getUserInfo();   // user

By default, it will not display the password, but you can override that with the ``showPassword()`` method::

	echo $uri->showPassword()->getUserInfo();   // user:password
	$uri->showPassword(false);

Host
----

The host portion of the URI is typically the domain name of the URL. This can be easily set and retrieved with the
``getHost()`` and ``setHost()`` methods::

	$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

	echo $uri->getHost();   // www.example.com
	echo $uri->setHost('anotherexample.com')->getHost();    // anotherexample.com

Port
----

The port is an integer number between 0 and 65535. Each sheme has a default value associated with it.
::

	$uri = new \CodeIgniter\HTTP\URI('ftp://user:password@example.com:21/some/path');

	echo $uri->getPort();   // 21
	echo $uri->setPort(2201)->getPort(); // 2201

When using the ``setPort()`` method, the port will be checked that it is within the valid range and assigned.

Path
----

The path are all of the segments within the site itself. As expected, the ``getPath()`` and ``setPath()`` methods
can be used to manipulate it::

	$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path');

	echo $uri->getPath();   // 'some/path'
	echo $uri->setPath('another/path')->getPath();  // 'another/path'

.. note:: When setting the path this way, or any other way the class allows, it is sanitized to encode any dangerous
	characters, and remove dot segments for safety.

Query
-----

The query variables can be manipulated through the class using simple string representations. Query values can only
be set as a string currently.
::

	$uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar');

	echo $uri->getQuery();  // 'foo=bar'
	$uri->setQuery('foo=bar&bar=baz');

.. note:: Query values cannot contain fragments. An InvalidArgumentException will be thrown if it does.

You can set query values using an array::

    $uri->setQueryArray(['foo' => 'bar', 'bar' => 'baz']);

The ``setQuery()`` and ``setQueryArray()`` methods overwrite any existing query variables. You can add a value to the
query variables collection without destroying the existing query variables with the ``addQuery()`` method. The first
parameter is the name of the variable, and the second parameter is the value::

    $uri->addQuery('foo', 'bar');

**Filtering Query Values**

You can filter the query values returned by passing an options array to the ``getQuery()`` method, with either an
*only* or an *except* key::

    $uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar&bar=baz&baz=foz');

    // Returns 'foo=bar'
    echo $uri->getQuery(['only' => ['foo']);

    // Returns 'foo=bar&baz=foz'
    echo $uri->getQuery(['except' => ['bar']]);

This only changes the values returned during this one call. If you need to modify the URI's query values more permanently,
you can use the ``stripQuery()`` and ``keepQuery()`` methods to change the actual object's query variable collection::

    $uri = new \CodeIgniter\HTTP\URI('http://www.example.com?foo=bar&bar=baz&baz=foz');

    // Leaves just the 'baz' variable
    $uri->stripQuery('foo', 'bar');

    // Leaves just the 'foo' variable
    $uri->keepQuery('foo');

Fragment
--------

Fragments are the portion at the end of the URL, preceded by the pound-sign (#). In HTML URL's these are links
to an on-page anchor. Media URI's can make use of them in various other ways.
::

	$uri = new \CodeIgniter\HTTP\URI('http://www.example.com/some/path#first-heading');

	echo $uri->getFragment();   // 'first-heading'
	echo $uri->setFragment('second-heading')->getFragment();    // 'second-heading'

============
URI Segments
============

Each section of the path between the slashes is a single segment. The URI class provides a simple way to determine
what the values of the segments are. The segments start at 1 being the furthest left of the path.
::

	// URI = http://example.com/users/15/profile

	// Prints '15'
	if ($request->uri->getSegment(1) == 'users')
	{
		echo $request->uri->getSegment(2);
	}

You can get a count of the total segments::

	$total = $request->uri->getTotalSegments(); // 3

Finally, you can retrieve an array of all of the segments::

	$segments = $request->uri->getSegments();

	// $segments =
	[
		0 => 'users',
		1 => '15',
		2 => 'profile'
	]
