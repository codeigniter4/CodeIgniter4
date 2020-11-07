#############
Cookie Helper
#############

The Cookie Helper file contains functions that assist in working with
cookies.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

Loading this Helper
===================

This helper is loaded using the following code::

	helper('cookie');

Available Functions
===================

The following functions are available:

.. php:function:: set_cookie($name[, $value = ''[, $expire = ''[, $domain = ''[, $path = '/'[, $prefix = ''[, $secure = false[, $httpOnly = false[, $sameSite = '']]]]]]]])

	:param	mixed	$name: Cookie name *or* associative array of all of the parameters available to this function
	:param	string	$value: Cookie value
	:param	int	$expire: Number of seconds until expiration
	:param	string	$domain: Cookie domain (usually: .yourdomain.com)
	:param	string	$path: Cookie path
	:param	string	$prefix: Cookie name prefix
	:param	bool	$secure: Whether to only send the cookie through HTTPS
	:param	bool	$httpOnly: Whether to hide the cookie from JavaScript
	:param	string	$sameSite: The value for the SameSite cookie parameter. If null, the default from `config/App.php` is used
	:rtype:	void

	This helper function gives you friendlier syntax to set browser
	cookies. Refer to the :doc:`Response Library </outgoing/response>` for
	a description of its use, as this function is an alias for
	``Response::setCookie()``.

.. php:function:: get_cookie($index[, $xssClean = false])

	:param	string	$index: Cookie name
	:param	bool	$xss_clean: Whether to apply XSS filtering to the returned value
	:returns:	The cookie value or NULL if not found
	:rtype:	mixed

	This helper function gives you friendlier syntax to get browser
	cookies. Refer to the :doc:`IncomingRequest Library </incoming/incomingrequest>` for
	detailed description of its use, as this function acts very
	similarly to ``IncomingRequest::getCookie()``, except it will also prepend
	the ``$cookiePrefix`` that you might've set in your
	*app/Config/App.php* file.

.. php:function:: delete_cookie($name[, $domain = ''[, $path = '/'[, $prefix = '']]])

	:param	string	$name: Cookie name
	:param	string	$domain: Cookie domain (usually: .yourdomain.com)
	:param	string	$path: Cookie path
	:param	string	$prefix: Cookie name prefix
	:rtype:	void

	Lets you delete a cookie. Unless you've set a custom path or other
	values, only the name of the cookie is needed.
	::

		delete_cookie('name');

	This function is otherwise identical to ``set_cookie()``, except that it
	does not have the value and expiration parameters. You can submit an
	array of values in the first parameter or you can set discrete
	parameters.
	::

		delete_cookie($name, $domain, $path, $prefix);
