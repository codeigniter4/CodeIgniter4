##########
URL Helper
##########

The URL Helper file contains functions that assist in working with URLs.

.. contents::
    :local:
    :depth: 2

Loading this Helper
===================

This helper is automatically loaded by the framework on every request.

Available Functions
===================

The following functions are available:

.. php:function:: site_url([$uri = ''[, $protocol = null[, $altConfig = null]]])

    :param  array|string         $uri: URI string or array of URI segments
    :param  string        $protocol: Protocol, e.g., 'http' or 'https'
    :param  \\Config\\App $altConfig: Alternate configuration to use
    :returns: Site URL
    :rtype:    string

    .. note:: Since v4.3.0, if you set ``Config\App::$allowedHostnames``,
        this returns the URL with the hostname set in it if the current URL matches.

    Returns your site URL, as specified in your config file. The **index.php**
    file (or whatever you have set as your site ``Config\App::$indexPage`` in your config
    file) will be added to the URL, as will any URI segments you pass to the
    function.

    You are encouraged to use this function any time you need to generate a
    local URL so that your pages become more portable in the event your URL
    changes.

    Segments can be optionally passed to the function as a string or an
    array. Here is a string example:

    .. literalinclude:: url_helper/001.php

    The above example would return something like:
    **http://example.com/index.php/news/local/123**

    Here is an example of segments passed as an array:

    .. literalinclude:: url_helper/002.php

    You may find the alternate configuration useful if generating URLs for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

.. php:function:: base_url([$uri = ''[, $protocol = null]])

    :param  array|string   $uri: URI string or array of URI segments
    :param  string  $protocol: Protocol, e.g., 'http' or 'https'
    :returns: Base URL
    :rtype: string

    .. note:: Since v4.3.0, if you set ``Config\App::$allowedHostnames``,
        this returns the URL with the hostname set in it if the current URL matches.

    .. note:: In previous versions, this returned the base URL without a trailing
        slash (``/``) when called with no argument. The bug was fixed and
        since v4.3.2 it returns the base URL with a trailing slash.

    Returns your site base URL, as specified in your config file. Example:

    .. literalinclude:: url_helper/003.php

    This function returns the same thing as :php:func:`site_url()`, without
    the ``Config\App::$indexPage`` being appended.

    Also like :php:func:`site_url()`, you can supply segments as a string or
    an array. Here is a string example:

    .. literalinclude:: url_helper/004.php

    The above example would return something like:
    **http://example.com/blog/post/123**

    This is useful because unlike :php:func:`site_url()`, you can supply a
    string to a file, such as an image or stylesheet. For example:

    .. literalinclude:: url_helper/005.php

    This would give you something like:
    **http://example.com/images/icons/edit.png**

.. php:function:: current_url([$returnObject = false[, $request = null]])

    :param    boolean    $returnObject: True if you would like a URI instance returned, instead of a string.
    :param    IncomingRequest|null    $request: An alternate request to use for path detection; useful for testing.
    :returns: The current URL
    :rtype:    string|\\CodeIgniter\\HTTP\\URI

    Returns the full URL of the page being currently viewed.
    When returning string, the query and fragment parts of the URL are removed.
    When returning URI, the query and fragment parts are preserved.

    However for security reasons, it is created based on the ``Config\App`` settings,
    and not intended to match the browser URL.

    Since v4.3.0, if you set ``Config\App::$allowedHostnames``,
    this returns the URL with the hostname set in it if the current URL matches.

    .. note:: Calling ``current_url()`` is the same as doing this:

        .. literalinclude:: url_helper/006.php
           :lines: 2-

    .. important:: Prior to v4.1.2, this function had a bug causing it to ignore the configuration on ``Config\App::$indexPage``.

.. php:function:: previous_url([$returnObject = false])

    :param boolean $returnObject: True if you would like a URI instance returned instead of a string.
    :returns: The URL the user was previously on
    :rtype: string|mixed|\\CodeIgniter\\HTTP\\URI

    Returns the full URL (including segments) of the page the user was previously on.

    .. note:: Due to security issues of blindly trusting the ``HTTP_REFERER`` system variable, CodeIgniter will
        store previously visited pages in the session if it's available. This ensures that we always
        use a known and trusted source. If the session hasn't been loaded, or is otherwise unavailable,
        then a sanitized version of ``HTTP_REFERER`` will be used.

.. php:function:: uri_string()

    :returns: A URI string
    :rtype:   string

    Returns the path part of the current URL relative to baseURL.

    For example, when your baseURL is **http://some-site.com/** and the current URL is::

        http://some-site.com/blog/comments/123

    The function would return::

        blog/comments/123

    When your baseURL is **http://some-site.com/subfolder/** and the current URL is::

        http://some-site.com/subfolder/blog/comments/123

    The function would return::

        blog/comments/123

    .. note:: In previous versions, the parameter ``$relative = false`` was defined.
        However, due to a bug, this function always returned a path relative to baseURL.
        Since v4.3.2, the parameter has been removed.

    .. note:: In previous versions, when you navigate to the baseURL, this function
        returned ``/``. Since v4.3.2, the bug has been fixed and it returns an
        empty string(``''``).

.. php:function:: index_page([$altConfig = null])

    :param \\Config\\App $altConfig: Alternate configuration to use
    :returns:  The ``indexPage`` value
    :rtype:    string

    Returns your site **indexPage**, as specified in your config file.
    Example:

    .. literalinclude:: url_helper/007.php

    As with :php:func:`site_url()`, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating URLs for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

.. php:function:: anchor([$uri = ''[, $title = ''[, $attributes = ''[, $altConfig = null]]]])

    :param  mixed         $uri: URI string or array of URI segments
    :param  string        $title: Anchor title
    :param  mixed         $attributes: HTML attributes
    :param  \\Config\\App $altConfig: Alternate configuration to use
    :returns: HTML hyperlink (anchor tag)
    :rtype:    string

    Creates a standard HTML anchor link based on your local site URL.

    The first parameter can contain any segments you wish appended to the
    URL. As with the :php:func:`site_url()` function above, segments can
    be a string or an array.

    .. note:: If you are building links that are internal to your application
        do not include the base URL (``http://...``). This will be added
        automatically from the information specified in your config file.
        Include only the URI segments you wish appended to the URL.

    The second segment is the text you would like the link to say. If you
    leave it blank, the URL will be used.

    The third parameter can contain a list of attributes you would like
    added to the link. The attributes can be a simple string or an
    associative array.

    Here are some examples:

    .. literalinclude:: url_helper/008.php

    As above, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating links for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

    .. note:: Attributes passed into the anchor function are automatically escaped to protected against XSS attacks.

.. php:function:: anchor_popup([$uri = ''[, $title = ''[, $attributes = false[, $altConfig = null]]]])

    :param  string          $uri: URI string
    :param  string          $title: Anchor title
    :param  mixed           $attributes: HTML attributes
    :param  \\Config\\App   $altConfig: Alternate configuration to use
    :returns: Pop-up hyperlink
    :rtype: string

    Nearly identical to the :php:func:`anchor()` function except that it
    opens the URL in a new window. You can specify JavaScript window
    attributes in the third parameter to control how the window is opened.
    If the third parameter is not set it will simply open a new window with
    your own browser settings.

    Here is an example with attributes:

    .. literalinclude:: url_helper/009.php

    As above, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating links for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

    .. note:: The above attributes are the function defaults so you only need to
        set the ones that are different from what you need. If you want the
        function to use all of its defaults simply pass an empty array in the
        third parameter:

        .. literalinclude:: url_helper/010.php

    .. note:: The **window_name** is not really an attribute, but an argument to
        the JavaScript `window.open() <https://www.w3schools.com/jsref/met_win_open.asp>`_
        method, which accepts either a window name or a window target.

    .. note:: Any other attribute than the listed above will be parsed as an
        HTML attribute to the anchor tag.

    .. note:: Attributes passed into the anchor_popup function are automatically escaped to protected against XSS attacks.

.. php:function:: mailto($email[, $title = ''[, $attributes = '']])

    :param  string  $email: E-mail address
    :param  string  $title: Anchor title
    :param  mixed   $attributes: HTML attributes
    :returns: A "mail to" hyperlink
    :rtype: string

    Creates a standard HTML e-mail link. Usage example:

    .. literalinclude:: url_helper/011.php

    As with the :php:func:`anchor()` tab above, you can set attributes using the
    third parameter:

    .. literalinclude:: url_helper/012.php

    .. note:: Attributes passed into the mailto function are automatically escaped to protected against XSS attacks.

.. php:function:: safe_mailto($email[, $title = ''[, $attributes = '']])

    :param  string  $email: E-mail address
    :param  string  $title: Anchor title
    :param  mixed   $attributes: HTML attributes
    :returns: A spam-safe "mail to" hyperlink
    :rtype: string

    Identical to the :php:func:`mailto()` function except it writes an obfuscated
    version of the *mailto* tag using ordinal numbers written with JavaScript to
    help prevent the e-mail address from being harvested by spam bots.

.. php:function:: auto_link($str[, $type = 'both'[, $popup = false]])

    :param  string  $str: Input string
    :param  string  $type: Link type ('email', 'url' or 'both')
    :param  bool    $popup: Whether to create popup links
    :returns: Linkified string
    :rtype: string

    Automatically turns URLs and e-mail addresses contained in a string into
    links. Example:

    .. literalinclude:: url_helper/013.php

    The second parameter determines whether URLs and e-mails are converted or
    just one or the other. The default behavior is both if the parameter is not
    specified. E-mail links are encoded as :php:func:`safe_mailto()` as shown
    above.

    Converts only URLs:

    .. literalinclude:: url_helper/014.php

    Converts only e-mail addresses:

    .. literalinclude:: url_helper/015.php

    The third parameter determines whether links are shown in a new window.
    The value can be true or false (boolean):

    .. literalinclude:: url_helper/016.php

    .. note:: The only URLs recognized are those that start with ``www.`` or with ``://``.

.. php:function:: url_title($str[, $separator = '-'[, $lowercase = false]])

    :param  string  $str: Input string
    :param  string  $separator: Word separator (usually ``'-'`` or ``'_'``)
    :param  bool    $lowercase: Whether to transform the output string to lowercase
    :returns: URL-formatted string
    :rtype: string

    Takes a string as input and creates a human-friendly URL string. This is
    useful if, for example, you have a blog in which you'd like to use the
    title of your entries in the URL. Example:

    .. literalinclude:: url_helper/017.php

    The second parameter determines the word delimiter. By default dashes
    are used. Preferred options are: ``-`` (dash) or ``_`` (underscore).

    Example:

    .. literalinclude:: url_helper/018.php

    The third parameter determines whether or not lowercase characters are
    forced. By default they are not. Options are boolean true/false.

    Example:

    .. literalinclude:: url_helper/019.php

.. php:function:: mb_url_title($str[, $separator = '-'[, $lowercase = false]])

    :param  string  $str: Input string
    :param  string  $separator: Word separator (usually ``'-'`` or ``'_'``)
    :param  bool    $lowercase: Whether to transform the output string to lowercase
    :returns: URL-formatted string
    :rtype: string

    This function works the same as :php:func:`url_title()` but it converts all
    accented characters automatically.

.. php:function:: prep_url([$str = ''[, $secure = false]])

    :param  string   $str: URL string
    :param  boolean  $secure: true for ``https://``
    :returns: Protocol-prefixed URL string
    :rtype: string

    This function will add ``http://`` or ``https://`` in the event that a protocol prefix
    is missing from a URL.

    Pass the URL string to the function like this:

    .. literalinclude:: url_helper/020.php

.. php:function:: url_to($controller[, ...$args])

    :param  string  $controller: Route name or Controller::method
    :param  mixed   ...$args:    One or more parameters to be passed to the route. The last parameter allows you to set the locale.
    :returns: Absolute URL
    :rtype: string

    .. note:: This function requires the controller/method to have a route defined in **app/Config/routes.php**.

    Builds an absolute URL to a controller method in your app. Example:

    .. literalinclude:: url_helper/021.php

    You can also add arguments to the route.
    Here is an example:

    .. literalinclude:: url_helper/022.php

    This is useful because you can still change your routes after putting links
    into your views.

    Since v4.3.0, when you use ``{locale}`` in your route, you can optionally specify the locale value as the last parameter.

    .. literalinclude:: url_helper/025.php

    For full details, see the :ref:`reverse-routing` and :ref:`using-named-routes`.

.. php:function:: url_is($path)

    :param string $path: The URL path relative to baseURL to check the current URI path against.
    :rtype: boolean

    Compares the current URL's path against the given path to see if they match. Example:

    .. literalinclude:: url_helper/023.php

    This would match **http://example.com/admin**. It would match **http://example.com/subdir/admin**
    if your baseURL is ``http://example.com/subdir/``.

    You can use the ``*`` wildcard to match
    any other applicable characters in the URL:

    .. literalinclude:: url_helper/024.php

    This would match any of the following:

    - /admin
    - /admin/
    - /admin/users
    - /admin/users/schools/classmates/...
