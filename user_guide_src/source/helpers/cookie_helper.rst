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

    :param    mixed    $name: Cookie name *or* associative array of all of the parameters available to this function
    :param    string    $value: Cookie value
    :param    int    $expire: Number of seconds until expiration
    :param    string    $domain: Cookie domain (usually: .yourdomain.com)
    :param    string    $path: Cookie path
    :param    string    $prefix: Cookie name prefix
    :param    bool    $secure: Whether to only send the cookie through HTTPS
    :param    bool    $httpOnly: Whether to hide the cookie from JavaScript
    :param    string    $sameSite: The value for the SameSite cookie parameter. If null, the default from `config/App.php` is used
    :rtype:    void

    This helper function gives you friendlier syntax to set browser
    cookies. Refer to the :doc:`Response Library </outgoing/response>` for
    a description of its use, as this function is an alias for
    ``Response::setCookie()``.

.. php:function:: get_cookie($index[, $xssClean = false])

    :param    string    $index: Cookie name
    :param    bool    $xssClean: Whether to apply XSS filtering to the returned value
    :returns:    The cookie value or null if not found
    :rtype:    mixed

    This helper function gives you friendlier syntax to get browser
    cookies. Refer to the :doc:`IncomingRequest Library </incoming/incomingrequest>` for
    detailed description of its use, as this function acts very
    similarly to ``IncomingRequest::getCookie()``, except it will also prepend
    the ``$cookiePrefix`` that you might've set in your
    **app/Config/App.php** file.

.. warning:: Using XSS filtering is a bad practice. It does not prevent XSS attacks perfectly. Using ``esc()`` with the correct ``$context`` in the views is recommended.

.. php:function:: delete_cookie($name[, $domain = ''[, $path = '/'[, $prefix = '']]])

    :param string $name: Cookie name
    :param string $domain: Cookie domain (usually: .yourdomain.com)
    :param string $path: Cookie path
    :param string $prefix: Cookie name prefix
    :rtype: void

    Lets you delete a cookie. Unless you've set a custom path or other
    values, only the name of the cookie is needed.

    .. literalinclude:: cookie_helper/002.php

    This function is otherwise identical to ``set_cookie()``, except that it
    does not have the value and expiration parameters. You can submit an
    array of values in the first parameter or you can set discrete
    parameters.

    .. literalinclude:: cookie_helper/003.php

.. php:function:: has_cookie(string $name[, ?string $value = null[, string $prefix = '']])

    :param string $name: Cookie name
    :param string|null $value: Cookie value
    :param string $prefix: Cookie prefix
    :rtype: bool

    Checks if a cookie exists by name. This is an alias of ``Response::hasCookie()``.
