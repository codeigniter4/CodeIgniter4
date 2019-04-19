##############################
Global Functions and Constants
##############################

CodeIgniter uses provides a few functions and variables that are globally defined, and are available to you at any point.
These do not require loading any additional libraries or helpers.

.. contents::
    :local:
    :depth: 2


================
Global Functions
================

Service Accessors
=================

.. php:function:: cache ( [$key] )

    :param  string $key: The cache name of the item to retrieve from cache (Optional)
    :returns: Either the cache object, or the item retrieved from the cache
    :rtype: mixed

    If no $key is provided, will return the Cache engine instance. If a $key
    is provided, will return the value of $key as stored in the cache currently,
    or null if no value is found.

    Examples::

     	$foo = cache('foo');
    	$cache = cache();

.. php:function:: env ( $key[, $default=null])

	:param string $key: The name of the environment variable to retrieve
	:param mixed  $default: The default value to return if no value is found.
	:returns: The environment variable, the default value, or null.
	:rtype: mixed

	Used to retrieve values that have previously been set to the environment,
	or return a default value if it is not found. Will format boolean values
	to actual booleans instead of string representations.

	Especially useful when used in conjunction with .env files for setting
	values that are specific to the environment itself, like database
	settings, API keys, etc.

.. php:function:: esc ( $data, $context='html' [, $encoding])

	:param   string|array   $data: The information to be escaped.
	:param   string   $context: The escaping context. Default is 'html'.
	:param   string   $encoding: The character encoding of the string.
	:returns: The escaped data.
	:rtype: mixed

	Escapes data for inclusion in web pages, to help prevent XSS attacks.
	This uses the Zend Escaper library to handle the actual filtering of the data.

	If $data is a string, then it simply escapes and returns it.
	If $data is an array, then it loops over it, escaping each 'value' of the key/value pairs.

	Valid context values: html, js, css, url, attr, raw, null

.. php:function:: helper( $filename )

	:param   string|array  $filename: The name of the helper file to load, or an array of names.

	Loads a helper file.

	For full details, see the :doc:`helpers` page.

.. php:function:: lang($line[, $args[, $locale ]])

	:param string $line: The line of text to retrieve
	:param array  $args: An array of data to substitute for placeholders.
	:param string $locale: Specify a different locale to be used instead of default one.

	Retrieves a locale-specific file based on an alias string.

	For more information, see the :doc:`Localization </outgoing/localization>` page.

.. php:function:: old( $key[, $default = null, [, $escape = 'html' ]] )

	:param string $key: The name of the old form data to check for.
	:param mixed  $default: The default value to return if $key doesn't exist.
	:param mixed  $escape: An `escape <#esc>`_ context or false to disable it.
	:returns: The value of the defined key, or the default value.
	:rtype: mixed

	Provides a simple way to access "old input data" from submitting a form.

	Example::

		// in controller, checking form submittal
		if (! $model->save($user))
		{
			// 'withInput' is what specifies "old data"
			// should be saved.
			return redirect()->back()->withInput();
		}

		// In the view
		<input type="email" name="email" value="<?= old('email') ?>">
		// Or with arrays
		<input type="email" name="user[email]" value="<?= old('user.email') ?>">

.. note:: If you are using the :doc:`form helper </helpers/form_helper>`, this feature is built-in. You only
		need to use this function when not using the form helper.

.. php:function:: session( [$key] )

	:param string $key: The name of the session item to check for.
	:returns: An instance of the Session object if no $key, the value found in the session for $key, or null.
	:rtype: mixed

	Provides a convenient way to access the session class and to retrieve a
	stored value. For more information, see the :doc:`Sessions </libraries/sessions>` page.

.. php:function:: timer( [$name] )

	:param string $name: The name of the benchmark point.
	:returns: The Timer instance
	:rtype: CodeIgniter\Debug\Timer

	A convenience method that provides quick access to the Timer class. You can pass in the name
	of a benchmark point as the only parameter. This will start timing from this point, or stop
	timing if a timer with this name is already running.

	Example::

		// Get an instance
		$timer = timer();

		// Set timer start and stop points
		timer('controller_loading');    // Will start the timer
		. . .
		timer('controller_loading');    // Will stop the running timer

.. php:function:: view ($name [, $data [, $options ]])

	:param   string   $name: The name of the file to load
	:param   array    $data: An array of key/value pairs to make available within the view.
	:param   array    $options: An array of options that will be passed to the rendering class.
	:returns: The output from the view.
	:rtype: string

	Grabs the current RendererInterface-compatible class
	and tells it to render the specified view. Simply provides
	a convenience method that can be used in Controllers,
	libraries, and routed closures.

	Currently, only one option is available for use within the `$options` array, `saveData` which specifies
	that data will persistent between multiple calls to `view()` within the same request. By default, the
	data for that view is forgotten after displaying that single view file.

	The $option array is provided primarily to facilitate third-party integrations with
	libraries like Twig.

	Example::

		$data = ['user' => $user];

		echo view('user_profile', $data);

	For more details, see the :doc:`Views </outgoing/views>` page.

Miscellaneous Functions
=======================

.. php:function:: csrf_token ()

	:returns: The name of the current CSRF token.
	:rtype: string

	Returns the name of the current CSRF token.

.. php:function:: csrf_hash ()

	:returns: The current value of the CSRF hash.
	:rtype: string

	Returns the current CSRF hash value.

.. php:function:: csrf_field ()

	:returns: A string with the HTML for hidden input with all required CSRF information.
	:rtype: string

	Returns a hidden input with the CSRF information already inserted:

		<input type="hidden" name="{csrf_token}" value="{csrf_hash}">

.. php:function:: force_https ( $duration = 31536000 [, $request = null [, $response = null]] )

	:param  int  $duration: The number of seconds browsers should convert links to this resource to HTTPS.
	:param  RequestInterface $request: An instance of the current Request object.
	:param  ResponseInterface $response: An instance of the current Response object.

	Checks to see if the page is currently being accessed via HTTPS. If it is, then
	nothing happens. If it is not, then the user is redirected back to the current URI
	but through HTTPS. Will set the HTTP Strict Transport Security header, which instructs
	modern browsers to automatically modify any HTTP requests to HTTPS requests for the $duration.

.. php:function:: is_cli ()

	:returns: TRUE if the script is being executed from the command line or FALSE otherwise.
	:rtype: bool

.. php:function:: log_message ($level, $message [, $context])

	:param   string   $level: The level of severity
	:param   string   $message: The message that is to be logged.
	:param   array    $context: An associative array of tags and their values that should be replaced in $message
	:returns: TRUE if was logged successfully or FALSE if there was a problem logging it
	:rtype: bool

	Logs a message using the Log Handlers defined in **app/Config/Logger.php**.

	Level can be one of the following values: **emergency**, **alert**, **critical**, **error**, **warning**,
	**notice**, **info**, or **debug**.

	Context can be used to substitute values in the message string. For full details, see the
	:doc:`Logging Information <logging>` page.

.. php:function:: redirect( string $uri )

	:param  string  $uri: The URI to redirect the user to.

	Returns a RedirectResponse instance allowing you to easily create redirects::

		// Go back to the previous page
		return redirect()->back();

		// Go to specific UI
		return redirect()->to('/admin');

		// Go to a named/reverse-routed URI
		return redirect()->route('named_route');

		// Keep the old input values upon redirect so they can be used by the `old()` function
		return redirect()->back()->withInput();

		// Set a flash message
		return redirect()->back()->with('foo', 'message');

	When passing a URI into the function, it is treated as a reverse-route request, not a relative/full URI, treating
        it the same as using redirect()->route()::

                // Go to a named/reverse-routed URI
		return redirect('named_route');

.. php:function:: remove_invisible_characters($str[, $urlEncoded = TRUE])

	:param	string	$str: Input string
	:param	bool	$urlEncoded: Whether to remove URL-encoded characters as well
	:returns:	Sanitized string
	:rtype:	string

	This function prevents inserting NULL characters between ASCII
	characters, like Java\\0script.

	Example::

		remove_invisible_characters('Java\\0script');
		// Returns: 'Javascript'

.. php:function:: route_to ( $method [, ...$params] )

	:param   string   $method: The named route alias, or name of the controller/method to match.
	:param   mixed   $params: One or more parameters to be passed to be matched in the route.

	Generates a relative URI for you based on either a named route alias, or a controller::method
	combination. Will take parameters into effect, if provided.

	For full details, see the :doc:`/incoming/routing` page.

.. php:function:: service ( $name [, ...$params] )

	:param   string   $name: The name of the service to load
	:param   mixed    $params: One or more parameters to pass to the service method.
	:returns: An instance of the service class specified.
	:rtype: mixed

	Provides easy access to any of the :doc:`Services <../concepts/services>` defined in the system.
	This will always return a shared instance of the class, so no matter how many times this is called
	during a single request, only one class instance will be created.

	Example::

		$logger = service('logger');
		$renderer = service('renderer', APPPATH.'views/');

.. php:function:: single_service ( $name [, ...$params] )

	:param   string   $name: The name of the service to load
	:param   mixed    $params: One or more parameters to pass to the service method.
	:returns: An instance of the service class specified.
	:rtype: mixed

	Identical to the **service()** function described above, except that all calls to this
	function will return a new instance of the class, where **service** returns the same
	instance every time.

.. php:function:: stringify_attributes ( $attributes [, $js] )

	:param   mixed    $attributes: string, array of key value pairs, or object
	:param   boolean  $js: TRUE if values do not need quotes (Javascript-style)
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
