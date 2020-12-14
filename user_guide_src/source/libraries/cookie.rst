######
Cookie
######

The Cookie class is a mechanism for storing data in the remote browser and
thus tracking or identifying return users.

.. contents::
    :local:
    :depth: 2

.. raw:: html

  <div class="custom-index container"></div>

Using the Cookie Class
*********************************************************************

Initializing a Cookie
==================================================================

To access and initialize the cookie via service **cookie**::

	$cookie = \Config\Services::cookie($config);

The ``$config`` parameter is optional - application configuration.
If not provided, the services register will instantiate the default
one **app/Config/Cookie.php**.

Once loaded, the ``Cookie`` library object will be available using::

	$cookie

Alternatively, you can use the helper function that will use the default
configuration options. This version is a little friendlier to read,
but does not take any configuration options.
::

	$cookie = cookie();

Set Cookie Data
===================

Let's say a particular user logs into your site. Once authenticated, you
could add their username and e-mail address to the cookie, making that
data globally available to you without having to run a cookie query when
you need it.

You can simply assign data to the ``$cookie`` object, by passing parameters
to ``set()`` method::

	$cookie->set('item', 'hat', 3600);

Or with an associative array containing the data into first parameter. Here's
an example::

	$data = [
		'name'	  => 'item',
		'value'	  => 'hat',
		'expires' => 3600,
	];

	$cookie->set($data);

Or even through the cookie helper method::

	set_cookie($data);

Get Cookie Data
=======================

Any piece of information from the cookie array is available through the 
conventional accessor method::

	$cookie->get('item');

Or even through the cookie helper method::

	get_cookie('item');

Where ``item`` is the array key corresponding to the item you wish to fetch.
For example, to assign a previously stored 'item'::

	$name = $cookie->get('item');

.. note:: The ``get()`` method returns NULL if the item you are trying
	to access does not exist.

If you want to retrieve all of the existing cookies, you can simply
omit the item key::

	$cookie->get();

Or even through the cookie helper method::

	get_cookie();

Remove Cookie Data
=====================

Just as ``set()`` can be used to add information to a
cookie, ``remove()`` can be used to remove it, by passing the
cookie key. For example, if you wanted to remove 'item' from
cookie data array::

	$cookie->remove('item');

Or even through the cookie helper method::

	remove_cookie('item');

Check Cookie Data
=====================

To check whether cookie exists or not ``has()`` can be used to for it, by passing the
cookie key. For example, if you wanted to check 'item' in
cookie data array::

	$cookie->has('item');

Or even through the cookie helper method::

	has_cookie('item');

Send Cookie Data
=====================

After adding cookie with ``set()`` method use ``send()`` to store cookie data
in the remote browser::

	$cookie->set('item1', 'bag', 3600);
	$cookie->set('item2', 'cup', 1800);
	$cookie->send();

Or we can chain them together::

	$cookie->set('item1', 'bag', 3600)
		->set('item2', 'cup', 1800)
		->send();

Fetch Cookie Data
=======================

Fetch any piece of information from the ``$_COOKIE`` array is available through the conventional accessor method::

	$cookie->fetch('item');

Or even through the cookie helper method::

	fetch_cookie('item');

Where ``item`` is the array key corresponding to the item you wish to fetch.
For example, to assign a previously stored 'item'::

	$name = $cookie->fetch('item');

.. note:: The ``fetch()`` method returns NULL if the item you are trying
	to access does not exist.

If you want to retrieve all of the existing ``$_COOKIE``, you can simply
omit the item key::

	$cookie->fetch();

Cookie Preferences
*********************************************************************

CodeIgniter will usually make everything work out of the box. However,
Cookies are a very sensitive component of any application, so some
careful configuration must be done. Please take your time to consider
all of the options and their effects.

You'll find the following cookie related preferences in your **app/Config/Cookie.php** file:

============================== ============================================ ================================================= ============================================================================================
Preference                     Default                                      Options                                           Description
============================== ============================================ ================================================= ============================================================================================
**prefix**					   ''											None											  Set a cookie name prefix to avoid collisions.
**path**					   '/'											None                         					  Typically will be a forward slash.
**domain**					   ''											None                                              Set to `.example-domain.com` for site-wide cookies.
**secure**					   FALSE										TRUE/FALSE (boolean)                              Secure HTTPS connection is required for sending cookie.
**httponly**				   FALSE										TRUE/FALSE (boolean)                              Access cookie via HTTP(S) only. (no AJAX)
**samesite**				   Lax											'None', 'Lax', 'Strict', ''                       Setting for cookie SameSite.
============================== ============================================ ================================================= ============================================================================================

Chain method together::

	$cookie->setPrefix('mk_')->set('item', 'cap', 300)->send();

Class Reference
***************

.. php:class:: CodeIgniter\Cookie\BaseCookie

	.. php:method:: setPrefix(string $prefix)

		:param string $prefix: The cookie prefix
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie prefix.

	.. php:method:: getPrefix()

		:returns: ``$prefix`` configuration property
		:rtype:	``string``

		Get cookie prefix.

	.. php:method:: setPath(string $path)

		:param string $path: The cookie path
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie path.

	.. php:method:: getPath()

		:returns: ``$path`` configuration property
		:rtype:	``string``

		Get cookie path.

	.. php:method:: setDomain(string $domain)

		:param string $domain: The cookie domain
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie domain.

	.. php:method:: getDomain()

		:returns: ``$domain`` configuration property
		:rtype:	``string``

		Get cookie domain.

	.. php:method:: setSecure(bool $secure = true)

		:param boolean $secure: The cookie secure
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie secure.

	.. php:method:: isSecure()

		:returns: ``$secure`` configuration property
		:rtype:	``boolean``

		Check cookie secure status.

	.. php:method:: setHTTPOnly(bool $httponly = true)

		:param boolean $httponly: The cookie httponly
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie httponly.

	.. php:method:: isHTTPOnly()

		:returns: ``$httponly`` configuration property
		:rtype:	``boolean``

		Check cookie httponly status.

	.. php:method:: setSameSite(string $samesite)

		:param string $samesite: The cookie name samesite
		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Set cookie samesite.

		Returns the default samesite configuration if $samesite is invalid.

	.. php:method:: getSameSite()

		:returns: ``$samesite`` configuration property
		:rtype:	``string``

		Get cookie samesite.

	.. php:method:: reset()

		:returns: ``BaseCookie`` instance (method chaining)
		:rtype:	``BaseCookie``

		Reset configuration to default.

.. php:class:: CodeIgniter\Cookie\Cookie

	.. php:method:: set($name, string $value, int $expires = NULL, string $path = '/',
						string $domain = '', string $prefix = '', bool $secure = FALSE,
						bool $httponly = FALSE, string $samesite = NULL)

		:param string|array $name: The cookie name or array containing binds
		:param string $value: The cookie value
		:param integer|null $expires: The cookie expiration time (in seconds)
		:param string $path: The cookie path (default: '/')
		:param string $domain: The cookie domain (e.g.: '.example-domain.com')
		:param string $prefix: The cookie name prefix
		:param boolean $secure: Whether to transfer cookies via SSL only
		:param boolean $httponly: Whether to access cookies via HTTP only (no AJAX)
		:param string|null $samesite: The cookie samesite
		:returns: ``CookieInterface`` instance (method chaining)
		:rtype:	``CookieInterface``

		Set a cookie.

		Accepts an arbitrary number of binds or an associative array in the
		first parameter containing all the values.

	.. php:method:: get(string $name = '', string $prefix = '')

		:param string $name: The cookie name
		:param string $prefix: The cookie prefix
		:returns: ``An array of stored cookies``
		:rtype:	``array|null``

		Get a cookie.

		Return a specific cookie for the given name, if no name was given
		it will return all cookies.

	.. php:method:: remove(string $name, string $path = '/', string $domain = '', string $prefix = '')

		:param string $name: The cookie name
		:param string $path: The cookie path
		:param string $domain: The cookie domain
		:param string $prefix: The cookie prefix
		:returns: ``CookieInterface`` instance (method chaining)
		:rtype:	``CookieInterface``

		Remove a cookie.

		Delete a specific cookie for the given name.

	.. php:method:: has(string $name, string $prefix = '')

		:param string $name: The cookie name
		:param string $prefix: The cookie prefix
		:returns: ``TRUE if found, FALSE if not``
		:rtype:	``boolean``

		Has a cookie.

		Check whether cookie exists or not.

	.. php:method:: send()

		:rtype:	``void``

		Send the cookies.

		Send the cookies to the remote browser.

	.. php:method:: fetch(string $name = NULL, string $prefix = '', bool $xssClean = FALSE)

		:param string|null $name: The cookie name
		:param string $prefix: The cookie prefix
		:param boolean $xssClean: Whether to apply filter
		:returns: ``$_COOKIE if no name supplied, otherwise the COOKIE value or NULL``
		:rtype:	``string|array|null``

		Fetch a cookie.

		Return an item from the COOKIE array.

	.. php:method:: clear()

		:rtype:	``void``

		Clear stored cookies.
