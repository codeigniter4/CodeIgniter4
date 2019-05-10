###########
View Parser
###########

.. contents::
    :local:
    :depth: 2

The View Parser can perform simple text substitution for
pseudo-variables contained within your view files.
It can parse simple variables or variable tag pairs.

Pseudo-variable names or control constructs are enclosed in braces, like this::

	<html>
	<head>
		<title>{blog_title}</title>
	</head>
	<body>
		<h3>{blog_heading}</h3>

		{blog_entries}
			<h5>{title}</h5>
			<p>{body}</p>
		{/blog_entries}

	</body>
	</html>

These variables are not actual PHP variables, but rather plain text
representations that allow you to eliminate PHP from your templates
(view files).

.. note:: CodeIgniter does **not** require you to use this class since
	using pure PHP in your view pages (for instance using the
	:doc:`View renderer </outgoing/view_renderer>` )
	lets them run a little faster.
	However, some developers prefer to use some form of template engine if
	they work with designers who they feel would find some
	confusion working with PHP.

***************************
Using the View Parser Class
***************************

The simplest method to load the parser class is through its service::

	$parser = \Config\Services::parser();

Alternately, if you are not using the ``Parser`` class as your default renderer, you
can instantiate it directly::

	$parser = new \CodeIgniter\View\Parser();

Then you can use any of the three standard rendering methods that it provides:
**render(viewpath, options, save)**, **setVar(name, value, context)** and
**setData(data, context)**. You will also be able to specify delimiters directly,
through the **setDelimiters(left,right)** method.

Using the ``Parser``, your view templates are processed only by the Parser
itself, and not like a conventional view PHP script. PHP code in such a script
is ignored by the parser, and only substitutions are performed.

This is purposeful: view files with no PHP.

What It Does
============

The ``Parser`` class processes "PHP/HTML scripts" stored in the application's view path.
These scripts have a ``.php`` extension, but can not contain any PHP.

Each view parameter (which we refer to as a pseudo-variable) triggers a substitution,
based on the type of value you provided for it. Pseudo-variables are not
extracted into PHP variables; instead their value is accessed through the pseudo-variable
syntax, where its name is referenced inside braces.

The Parser class uses an associative array internally, to accumulate pseudo-variable
settings until you call its ``render()``. This means that your pseudo-variable names
need to be unique, or a later parameter setting will over-ride an earlier one.

This also impacts escaping parameter values for different contexts inside your
script. You will have to give each escaped value a unique parameter name.

Parser templates
================

You can use the ``render()`` method to parse (or render) simple templates,
like this::

	$data = [
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading'
	];

	echo $parser->setData($data)
	             ->render('blog_template');

View parameters are passed to ``setData()`` as an associative
array of data to be replaced in the template. In the above example, the
template would contain two variables: {blog_title} and {blog_heading}
The first parameter to ``render()`` contains the name of the :doc:`view
file </outgoing/views>` (in this example the file would be called blog_template.php),

Parser Configuration Options
============================

Several options can be passed to the ``render()`` or ``renderString()`` methods.

-   ``cache`` - the time in seconds, to save a view's results; ignored for renderString()
-   ``cache_name`` - the ID used to save/retrieve a cached view result; defaults to the viewpath;
		ignored for renderString()
-   ``saveData`` - true if the view data parameters should be retained for subsequent calls;
		default is **false**
-	``cascadeData`` - true if pseudo-variable settings should be passed on to nested
		substitutions; default is **true**

::

	echo $parser->render('blog_template', [
		'cache'      => HOUR,
		'cache_name' => 'something_unique',
	]);

***********************
Substitution Variations
***********************

There are three types of substitution supported: simple, looping, and nested.
Substitutions are performed in the same sequence that pseudo-variables were added.

The **simple substitution** performed by the parser is a one-to-one
replacement of pseudo-variables where the corresponding data parameter
has either a scalar or string value, as in this example::

	$template = '<head><title>{blog_title}</title></head>';
	$data     = ['blog_title' => 'My ramblings'];

	echo $parser->setData($data)->renderString($template);

	// Result: <head><title>My ramblings</title></head>

The ``Parser`` takes substitution a lot further with "variable pairs",
used for nested substitutions or looping, and with some advanced
constructs for conditional substitution.

When the parser executes, it will generally

-	handle any conditional substitutions
-	handle any nested/looping substitutions
-	handle the remaining single substitutions

Loop Substitutions
==================

A loop substitution happens when the value for a pseudo-variable is
a sequential array of arrays, like an array of row settings.

The above example code allows simple variables to be replaced. What if
you would like an entire block of variables to be repeated, with each
iteration containing new values? Consider the template example we showed
at the top of the page::

	<html>
	<head>
		<title>{blog_title}</title>
	</head>
	<body>
		<h3>{blog_heading}</h3>

		{blog_entries}
			<h5>{title}</h5>
			<p>{body}</p>
		{/blog_entries}

	</body>
	</html>

In the above code you'll notice a pair of variables: {blog_entries}
data... {/blog_entries}. In a case like this, the entire chunk of data
between these pairs would be repeated multiple times, corresponding to
the number of rows in the "blog_entries" element of the parameters array.

Parsing variable pairs is done using the identical code shown above to
parse single variables, except, you will add a multi-dimensional array
corresponding to your variable pair data. Consider this example::

	$data = [
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entries' => [
			['title' => 'Title 1', 'body' => 'Body 1'],
			['title' => 'Title 2', 'body' => 'Body 2'],
			['title' => 'Title 3', 'body' => 'Body 3'],
			['title' => 'Title 4', 'body' => 'Body 4'],
			['title' => 'Title 5', 'body' => 'Body 5']
		]
	];

	echo $parser->setData($data)
	             ->render('blog_template');

The value for the pseudo-variable ``blog_entries`` is a sequential
array of associative arrays. The outer level does not have keys associated
with each of the nested "rows".

If your "pair" data is coming from a database result, which is already a
multi-dimensional array, you can simply use the database ``getResultArray()``
method::

	$query = $db->query("SELECT * FROM blog");

	$data = [
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entries' => $query->getResultArray()
	];

	echo $parser->setData($data)
	             ->render('blog_template');

If the array you are trying to loop over contains objects instead of arrays,
the parser will first look for an ``asArray`` method on the object. If it exists,
that method will be called and the resulting array is then looped over just as
described above. If no ``asArray`` method exists, the object will be cast as
an array and its public properties will be made available to the Parser.

This is especially useful with the Entity classes, which has an asArray method
that returns all public and protected properties (minus the _options property) and
makes them available to the Parser.

Nested Substitutions
====================

A nested substitution happens when the value for a pseudo-variable is
an associative array of values, like a record from a database::

	$data = [
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entry'   => [
			'title' => 'Title 1', 'body' => 'Body 1'
		]
	];

	echo $parser->setData($data)
	             ->render('blog_template');

The value for the pseudo-variable ``blog_entry`` is an associative
array. The key/value pairs defined inside it will be exposed inside
the variable pair loop for that variable.

A ``blog_template`` that might work for the above::

	<h1>{blog_title} - {blog_heading}</h1>
	{blog_entry}
		<div>
			<h2>{title}</h2>
			<p>{body}{/p}
		</div>
	{/blog_entry}

If you would like the other pseudo-variables accessible inside the "blog_entry"
scope, then make sure that the "cascadeData" option is set to true.

Comments
========

You can place comments in your templates that will be ignored and removed during parsing by wrapping the
comments in a ``{#  #}`` symbols.

::

	{# This comment is removed during parsing. #}
	{blog_entry}
		<div>
			<h2>{title}</h2>
			<p>{body}{/p}
		</div>
	{/blog_entry}

Cascading Data
==============

With both a nested and a loop substitution, you have the option of cascading
data pairs into the inner substitution.

The following example is not impacted by cascading::

	$template = '{name} lives in {location}{city} on {planet}{/location}.';

	$data = [
		'name'     => 'George',
		'location' => [ 'city' => 'Red City', 'planet' => 'Mars' ]
	];

	echo $parser->setData($data)->renderString($template);
	// Result: George lives in Red City on Mars.

This example gives different results, depending on cascading::

	$template = '{location}{name} lives in {city} on {planet}{/location}.';

	$data = [
		'name'     => 'George',
		'location' => [ 'city' => 'Red City', 'planet' => 'Mars' ]
	];

	echo $parser->setData($data)->renderString($template, ['cascadeData'=>false]);
	// Result: {name} lives in Red City on Mars.

	echo $parser->setData($data)->renderString($template, ['cascadeData'=>true]);
	// Result: George lives in Red City on Mars.

Preventing Parsing
==================

You can specify portions of the page to not be parsed with the ``{noparse}{/noparse}`` tag pair. Anything in this
section will stay exactly as it is, with no variable substitution, looping, etc, happening to the markup between the brackets.

::

	{noparse}
		<h1>Untouched Code</h1>
	{/noparse}

Conditional Logic
=================

The Parser class supports some basic conditionals to handle ``if``, ``else``, and ``elseif`` syntax. All ``if``
blocks must be closed with an ``endif`` tag::

	{if $role=='admin'}
		<h1>Welcome, Admin!</h1>
	{endif}

This simple block is converted to the following during parsing::

	<?php if ($role=='admin'): ?>
		<h1>Welcome, Admin!</h1>
	<?php endif ?>

All variables used within if statements must have been previously set with the same name. Other than that, it is
treated exactly like a standard PHP conditional, and all standard PHP rules would apply here. You can use any
of the comparison operators you would normally, like ``==``, ``===``, ``!==``, ``<``, ``>``, etc.

::

	{if $role=='admin'}
		<h1>Welcome, Admin</h1>
	{elseif $role=='moderator'}
		<h1>Welcome, Moderator</h1>
	{else}
		<h1>Welcome, User</h1>
	{endif}

.. note:: In the background, conditionals are parsed using an **eval()**, so you must ensure that you take
	care with the user data that is used within conditionals, or you could open your application up to security risks.

Escaping Data
=============

By default, all variable substitution is escaped to help prevent XSS attacks on your pages. CodeIgniter's ``esc`` method
supports several different contexts, like general **html**, when it's in an HTML **attr*, in **css**, etc. If nothing
else is specified, the data will be assumed to be in an HTML context. You can specify the context used by using the **esc**
filter::

	{ user_styles | esc(css) }
	<a href="{ user_link | esc(attr) }">{ title }</a>

There will be times when you absolutely need something to used and NOT escaped. You can do this by adding exclamation
marks to the opening and closing braces::

	{! unescaped_var !}

Filters
=======

Any single variable substitution can have one or more filters applied to it to modify the way it is presented. These
are not intended to drastically change the output, but provide ways to reuse the same variable data but with different
presentations. The **esc** filter discussed above is one example. Dates are another common use case, where you might
need to format the same data differently in several sections on the same page.

Filters are commands that come after the pseudo-variable name, and are separated by the pipe symbol, ``|``::

	// -55 is displayed as 55
	{ value|abs  }

If the parameter takes any arguments, they must be separated by commas and enclosed in parentheses::

	{ created_at|date(Y-m-d) }

Multiple filters can be applied to the value by piping multiple ones together. They are processed in order, from
left to right::

	{ created_at|date_modify(+5 days)|date(Y-m-d) }

Provided Filters
----------------

The following filters are available when using the parser:

+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ **Filter**    + **Arguments**       + **Description**                                              + **Example**                         +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ abs           +                     + Displays the absolute value of a number.                     + { v|abs }                           +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ capitalize    +                     + Displays the string in sentence case: all lowercase          + { v|capitalize}                     +
+               +                     + with firstletter capitalized.                                +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ date          + format (Y-m-d)      + A PHP **date**-compatible formatting string.                 + { v|date(Y-m-d) }                   +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ date_modify   + value to add        + A **strtotime** compatible string to modify the date,        + { v|date_modify(+1 day) }           +
+               + / subtract          + like ``+5 day`` or ``-1 week``.                              +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ default       + default value       + Displays the default value if the variable is empty or       + { v|default(just in case) }         +
+               +                     + undefined.                                                   +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ esc           + html, attr, css, js + Specifies the context to escape the data.                    + { v|esc(attr) }                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ excerpt       + phrase, radius      + Returns the text within a radius of words from a given       + { v|excerpt(green giant, 20) }      +
+               +                     + phrase. Same as **excerpt** helper function.                 +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ highlight     + phrase              + Highlights a given phrase within the text using              + { v|highlight(view parser) }        +
+               +                     + '<mark></mark>' tags.                                        +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ highlight_code+                     + Highlights code samples with HTML/CSS.                       + { v|highlight_code }                +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ limit_chars   + limit               + Limits the number of chracters to $limit.                    + { v|limit_chars(100) }              +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ limit_words   + limit               + Limits the number of words to $limit.                        + { v|limit_words(20) }               +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ local_currency+ currency, locale    + Displays a localized version of a currency. "currency"       + { v|local_currency(EUR,en_US) }     +
+               +                     + valueis any 3-letter ISO 4217 currency code.                 +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ local_number  + type, precision,    + Displays a localized version of a number. "type" can be      + { v|local_number(decimal,2,en_US) } +
+               + locale              + one of: decimal, currency, percent, scientific, spellout,    +                                     +
+               +                     + ordinal, duration.                                           +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ lower         +                     + Converts a string to lowercase.                              + { v|lower }                         +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ nl2br         +                     + Replaces all newline characters (\n) to an HTML <br/> tag.   + { v|nl2br }                         +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ number_format + places              + Wraps PHP **number_format** function for use within the      + { v|number_format(3) }              +
+               +                     + parser.                                                      +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ prose         +                     + Takes a body of text and uses the **auto_typography()**      + { v|prose }                         +
+               +                     + method to turn it into prettier, easier-to-read, prose.      +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ round         + places, type        + Rounds a number to the specified places. Types of **ceil**   + { v|round(3) } { v|round(ceil) }    +
+               +                     + and **floor** can be passed to use those functions instead.  +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ strip_tags    + allowed chars       + Wraps PHP **strip_tags**. Can accept a string of allowed     + { v|strip_tags(<br>) }              +
+               +                     + tags.                                                        +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ title         +                     + Displays a "title case" version of the string, with all      + { v|title }                         +
+               +                     + lowercase, and each word capitalized.                        +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+ upper         +                     + Displays the string in all uppercase.                        + { v|upper }                         +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+
+               +                     +                                                              +                                     +
+---------------+---------------------+--------------------------------------------------------------+-------------------------------------+

See `PHP's NumberFormatter <http://php.net/manual/en/numberformatter.create.php>`_ for details relevant to the
"local_number" filter.

Custom Filters
--------------

You can easily create your own filters by editing **app/Config/View.php** and adding new entries to the
``$filters`` array. Each key is the name of the filter is called by in the view, and its value is any valid PHP
callable::

	public $filters = [
		'abs'        => '\CodeIgniter\View\Filters::abs',
		'capitalize' => '\CodeIgniter\View\Filters::capitalize',
	];

PHP Native functions as Filters
-------------------------------

You can easily use native php function as filters by editing **app/Config/View.php** and adding new entries to the
``$filters`` array.Each key is the name of the native PHP function is called by in the view, and its value is any valid native PHP
function prefixed with::

	public $filters = [
		'str_repeat' => '\str_repeat',
	];

Parser Plugins
==============

Plugins allow you to extend the parser, adding custom features for each project. They can be any PHP callable, making
them very simple to implement. Within templates, plugins are specified by ``{+ +}`` tags::

	{+ foo +} inner content {+ /foo +}

This example shows a plugin named **foo**. It can manipulate any of the content between its opening and closing tags.
In this example, it could work with the text " inner content ". Plugins are processed before any pseudo-variable
replacements happen.

While plugins will often consist of tag pairs, like shown above, they can also be a single tag, with no closing tag::

	{+ foo +}

Opening tags can also contain parameters that can customize how the plugin works. The parameters are represented as
key/value pairs::

	{+ foo bar=2 baz="x y" }

Parameters can also be single values::

	{+ include somefile.php +}

Provided Plugins
----------------

The following plugins are available when using the parser:

==================== ========================== ================================================================================== ================================================================
Plugin               Arguments                  Description                                                           			   Example
==================== ========================== ================================================================================== ================================================================
currentURL                                      Alias for the current_url helper function.                                         {+ currentURL +}
previousURL                                     Alias for the previous_url helper function.                           		       {+ previousURL +}
siteURL                                         Alias for the site_url helper function.                                            {+ siteURL "login" +}
mailto               email, title, attributes   Alias for the mailto helper function.                                 		       {+ mailto email=foo@example.com title="Stranger Things" +}
safe_mailto          email, title, attributes   Alias for the safe_mailto helper function.                            		       {+ safe_mailto email=foo@example.com title="Stranger Things" +}
lang                 language string            Alias for the lang helper function.                                    		       {+ lang number.terabyteAbbr +}
validation_errors    fieldname(optional)        Returns either error string for the field (if specified) or all validation errors. {+ validation_errors +} , {+ validation_errors field="email" +}
route                route name                 Alias for the route_to helper function.                                            {+ route "login" +}
==================== ========================== ================================================================================== ================================================================

Registering a Plugin
--------------------

At its simplest, all you need to do to register a new plugin and make it ready for use is to add it to the
**app/Config/View.php**, under the **$plugins** array. The key is the name of the plugin that is
used within the template file. The value is any valid PHP callable, including static class methods, and closures::

	public $plugins = [
		'foo'	=> '\Some\Class::methodName',
		'bar'	=> function($str, array $params=[]) {
			return $str;
		},
	];

If the callable is on its own, it is treated as a single tag, not a open/close one. It will be replaced by
the return value from the plugin::

	public $plugins = [
		'foo'	=> '\Some\Class::methodName'
	];

	// Tag is replaced by the return value of Some\Class::methodName static function.
	{+ foo +}

If the callable is wrapped in an array, it is treated as an open/close tag pair that can operate on any of
the content between its tags::

	public $plugins = [
		'foo' => ['\Some\Class::methodName']
	];

	{+ foo +} inner content {+ /foo +}

***********
Usage Notes
***********

If you include substitution parameters that are not referenced in your
template, they are ignored::

	$template = 'Hello, {firstname} {lastname}';
	$data = [
		'title' => 'Mr',
		'firstname' => 'John',
		'lastname' => 'Doe'
	];
	echo $parser->setData($data)
	             ->renderString($template);

	// Result: Hello, John Doe

If you do not include a substitution parameter that is referenced in your
template, the original pseudo-variable is shown in the result::

	$template = 'Hello, {firstname} {initials} {lastname}';
	$data = [
		'title'     => 'Mr',
		'firstname' => 'John',
		'lastname'  => 'Doe'
	];
	echo $parser->setData($data)
	             ->renderString($template);

	// Result: Hello, John {initials} Doe

If you provide a string substitution parameter when an array is expected,
i.e. for a variable pair, the substitution is done for the opening variable
pair tag, but the closing variable pair tag is not rendered properly::

	$template = 'Hello, {firstname} {lastname} ({degrees}{degree} {/degrees})';
	$data = [
		'degrees'   => 'Mr',
		'firstname' => 'John',
		'lastname'  => 'Doe',
		'titles'    => [
			['degree' => 'BSc'],
			['degree' => 'PhD']
		]
	];
	echo $parser->setData($data)
	             ->renderString($template);

	// Result: Hello, John Doe (Mr{degree} {/degrees})

View Fragments
==============

You do not have to use variable pairs to get the effect of iteration in
your views. It is possible to use a view fragment for what would be inside
a variable pair, and to control the iteration in your controller instead
of in the view.

An example with the iteration controlled in the view::

	$template = '<ul>{menuitems}
		<li><a href="{link}">{title}</a></li>
	{/menuitems}</ul>';

	$data = [
		'menuitems' => [
			['title' => 'First Link', 'link' => '/first'],
			['title' => 'Second Link', 'link' => '/second'],
		]
	];
	echo $parser->setData($data)
	             ->renderString($template);

Result::

	<ul>
		<li><a href="/first">First Link</a></li>
		<li><a href="/second">Second Link</a></li>
	</ul>

An example with the iteration controlled in the controller,
using a view fragment::

	$temp = '';
	$template1 = '<li><a href="{link}">{title}</a></li>';
	$data1 = [
		['title' => 'First Link', 'link' => '/first'],
		['title' => 'Second Link', 'link' => '/second'],
	];

	foreach ($data1 as $menuitem)
	{
		$temp .= $parser->setData($menuItem)->renderString();
	}

	$template = '<ul>{menuitems}</ul>';
	$data = [
		'menuitems' => $temp
	];
	echo $parser->setData($data)
	             ->renderString($template);

Result::

	<ul>
		<li><a href="/first">First Link</a></li>
		<li><a href="/second">Second Link</a></li>
	</ul>

***************
Class Reference
***************

.. php:class:: CodeIgniter\\View\\Parser

	.. php:method:: render($view[, $options[, $saveData=false]]])

		:param  string  $view: File name of the view source
		:param  array   $options: Array of options, as key/value pairs
		:param  boolean $saveData: If true, will save data for use with any other calls, if false, will clean the data after rendering the view.
		:returns: The rendered text for the chosen view
		:rtype: string

    		Builds the output based upon a file name and any data that has already been set::

			echo $parser->render('myview');

        Options supported:

	        -   ``cache`` - the time in seconds, to save a view's results
	        -   ``cache_name`` - the ID used to save/retrieve a cached view result; defaults to the viewpath
	        -   ``cascadeData`` - true if the data pairs in effect when a nested or loop substitution occurs should be propagated
	        -   ``saveData`` - true if the view data parameter should be retained for subsequent calls
	        -   ``leftDelimiter`` - the left delimiter to use in pseudo-variable syntax
	        -   ``rightDelimiter`` - the right delimiter to use in pseudo-variable syntax

		Any conditional substitutions are performed first, then remaining
		substitutions are performed for each data pair.

	.. php:method:: renderString($template[, $options[, $saveData=false]]])

		:param  string  $template: View source provided as a string
    		:param  array   $options: Array of options, as key/value pairs
    		:param  boolean $saveData: If true, will save data for use with any other calls, if false, will clean the data after rendering the view.
    		:returns: The rendered text for the chosen view
    		:rtype: string

    		Builds the output based upon a provided template source and any data that has already been set::

			echo $parser->render('myview');

        Options supported, and behavior, as above.

	.. php:method:: setData([$data[, $context=null]])

		:param  array   $data: Array of view data strings, as key/value pairs
    		:param  string  $context: The context to use for data escaping.
    		:returns: The Renderer, for method chaining
    		:rtype: CodeIgniter\\View\\RendererInterface.

    		Sets several pieces of view data at once::

			$renderer->setData(['name'=>'George', 'position'=>'Boss']);

        Supported escape contexts: html, css, js, url, or attr or raw.
		If 'raw', no escaping will happen.

	.. php:method:: setVar($name[, $value=null[, $context=null]])

		:param  string  $name: Name of the view data variable
    		:param  mixed   $value: The value of this view data
    		:param  string  $context: The context to use for data escaping.
    		:returns: The Renderer, for method chaining
    		:rtype: CodeIgniter\\View\\RendererInterface.

    		Sets a single piece of view data::

			$renderer->setVar('name','Joe','html');

        Supported escape contexts: html, css, js, url, attr or raw.
		If 'raw', no escaping will happen.

	.. php:method:: setDelimiters($leftDelimiter = '{', $rightDelimiter = '}')

		:param  string  $leftDelimiter: Left delimiter for substitution fields
    		:param  string  $rightDelimiter: right delimiter for substitution fields
    		:returns: The Renderer, for method chaining
    		:rtype: CodeIgniter\\View\\RendererInterface.

    		Over-ride the substitution field delimiters::

			$renderer->setDelimiters('[',']');
