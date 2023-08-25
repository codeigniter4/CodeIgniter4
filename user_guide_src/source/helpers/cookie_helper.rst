#############
Cookie Helper
#############

The Cookie Helper file contains functions that assist in working with
cookies.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is loaded using the following code:

.. literalinclude:: cookie_helper/001.php

Available Functions
===================

The following functions are available:

.. php:function:: set_cookie($name[, $value = ''[, $expire = ''[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = false[, $httpOnly = false[, $sameSite = '']]]]]]]])

    :param    array|Cookie|string    $name: Cookie name *or* associative array of all of the parameters available to this function *or* an instance of ``CodeIgniter\Cookie\Cookie``
    :param    string    $value: Cookie value
    :param    int    $expire: Number of seconds until expiration. If set to ``0`` the cookie will only last as long as the browser is open
    :param    string    $domain: Cookie domain (usually: .yourdomain.com)
    :param    string    $path: Cookie path
    :param    string    $prefix: Cookie name prefix. If ``''``, the default from **app/Config/Cookie.php** is used
    :param    bool    $secure: Whether to only send the cookie through HTTPS. If ``null``, the default from **app/Config/Cookie.php** is used
    :param    bool    $httpOnly: Whether to hide the cookie from JavaScript. If ``null``, the default from **app/Config/Cookie.php** is used
    :param    string    $sameSite: The value for the SameSite cookie parameter. If ``null``, the default from **app/Config/Cookie.php** is used
    :rtype:    void

    .. note:: Prior to v4.2.7, the default values of ``$secure`` and ``$httpOnly`` were ``false``
        due to a bug, and these values from **app/Config/Cookie.php** were never used.

    This helper function gives you friendlier syntax to set browser
    cookies. Refer to the :doc:`Response Library </outgoing/response>` for
    a description of its use, as this function is an alias for
    :php:meth:`CodeIgniter\\HTTP\\Response::setCookie()`.

.. php:function:: get_cookie($index[, $xssClean = false[, $prefix = '']])

    :param    string    $index: Cookie name
    :param    bool    $xssClean: Whether to apply XSS filtering to the returned value
    :param    string|null  $prefix: Cookie name prefix. If set to ``''``, the default value from **app/Config/Cookie.php** will be used. If set to ``null``, no prefix
    :returns:    The cookie value or null if not found
    :rtype:    mixed

    .. note:: Since v4.2.1, the third parameter ``$prefix`` has been introduced and the behavior has been changed a bit due to a bug fix. See :ref:`Upgrading <upgrade-421-get_cookie>` for details.

    This helper function gives you friendlier syntax to get browser
    cookies. Refer to the :doc:`IncomingRequest Library </incoming/incomingrequest>` for
    detailed description of its use, as this function acts very
    similarly to :php:meth:`CodeIgniter\\HTTP\\IncomingRequest::getCookie()`,
    except it will also prepend
    the ``Config\Cookie::$prefix`` that you might've set in your
    **app/Config/Cookie.php** file.

    .. warning:: Using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using :php:func:`esc()` with the correct ``$context`` in the views is recommended.

.. php:function:: delete_cookie($name[, $domain = ''[, $path = '/'[, $prefix = '']]])

    :param string $name: Cookie name
    :param string $domain: Cookie domain (usually: .yourdomain.com)
    :param string $path: Cookie path
    :param string $prefix: Cookie name prefix
    :rtype: void

    Lets you delete a cookie. Unless you've set a custom path or other
    values, only the name of the cookie is needed.

    .. literalinclude:: cookie_helper/002.php

    This function is otherwise identical to :php:func:`set_cookie()`, except that it
    does not have the ``value`` and ``expire`` parameters.

    .. note:: When you use :php:func:`set_cookie()`,
        if the ``value`` is set to empty string and the ``expire`` is set to ``0``, the cookie will be deleted.
        If the ``value`` is set to non-empty string and the ``expire`` is set to ``0``, the cookie will only last as long as the browser is open.

    You can submit an
    array of values in the first parameter or you can set discrete
    parameters.

    .. literalinclude:: cookie_helper/003.php

.. php:function:: has_cookie(string $name[, ?string $value = null[, string $prefix = '']])

    :param string $name: Cookie name
    :param string|null $value: Cookie value
    :param string $prefix: Cookie prefix
    :rtype: bool

    Checks if a cookie exists by name. This is an alias of ``Response::hasCookie()``.
