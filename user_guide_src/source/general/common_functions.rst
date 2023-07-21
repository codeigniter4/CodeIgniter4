##############################
Global Functions and Constants
##############################

CodeIgniter provides a few functions and variables that are globally defined, and are available to you at any point.
These do not require loading any additional libraries or helpers.

.. contents::
    :local:
    :depth: 2

================
Global Functions
================

Service Accessors
=================

.. php:function:: cache([$key])

    :param  string $key: The cache name of the item to retrieve from cache (Optional)
    :returns: Either the cache object, or the item retrieved from the cache
    :rtype: mixed

    If no $key is provided, will return the Cache engine instance. If a $key
    is provided, will return the value of $key as stored in the cache currently,
    or null if no value is found.

    Examples:

    .. literalinclude:: common_functions/001.php

.. php:function:: config(string $name[, bool $getShared = true])

    :param string $name: The config classname.
    :param bool $getShared: Whether to return a shared instance.
    :returns: The config instances.
    :rtype: object|null

    More simple way of getting config instances from Factories.

    See :ref:`Configuration <configuration-config>` and
    :ref:`Factories <factories-config>` for details.

    The ``config()`` uses ``Factories::config()`` internally.
    See :ref:`factories-loading-class` for details on the first parameter ``$name``.

.. php:function:: cookie(string $name[, string $value = ''[, array $options = []]])

    :param string $name: Cookie name
    :param string $value: Cookie value
    :param array $options: Cookie options
    :rtype: ``Cookie``
    :returns: ``Cookie`` instance
    :throws: ``CookieException``

    Simpler way to create a new Cookie instance.

.. php:function:: cookies([array $cookies = [][, bool $getGlobal = true]])

    :param array $cookies: If ``getGlobal`` is ``false``, this is passed to ``CookieStore``'s constructor.
    :param bool $getGlobal: If ``false``, creates a new instance of ``CookieStore``.
    :rtype: ``CookieStore``
    :returns: Instance of ``CookieStore`` saved in the current ``Response``, or a new ``CookieStore`` instance.

    Fetches the global ``CookieStore`` instance held by ``Response``.

.. php:function:: env($key[, $default = null])

    :param string $key: The name of the environment variable to retrieve
    :param mixed  $default: The default value to return if no value is found.
    :returns: The environment variable, the default value, or null.
    :rtype: mixed

    Used to retrieve values that have previously been set to the environment,
    or return a default value if it is not found. Will format boolean values
    to actual booleans instead of string representations.

    Especially useful when used in conjunction with **.env** files for setting
    values that are specific to the environment itself, like database
    settings, API keys, etc.

.. php:function:: esc($data[, $context = 'html'[, $encoding]])

    :param   string|array   $data: The information to be escaped.
    :param   string   $context: The escaping context. Default is 'html'.
    :param   string   $encoding: The character encoding of the string.
    :returns: The escaped data.
    :rtype: mixed

    Escapes data for inclusion in web pages, to help prevent XSS attacks.
    This uses the Laminas Escaper library to handle the actual filtering of the data.

    If $data is a string, then it simply escapes and returns it.
    If $data is an array, then it loops over it, escaping each 'value' of the key/value pairs.

    Valid context values: ``html``, ``js``, ``css``, ``url``, ``attr``, ``raw``

.. php:function:: helper($filename)

    :param   string|array  $filename: The name of the helper file to load, or an array of names.

    Loads a helper file.

    For full details, see the :doc:`helpers` page.

.. php:function:: lang($line[, $args[, $locale]])

    :param string $line: The line of text to retrieve
    :param array  $args: An array of data to substitute for placeholders.
    :param string $locale: Specify a different locale to be used instead of default one.

    Retrieves a locale-specific file based on an alias string.

    For more information, see the :doc:`Localization </outgoing/localization>` page.

.. php:function:: model($name[, $getShared = true[, &$conn = null]])

    :param string                   $name: The model classname.
    :param boolean                  $getShared: Whether to return a shared instance.
    :param ConnectionInterface|null $conn: The database connection.
    :returns: The model instances
    :rtype: object

    More simple way of getting model instances.

    The ``model()`` uses ``Factories::models()`` internally.
    See :ref:`factories-loading-class` for details on the first parameter ``$name``.

    See also the :ref:`Using CodeIgniter's Model <accessing-models>`.

.. php:function:: old($key[, $default = null,[, $escape = 'html']])

    :param string $key: The name of the old form data to check for.
    :param string|null  $default: The default value to return if $key doesn't exist.
    :param false|string  $escape: An `escape <#esc>`_ context or false to disable it.
    :returns: The value of the defined key, or the default value.
    :rtype: array|string|null

    Provides a simple way to access "old input data" from submitting a form.

    Example:

    .. literalinclude:: common_functions/002.php

.. note:: If you are using the :php:func:`set_value()`, :php:func:`set_select()`,
    :php:func:`set_checkbox()`, and :php:func:`set_radio()` functions in
    :doc:`form helper </helpers/form_helper>`, this feature is built-in. You only
    need to use this function when not using the form helper.

.. php:function:: session([$key])

    :param string $key: The name of the session item to check for.
    :returns: An instance of the Session object if no $key, the value found in the session for $key, or null.
    :rtype: mixed

    Provides a convenient way to access the session class and to retrieve a
    stored value. For more information, see the :doc:`Sessions </libraries/sessions>` page.

.. php:function:: timer([$name])

    :param string $name: The name of the benchmark point.
    :returns: The Timer instance
    :rtype: CodeIgniter\Debug\Timer

    A convenience method that provides quick access to the Timer class. You can pass in the name
    of a benchmark point as the only parameter. This will start timing from this point, or stop
    timing if a timer with this name is already running.

    Example:

    .. literalinclude:: common_functions/003.php

.. php:function:: view($name[, $data[, $options]])

    :param   string   $name: The name of the file to load
    :param   array    $data: An array of key/value pairs to make available within the view.
    :param   array    $options: An array of options that will be passed to the rendering class.
    :returns: The output from the view.
    :rtype: string

    Grabs the current RendererInterface-compatible class
    and tells it to render the specified view. Simply provides
    a convenience method that can be used in Controllers,
    libraries, and routed closures.

    Currently, these options are available for use within the ``$options`` array:

    - ``saveData`` specifies that data will persistent between multiple calls to ``view()`` within the same request. If you do not want the data to be persisted, specify false.
    - ``cache`` specifies the number of seconds to cache the view for. See :ref:`caching-views` for the details.
    - ``debug`` can be set to false to disable the addition of debug code for :ref:`Debug Toolbar <the-debug-toolbar>`.

    The ``$option`` array is provided primarily to facilitate third-party integrations with
    libraries like Twig.

    Example:

    .. literalinclude:: common_functions/004.php

    For more details, see the :doc:`Views </outgoing/views>` page.

.. php:function:: view_cell($library[, $params = null[, $ttl = 0[, $cacheName = null]]])

    :param string      $library:
    :param null        $params:
    :param integer     $ttl:
    :param string|null $cacheName:
    :returns: View cells are used within views to insert HTML chunks that are managed by other classes.
    :rtype: string

    For more details, see the :doc:`View Cells </outgoing/view_cells>` page.

Miscellaneous Functions
=======================

.. php:function:: app_timezone()

    :returns: The timezone the application has been set to display dates in.
    :rtype: string

    Returns the timezone the application has been set to display dates in.

.. php:function:: csp_script_nonce()

    :returns: The CSP nonce attribute for script tag.
    :rtype: string

    Returns the nonce attribute for a script tag. For example: ``nonce="Eskdikejidojdk978Ad8jf"``.
    See :ref:`content-security-policy`.

.. php:function:: csp_style_nonce()

    :returns: The CSP nonce attribute for style tag.
    :rtype: string

    Returns the nonce attribute for a style tag. For example: ``nonce="Eskdikejidojdk978Ad8jf"``.
    See :ref:`content-security-policy`.

.. php:function:: csrf_token()

    :returns: The name of the current CSRF token.
    :rtype: string

    Returns the name of the current CSRF token.

.. php:function:: csrf_header()

    :returns: The name of the header for current CSRF token.
    :rtype: string

    The name of the header for current CSRF token.

.. php:function:: csrf_hash()

    :returns: The current value of the CSRF hash.
    :rtype: string

    Returns the current CSRF hash value.

.. php:function:: csrf_field()

    :returns: A string with the HTML for hidden input with all required CSRF information.
    :rtype: string

    Returns a hidden input with the CSRF information already inserted::

        <input type="hidden" name="{csrf_token}" value="{csrf_hash}">

.. php:function:: csrf_meta()

    :returns: A string with the HTML for meta tag with all required CSRF information.
    :rtype: string

    Returns a meta tag with the CSRF information already inserted::

        <meta name="{csrf_header}" content="{csrf_hash}">

.. php:function:: force_https($duration = 31536000[, $request = null[, $response = null]])

    :param  int  $duration: The number of seconds browsers should convert links to this resource to HTTPS.
    :param  RequestInterface $request: An instance of the current Request object.
    :param  ResponseInterface $response: An instance of the current Response object.

    Checks to see if the page is currently being accessed via HTTPS. If it is, then
    nothing happens. If it is not, then the user is redirected back to the current URI
    but through HTTPS. Will set the HTTP Strict Transport Security (HTST) header, which instructs
    modern browsers to automatically modify any HTTP requests to HTTPS requests for the ``$duration``.

    .. note:: This function is also used when you set
        ``Config\App:$forceGlobalSecureRequests`` to true.

.. php:function:: function_usable($function_name)

    :param string $function_name: Function to check for
    :returns: true if the function exists and is safe to call, false otherwise.
    :rtype: bool

.. php:function:: is_cli()

    :returns: true if the script is being executed from the command line or false otherwise.
    :rtype: bool

.. php:function:: is_really_writable($file)

    :param string $file: The filename being checked.
    :returns: true if you can write to the file, false otherwise.
    :rtype: bool

.. php:function:: is_windows([$mock = null])

    :param bool|null $mock: If given and is a boolean then it will be used as the return value.
    :rtype: bool

    Detect if platform is running in Windows.

    .. note:: The boolean value provided to $mock will persist in subsequent calls. To reset this
        mock value, the user must pass an explicit ``null`` to the function call. This will
        refresh the function to use auto-detection.

    .. literalinclude:: common_functions/012.php

.. php:function:: log_message($level, $message [, $context])

    :param   string   $level: The level of severity
    :param   string   $message: The message that is to be logged.
    :param   array    $context: An associative array of tags and their values that should be replaced in $message
    :returns: true if was logged successfully or false if there was a problem logging it
    :rtype: bool

    Logs a message using the Log Handlers defined in **app/Config/Logger.php**.

    Level can be one of the following values: **emergency**, **alert**, **critical**, **error**, **warning**,
    **notice**, **info**, or **debug**.

    Context can be used to substitute values in the message string. For full details, see the
    :doc:`Logging Information <logging>` page.

.. php:function:: redirect(string $route)

    :param  string  $route: The route name or Controller::method to redirect the user to.
    :rtype: RedirectResponse

    Returns a RedirectResponse instance allowing you to easily create redirects.
    See :ref:`response-redirect` for details.

.. php:function:: remove_invisible_characters($str[, $urlEncoded = true])

    :param    string    $str: Input string
    :param    bool    $urlEncoded: Whether to remove URL-encoded characters as well
    :returns:    Sanitized string
    :rtype:    string

    This function prevents inserting null characters between ASCII
    characters, like Java\\0script.

    Example:

    .. literalinclude:: common_functions/007.php

.. php:function:: request()

    .. versionadded:: 4.3.0

    :returns:    The shared Request object.
    :rtype:    IncomingRequest|CLIRequest

    This function is a wrapper for ``Services::request()``.

.. php:function:: response()

    .. versionadded:: 4.3.0

    :returns:    The shared Response object.
    :rtype:    Response

    This function is a wrapper for ``Services::response()``.

.. php:function:: route_to($method[, ...$params])

    :param   string       $method: Route name or Controller::method
    :param   int|string   ...$params: One or more parameters to be passed to the route. The last parameter allows you to set the locale.
    :returns: a route path (URI path relative to baseURL)
    :rtype: string

    .. note:: This function requires the controller/method to have a route defined in **app/Config/routes.php**.

    .. important:: ``route_to()`` returns a *route* path, not a full URI path for your site.
        If your **baseURL** contains sub folders, the return value is not the same
        as the URI to link. In that case, just use :php:func:`url_to()` instead.
        See also :ref:`urls-url-structure`.

    Generates a route for you based on a controller::method combination. Will take parameters into effect, if provided.

    .. literalinclude:: common_functions/009.php

    Generates a route for you based on a route name.

    .. literalinclude:: common_functions/010.php

    Since v4.3.0, when you use ``{locale}`` in your route, you can optionally specify the locale value as the last parameter.

    .. literalinclude:: common_functions/011.php

.. php:function:: service($name[, ...$params])

    :param   string   $name: The name of the service to load
    :param   mixed    $params: One or more parameters to pass to the service method.
    :returns: An instance of the service class specified.
    :rtype: mixed

    Provides easy access to any of the :doc:`Services <../concepts/services>` defined in the system.
    This will always return a shared instance of the class, so no matter how many times this is called
    during a single request, only one class instance will be created.

    Example:

    .. literalinclude:: common_functions/008.php

.. php:function:: single_service($name [, ...$params])

    :param   string   $name: The name of the service to load
    :param   mixed    $params: One or more parameters to pass to the service method.
    :returns: An instance of the service class specified.
    :rtype: mixed

    Identical to the **service()** function described above, except that all calls to this
    function will return a new instance of the class, where **service** returns the same
    instance every time.

.. php:function:: slash_item ( $item )

    :param string $item: Config item name
    :returns: The configuration item or null if the item doesn't exist
    :rtype:  string|null

    Fetch a config file item with slash appended (if not empty)

.. php:function:: stringify_attributes($attributes [, $js])

    :param   mixed    $attributes: string, array of key value pairs, or object
    :param   boolean  $js: true if values do not need quotes (Javascript-style)
    :returns: String containing the attribute key/value pairs, comma-separated
    :rtype: string

    Helper function used to convert a string, array, or object of attributes to a string.

================
Global Constants
================

The following constants are always available anywhere within your application.

Core Constants
==============

.. php:const:: APPPATH

    The path to the **app** directory.

.. php:const:: ROOTPATH

    The path to the project root directory. Just above ``APPPATH``.

.. php:const:: SYSTEMPATH

    The path to the **system** directory.

.. php:const:: FCPATH

    The path to the directory that holds the front controller.

.. php:const:: WRITEPATH

    The path to the **writable** directory.

Time Constants
==============

.. php:const:: SECOND

    Equals 1.

.. php:const:: MINUTE

    Equals 60.

.. php:const:: HOUR

    Equals 3600.

.. php:const:: DAY

    Equals 86400.

.. php:const:: WEEK

    Equals 604800.

.. php:const:: MONTH

    Equals 2592000.

.. php:const:: YEAR

    Equals 31536000.

.. php:const:: DECADE

    Equals 315360000.
