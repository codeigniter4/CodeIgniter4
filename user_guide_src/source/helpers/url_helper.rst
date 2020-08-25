##########
URL Helper
##########

The URL Helper file contains functions that assist in working with URLs.

.. contents::
  :local:

.. raw:: html

  <div class="custom-index container"></div>

Loading this Helper
===================

This helper is automatically loaded by the framework on every request.

Available Functions
===================

The following functions are available:

.. php:function:: site_url([$uri = ''[, $protocol = NULL[, $altConfig = NULL]]])

    :param  mixed         $uri: URI string or array of URI segments
    :param  string        $protocol: Protocol, e.g. 'http' or 'https'
    :param  \\Config\\App $altConfig: Alternate configuration to use
    :returns: Site URL
    :rtype:	string

    Returns your site URL, as specified in your config file. The index.php
    file (or whatever you have set as your site **indexPage** in your config
    file) will be added to the URL, as will any URI segments you pass to the
    function.

    You are encouraged to use this function any time you need to generate a
    local URL so that your pages become more portable in the event your URL
    changes.

    Segments can be optionally passed to the function as a string or an
    array. Here is a string example::

        echo site_url('news/local/123');

    The above example would return something like:
    *http://example.com/index.php/news/local/123*

    Here is an example of segments passed as an array::

        $segments = ['news', 'local', '123'];
        echo site_url($segments);

    You may find the alternate configuration useful if generating URLs for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

.. php:function:: base_url([$uri = ''[, $protocol = NULL]])

    :param  mixed   $uri: URI string or array of URI segments
    :param  string  $protocol: Protocol, e.g. 'http' or 'https'
    :returns: Base URL
    :rtype: string

    Returns your site base URL, as specified in your config file. Example::

        echo base_url();

    This function returns the same thing as :php:func:`site_url()`, without
    the *indexPage* being appended.

    Also like :php:func:`site_url()`, you can supply segments as a string or
    an array. Here is a string example::

        echo base_url('blog/post/123');

    The above example would return something like:
    *http://example.com/blog/post/123*

    This is useful because unlike :php:func:`site_url()`, you can supply a
    string to a file, such as an image or stylesheet. For example::

        echo base_url('images/icons/edit.png');

    This would give you something like:
    *http://example.com/images/icons/edit.png*

.. php:function:: current_url([$returnObject = false])

    :param	boolean	$returnObject: True if you would like a URI instance returned, instead of a string.
    :returns: The current URL
    :rtype:	string|\\CodeIgniter\\HTTP\\URI

    Returns the full URL (including segments) of the page being currently
    viewed.

    .. note:: Calling this function is the same as doing this:
        ::

            base_url(uri_string());

.. php:function:: previous_url([$returnObject = false])

    :param boolean $returnObject: True if you would like a URI instance returned instead of a string.
    :returns: The URL the user was previously on
    :rtype: string|mixed|\\CodeIgniter\\HTTP\\URI

    Returns the full URL (including segments) of the page the user was previously on.

    Due to security issues of blindly trusting the HTTP_REFERER system variable, CodeIgniter will
    store previously visited pages in the session if it's available. This ensures that we always
    use a known and trusted source. If the session hasn't been loaded, or is otherwise unavailable,
    then a sanitized version of HTTP_REFERER will be used.

.. php:function:: uri_string()

    :returns: An URI string
    :rtype:	string

    Returns the path part relative to **baseUrl**.
    For example, if your URL was this::

        http://some-site.com/blog/comments/123

    The function would return::

        blog/comments/123

.. php:function:: index_page([$altConfig = NULL])

    :param \\Config\\App $altConfig: Alternate configuration to use
    :returns: 'index_page' value
    :rtype:	string

    Returns your site **indexPage**, as specified in your config file.
    Example::

        echo index_page();

    As with :php:func:`site_url()`, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating URLs for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

.. php:function:: anchor([$uri = ''[, $title = ''[, $attributes = ''[, $altConfig = NULL]]]])

    :param  mixed         $uri: URI string or array of URI segments
    :param  string        $title: Anchor title
    :param  mixed         $attributes: HTML attributes
    :param  \\Config\\App $altConfig: Alternate configuration to use
    :returns: HTML hyperlink (anchor tag)
    :rtype:	string

    Creates a standard HTML anchor link based on your local site URL.

    The first parameter can contain any segments you wish appended to the
    URL. As with the :php:func:`site_url()` function above, segments can
    be a string or an array.

    .. note:: If you are building links that are internal to your application
        do not include the base URL (`http://...`). This will be added
        automatically from the information specified in your config file.
        Include only the URI segments you wish appended to the URL.

    The second segment is the text you would like the link to say. If you
    leave it blank, the URL will be used.

    The third parameter can contain a list of attributes you would like
    added to the link. The attributes can be a simple string or an
    associative array.

    Here are some examples::

        echo anchor('news/local/123', 'My News', 'title="News title"');
        // Prints: <a href="http://example.com/index.php/news/local/123" title="News title">My News</a>

        echo anchor('news/local/123', 'My News', ['title' => 'The best news!']);
        // Prints: <a href="http://example.com/index.php/news/local/123" title="The best news!">My News</a>

        echo anchor('', 'Click here');
        // Prints: <a href="http://example.com/index.php">Click here</a>

    As above, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating links for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

    .. note:: Attributes passed into the anchor function are automatically escaped to protected against XSS attacks.

.. php:function:: anchor_popup([$uri = ''[, $title = ''[, $attributes = FALSE[, $altConfig = NULL]]]])

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

    Here is an example with attributes::

        $atts = [
            'width'       => 800,
            'height'      => 600,
            'scrollbars'  => 'yes',
            'status'      => 'yes',
            'resizable'   => 'yes',
            'screenx'     => 0,
            'screeny'     => 0,
            'window_name' => '_blank'
        ];

        echo anchor_popup('news/local/123', 'Click Me!', $atts);

    As above, you may specify an alternate configuration.
    You may find the alternate configuration useful if generating links for a
    different site than yours, which contains different configuration preferences.
    We use this for unit testing the framework itself.

    .. note:: The above attributes are the function defaults so you only need to
        set the ones that are different from what you need. If you want the
        function to use all of its defaults simply pass an empty array in the
        third parameter::

            echo anchor_popup('news/local/123', 'Click Me!', []);

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

    Creates a standard HTML e-mail link. Usage example::

        echo mailto('me@my-site.com', 'Click Here to Contact Me');

    As with the :php:func:`anchor()` tab above, you can set attributes using the
    third parameter::

        $attributes = ['title' => 'Mail me'];
        echo mailto('me@my-site.com', 'Contact Me', $attributes);

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

.. php:function:: auto_link($str[, $type = 'both'[, $popup = FALSE]])

    :param  string  $str: Input string
    :param  string  $type: Link type ('email', 'url' or 'both')
    :param  bool    $popup: Whether to create popup links
    :returns: Linkified string
    :rtype: string

    Automatically turns URLs and e-mail addresses contained in a string into
    links. Example::

        $string = auto_link($string);

    The second parameter determines whether URLs and e-mails are converted or
    just one or the other. The default behavior is both if the parameter is not
    specified. E-mail links are encoded as :php:func:`safe_mailto()` as shown
    above.

    Converts only URLs::

        $string = auto_link($string, 'url');

    Converts only e-mail addresses::

        $string = auto_link($string, 'email');

    The third parameter determines whether links are shown in a new window.
    The value can be TRUE or FALSE (boolean)::

        $string = auto_link($string, 'both', TRUE);

    .. note:: The only URLs recognized are those that start with "www." or with "://".

.. php:function:: url_title($str[, $separator = '-'[, $lowercase = FALSE]])

    :param  string  $str: Input string
    :param  string  $separator: Word separator (usually '-' or '_')
    :param  bool    $lowercase: Whether to transform the output string to lowercase
    :returns: URL-formatted string
    :rtype: string

    Takes a string as input and creates a human-friendly URL string. This is
    useful if, for example, you have a blog in which you'd like to use the
    title of your entries in the URL. Example::

        $title     = "What's wrong with CSS?";
        $url_title = url_title($title);
        // Produces: Whats-wrong-with-CSS

    The second parameter determines the word delimiter. By default dashes
    are used. Preferred options are: **-** (dash) or **_** (underscore).

    Example::

        $title     = "What's wrong with CSS?";
        $url_title = url_title($title, '_');
        // Produces: Whats_wrong_with_CSS

    The third parameter determines whether or not lowercase characters are
    forced. By default they are not. Options are boolean TRUE/FALSE.

    Example::

        $title     = "What's wrong with CSS?";
        $url_title = url_title($title, '-', TRUE);
        // Produces: whats-wrong-with-css

.. php:function:: mb_url_title($str[, $separator = '-'[, $lowercase = FALSE]])

    :param  string  $str: Input string
    :param  string  $separator: Word separator (usually '-' or '_')
    :param  bool    $lowercase: Whether to transform the output string to lowercase
    :returns: URL-formatted string
    :rtype: string

    This function works the same as :php:func:`url_title()` but it converts all
    accented characters automatically.

.. php:function:: prep_url($str = '')

    :param  string  $str: URL string
    :returns: Protocol-prefixed URL string
    :rtype: string

    This function will add *http://* in the event that a protocol prefix
    is missing from a URL.

    Pass the URL string to the function like this::

        $url = prep_url('example.com');

.. php:function:: url_to($controller[, ...$args])

    :param  string  $controller: The controller class and method
    :param  mixed   ...$args: Additional arguments to be injected into the route
    :returns: Absolute URL
    :rtype: string

    Builds an absolute URL to a controller method in your app. Example::

        echo url_to('Home::index');

    You can also add arguments to the route.
    Here is an example::

        echo url_to('Page::index', 'home');

    The above example would return something like:
    *http://example.com/page/home*

    This is useful because you can still change your routes after putting links
    into your views.

.. php:function:: url_is($path)

    :param string $path: The path to check the current URI path against.
    :rtype: boolean

    Compares the current URL's path against the given path to see if they match. Example::

        if (url_is('admin')) { ... }

    This would match ``http://example.com/admin``. You can use the ``*`` wildcard to match
    any other applicable characters in the URL::

        if (url_is('admin*')) { ... }

    This would match any of the following:

    - /admin
    - /admin/
    - /admin/users
    - /admin/users/schools/classmates/...
