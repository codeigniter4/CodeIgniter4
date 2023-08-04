#######
Cookies
#######

An **HTTP cookie** (web cookie, browser cookie) is a small piece of data that a server
sends to the user's web browser. The browser may store it and send it back with later
requests to the same server. Typically, it's used to tell if two requests came from
the same browser - keeping a user logged-in, for example.
It remembers stateful information for the stateless HTTP protocol.

Cookies are mainly used for three purposes:

- **Session management**: Logins, shopping carts, game scores, or anything else the server should remember
- **Personalization**: User preferences, themes, and other settings
- **Tracking**: Recording and analyzing user behavior

To help you efficiently send cookies to browsers,
CodeIgniter provides the ``CodeIgniter\Cookie\Cookie`` class to abstract the
cookie interaction.

.. contents::
    :local:
    :depth: 2

****************
Creating Cookies
****************

There are currently four (4) ways to create a new ``Cookie`` value object.

.. literalinclude:: cookies/001.php

When constructing the ``Cookie`` object, only the ``name`` attribute is required. All other else are optional.
If the optional attributes are not modified, their values will be filled up by the default values saved in
the ``Cookie`` class.

Overriding Defaults
===================

To override the defaults currently stored in the class, you can pass a ``Config\Cookie``
instance or an array of defaults to the static ``Cookie::setDefaults()`` method.

.. literalinclude:: cookies/002.php

Passing the ``Config\Cookie`` instance or an array to ``Cookie::setDefaults()`` will effectively
overwrite your defaults and will persist until new defaults are passed.

Changing Defaults for a Limited Time
------------------------------------

If you do not want this
behavior but only want to change defaults for a limited time, you can take advantage of
``Cookie::setDefaults()`` return which returns the old defaults array.

.. literalinclude:: cookies/003.php

*****************************
Accessing Cookie's Attributes
*****************************

Once instantiated, you can easily access a ``Cookie``'s attribute by using one of its getter methods.

.. literalinclude:: cookies/004.php

*****************
Immutable Cookies
*****************

A new ``Cookie`` instance is an immutable value object representation of an HTTP cookie. Being immutable,
modifying any of the instance's attributes will not affect the original instance. The modification **always**
returns a new instance. You need to retain this new instance in order to use it.

.. literalinclude:: cookies/005.php

********************************
Validating a Cookie's Attributes
********************************

An HTTP cookie is regulated by several specifications that need to be followed in order to be
accepted by browsers. Thus, when creating or modifying certain attributes of the ``Cookie``,
these are validated in order to check if these follow the specifications.

A ``CookieException`` is thrown if violations were reported.

Validating the Name Attribute
=============================

A cookie name can be any US-ASCII character, except for the following:

- control characters;
- spaces or tabs;
- separator characters, such as ``( ) < > @ , ; : \ " / [ ] ? = { }``

If setting the ``$raw`` parameter to ``true`` this validation will be strictly made. This is because
PHP's `setcookie() <https://www.php.net/manual/en/function.setcookie.php>`_
and `setrawcookie() <https://www.php.net/manual/en/function.setrawcookie.php>`_
will reject cookies with invalid names. Additionally, cookie
names cannot be an empty string.

Validating the Prefix Attribute
===============================

When using the ``__Secure-`` prefix, cookies must be set with the ``$secure`` flag set to ``true``. If
using the ``__Host-`` prefix, cookies must exhibit the following:

- ``$secure`` flag set to ``true``
- ``$domain`` is empty
- ``$path`` must be ``/``

Validating the SameSite Attribute
=================================

The SameSite attribute only accepts three (3) values:

- **Lax**: Cookies are not sent on normal cross-site subrequests (for example to load images or frames into a third party site), but are sent when a user is navigating to the origin site (*i.e.* when following a link).
- **Strict**: Cookies will only be sent in a first-party context and not be sent along with requests initiated by third party websites.
- **None**: Cookies will be sent in all contexts, *i.e.* in responses to both first-party and cross-origin requests.

CodeIgniter, however, allows you to set the SameSite attribute to an empty string. When an empty string is
provided, the default SameSite setting saved in the ``Cookie`` class is used. You can change the default SameSite
by using the ``Cookie::setDefaults()`` as discussed above.

Recent cookie specifications have changed such that modern browsers are being required to give a default SameSite
if nothing was provided. This default is ``Lax``. If you have set the SameSite to be an empty string and your
default SameSite is also an empty string, your cookie will be given the ``Lax`` value.

If the SameSite is set to ``None`` you need to make sure that ``Secure`` is also set to ``true``.

When writing the SameSite attribute, the ``Cookie`` class accepts any of the values case-insensitively. You can
also take advantage of the class's constants to make it not a hassle.

.. literalinclude:: cookies/006.php


***************
Sending Cookies
***************

Set the ``Cookie`` objects in the ``CookieStore`` of the Response object, and
the framework will automatically send the cookies.

Use :php:meth:`CodeIgniter\\HTTP\\Response::setCookie()` to set:

.. literalinclude:: cookies/017.php

You can also use the :php:func:`set_cookie()` helper function:

.. literalinclude:: cookies/018.php


**********************
Using the Cookie Store
**********************

.. note:: Normally, there is no need to use CookieStore directly.

The ``CookieStore`` class represents an immutable collection of ``Cookie`` objects.

Getting the Store from Response
===============================

The ``CookieStore``
instance can be accessed from the current ``Response`` object.

.. literalinclude:: cookies/007.php

Creating CookieStore
====================

CodeIgniter provides three (3) other ways to create a new instance of the ``CookieStore``.

.. literalinclude:: cookies/008.php

.. note:: When using the global :php:func:`cookies()` function, the passed ``Cookie`` array will only be considered
    if the second argument, ``$getGlobal``, is set to ``false``.

Checking Cookies in Store
=========================

To check whether a ``Cookie`` object exists in the ``CookieStore`` instance, you can use several ways:

.. literalinclude:: cookies/009.php

Getting Cookies in Store
========================

Retrieving a ``Cookie`` instance in a cookie collection is very easy:

.. literalinclude:: cookies/010.php

When getting a ``Cookie`` instance directly from a ``CookieStore``, an invalid name
will throw a ``CookieException``.

.. literalinclude:: cookies/011.php

When getting a ``Cookie`` instance from the current ``Response``'s cookie collection,
an invalid name will just return ``null``.

.. literalinclude:: cookies/012.php

If no arguments are supplied in when getting cookies from the ``Response``, all ``Cookie`` objects
in store will be displayed.

.. literalinclude:: cookies/013.php

.. note:: The helper function :php:func:`get_cookie()` gets the cookie from the current ``Request`` object, not
    from ``Response``. This function checks the ``$_COOKIE`` array if that cookie is set and fetches it
    right away.

Adding/Removing Cookies in Store
================================

As previously mentioned, ``CookieStore`` objects are immutable. You need to save the modified instance
in order to work on it. The original instance is left unchanged.

.. literalinclude:: cookies/014.php

.. note:: Removing a cookie from the store **DOES NOT** delete it from the browser.
    If you intend to delete a cookie *from the browser*, you must put an empty value
    cookie with the same name to the store.

When interacting with the cookies in store in the current ``Response`` object, you can safely add or delete
cookies without worrying the immutable nature of the cookie collection. The ``Response`` object will replace
the instance with the modified instance.

.. literalinclude:: cookies/015.php

Dispatching Cookies in Store
============================

.. deprecated:: 4.1.6

.. important:: This method is deprecated. It will be removed in future releases.

More often than not, you do not need to concern yourself in manually sending cookies. CodeIgniter will do this
for you. However, if you really need to manually send cookies, you can use the ``dispatch`` method. Just like
in sending other headers, you need to make sure the headers are not yet sent by checking the value
of ``headers_sent()``.

.. literalinclude:: cookies/016.php

**********************
Cookie Personalization
**********************

Sane defaults are already in place inside the ``Cookie`` class to ensure the smooth creation of cookie
objects. However, you may wish to define your own settings by changing the following settings in the
``Config\Cookie`` class in **app/Config/Cookie.php** file.

==================== ===================================== ========= =====================================================
Setting              Options/ Types                        Default   Description
==================== ===================================== ========= =====================================================
**$prefix**          ``string``                            ``''``    Prefix to prepend to the cookie name.
**$expires**         ``DateTimeInterface|string|int``      ``0``     The expires timestamp.
**$path**            ``string``                            ``/``     The path property of the cookie.
**$domain**          ``string``                            ``''``    The domain property of the cookie.with trailing slash.
**$secure**          ``true/false``                        ``false`` If to be sent over secure HTTPS.
**$httponly**        ``true/false``                        ``true``  If not accessible to JavaScript.
**$samesite**        ``Lax|None|Strict|lax|none|strict''`` ``Lax``   The SameSite attribute.
**$raw**             ``true/false``                        ``false`` If to be dispatched using ``setrawcookie()``.
==================== ===================================== ========= =====================================================

In runtime, you can manually supply a new default using the ``Cookie::setDefaults()`` method.

***************
Class Reference
***************

.. php:namespace:: CodeIgniter\Cookie

.. php:class:: Cookie

    .. php:staticmethod:: setDefaults([$config = []])

        :param \\Config\\Cookie|array $config: The configuration array or instance
        :rtype: array<string, mixed>
        :returns: The old defaults

        Set the default attributes to a Cookie instance by injecting the values from the ``Config\Cookie`` config or an array.

    .. php:staticmethod:: fromHeaderString(string $header[, bool $raw = false])

        :param string $header: The ``Set-Cookie`` header string
        :param bool $raw: Whether this cookie is not to be URL encoded and sent via ``setrawcookie()``
        :rtype: ``Cookie``
        :returns: ``Cookie`` instance
        :throws: ``CookieException``

        Create a new Cookie instance from a ``Set-Cookie`` header.

    .. php:method:: __construct(string $name[, string $value = ''[, array $options = []]])

        :param string $name: The cookie name
        :param string $value: The cookie value
        :param array $options: The cookie options
        :rtype: ``Cookie``
        :returns: ``Cookie`` instance
        :throws: ``CookieException``

        Construct a new Cookie instance.

    .. php:method:: getId()

        :rtype: string
        :returns: The ID used in indexing in the cookie collection.

    .. php:method:: getPrefix(): string
    .. php:method:: getName(): string
    .. php:method:: getPrefixedName(): string
    .. php:method:: getValue(): string
    .. php:method:: getExpiresTimestamp(): int
    .. php:method:: getExpiresString(): string
    .. php:method:: isExpired(): bool
    .. php:method:: getMaxAge(): int
    .. php:method:: getDomain(): string
    .. php:method:: getPath(): string
    .. php:method:: isSecure(): bool
    .. php:method:: isHTTPOnly(): bool
    .. php:method:: getSameSite(): string
    .. php:method:: isRaw(): bool
    .. php:method:: getOptions(): array

    .. php:method:: withRaw([bool $raw = true])

        :param bool $raw:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with URL encoding option updated.

    .. php:method:: withPrefix([string $prefix = ''])

        :param string $prefix:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new prefix.

    .. php:method:: withName(string $name)

        :param string $name:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new name.

    .. php:method:: withValue(string $value)

        :param string $value:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new value.

    .. php:method:: withExpires($expires)

        :param DateTimeInterface|string|int $expires:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new cookie expires time.

    .. php:method:: withExpired()

        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie that will expire from the browser.

    .. php:method:: withNeverExpiring()

        .. deprecated:: 4.2.6

        .. important:: This method is deprecated. It will be removed in future releases.

        :param string $name:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie that will virtually never expire.

    .. php:method:: withDomain(?string $domain)

        :param string|null $domain:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new domain.

    .. php:method:: withPath(?string $path)

        :param string|null $path:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new path.

    .. php:method:: withSecure([bool $secure = true])

        :param bool $secure:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new "Secure" attribute.

    .. php:method:: withHTTPOnly([bool $httponly = true])

        :param bool $httponly:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new "HttpOnly" attribute.

    .. php:method:: withSameSite(string $samesite)

        :param string $samesite:
        :rtype: ``Cookie``
        :returns: new ``Cookie`` instance

        Creates a new Cookie with new "SameSite" attribute.

    .. php:method:: toHeaderString()

        :rtype: string
        :returns: Returns the string representation that can be passed as a header string.

    .. php:method:: toArray()

        :rtype: array
        :returns: Returns the array representation of the Cookie instance.

.. php:class:: CookieStore

    .. php:staticmethod:: fromCookieHeaders(array $headers[, bool $raw = false])

        :param array $header: Array of ``Set-Cookie`` headers
        :param bool $raw: Whether not to use URL encoding
        :rtype: ``CookieStore``
        :returns: ``CookieStore`` instance
        :throws: ``CookieException``

        Creates a CookieStore from an array of ``Set-Cookie`` headers.

    .. php:method:: __construct(array $cookies)

        :param array $cookies: Array of ``Cookie`` objects
        :rtype: ``CookieStore``
        :returns: ``CookieStore`` instance
        :throws: ``CookieException``

    .. php:method:: has(string $name[, string $prefix = ''[, ?string $value = null]]): bool

        :param string $name: Cookie name
        :param string $prefix: Cookie prefix
        :param string|null $value: Cookie value
        :rtype: bool
        :returns: Checks if a ``Cookie`` object identified by name and prefix is present in the collection.

    .. php:method:: get(string $name[, string $prefix = '']): Cookie

        :param string $name: Cookie name
        :param string $prefix: Cookie prefix
        :rtype: ``Cookie``
        :returns: Retrieves an instance of Cookie identified by a name and prefix.
        :throws: ``CookieException``

    .. php:method:: put(Cookie $cookie): CookieStore

        :param Cookie $cookie: A Cookie object
        :rtype: ``CookieStore``
        :returns: new ``CookieStore`` instance

        Store a new cookie and return a new collection. The original collection is left unchanged.

    .. php:method:: remove(string $name[, string $prefix = '']): CookieStore

        :param string $name: Cookie name
        :param string $prefix: Cookie prefix
        :rtype: ``CookieStore``
        :returns: new ``CookieStore`` instance

        Removes a cookie from a collection and returns an updated collection.
        The original collection is left unchanged.

    .. php:method:: dispatch(): void

        :rtype: void

        Dispatches all cookies in store.

    .. php:method:: display(): array

        :rtype: array
        :returns: Returns all cookie instances in store.

    .. php:method:: clear(): void

        :rtype: void

        Clears the cookie collection.
