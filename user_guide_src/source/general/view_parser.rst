###########
View Parser
###########

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
	:doc:`View renderer </general/view_renderer>` )
	lets them run a little faster.
	However, some developers prefer to use some form of template engine if
	they work with designers who they feel would find some
	confusion working with PHP.

***************************
Using the View Parser Class
***************************

If you have the ``Parser`` as your default renderer, then the parsing will occur
transparently. If you want to work with it more directly, you can access the 
Parser service directly::

	$parser = \Config\Services::renderer();

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
These scripts have a ``.php`` extension, but should not contain any PHP.

Each view parameter (which we refer to as a pseudo-variable) triggers a substitution, 
based on the type of value you provided for it. Pseudo-variables are not
extracted into PHP variables; instead their value is accessed through the pseudo-variable
syntax, where its name is referenced inside braces.
This means that your view parameter names need not be legal PHP variable names.

The Parser class uses an associative array internally, to accumulate pseudo-variable
settings until you call its ``render()``. This means that your pseudo-variable names
need to be unique, or a later parameter setting will over-ride an earlier one.

This also impacts escaping parameter values for different contexts inside your
script. You will have to give each escaped value a unique parameter name.


Parser templates
================

You can use the ``render()`` method to parse (or render) simple templates,
like this::

	$data = array(
		'blog_title' => 'My Blog Title',
		'blog_heading' => 'My Blog Heading'
	);

	echo $parser->setData($data)
		->render('blog_template');

View parameters are passed to ``setData()`` as an associative
array of data to be replaced in the template. In the above example, the
template would contain two variables: {blog_title} and {blog_heading}
The first parameter to ``render()`` contains the name of the :doc:`view
file <../general/views>` (in this example the file would be called
blog_template.php), 


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

***********************
Substitution Variations
***********************

There are three types of substitution supported: simple, looping, and nested.
Substitutions are performed in the same sequence that
pseudo-variables were added.

The **simple substitution** performed by the parser is a one-to-one
replacement of pseudo-variables where the corresponding data parameter
has either a scalar or string value, as in this example::

	$template = '<head><title>{blog_title}</title></head>';
	$data = ['blog_title' => 'My ramblings'];

	echo $parser->setData($data)->renderString($template);

	// Result: <head><title>My ramblings</title></head>

The ``Parser`` takes substitution a lot further with "variable pairs",
used for nested substitutions or looping, and with some advanced
constructs for conditional substitution.

When the parser executes, it will generally

-	handle any conditional substitutions
-	handle any nested/looping substutions
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

	$data = array(
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entries' => array(
			array('title' => 'Title 1', 'body' => 'Body 1'),
			array('title' => 'Title 2', 'body' => 'Body 2'),
			array('title' => 'Title 3', 'body' => 'Body 3'),
			array('title' => 'Title 4', 'body' => 'Body 4'),
			array('title' => 'Title 5', 'body' => 'Body 5')
		)
	);

	echo $parser->setData($data)
		->render('blog_template');

The value for the pseudo-variable ``blog_entries`` is a sequential
array of associative arrays. The outer level does not have keys associated
with each of the nested "rows".

If your "pair" data is coming from a database result, which is already a
multi-dimensional array, you can simply use the database ``getResultArray()``
method::

	$query = $db->query("SELECT * FROM blog");

	$data = array(
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entries' => $query->getResultArray()
	);

	echo $parser->setData($data)
		->render('blog_template');

Nested Substitutions
====================

A nested substitution happens when the value for a pseudo-variable is
an associative array of values, like a record from a database::

	$data = array(
		'blog_title'   => 'My Blog Title',
		'blog_heading' => 'My Blog Heading',
		'blog_entry' => array(
			'title' => 'Title 1', 'body' => 'Body 1'
		)
	);

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

Cascading Data
==============

With both a nested and a loop substitution, you have the option of cascading
data pairs into the inner substitution. 

The following example is not impacted by cascading::

	$template = '{name} lives in {location}{city} on {planet}{/location}.';

	$data = ['name' => 'George', 
		'location' => [ 'city' => 'Red City', 'planet' => 'Mars' ] ];

	echo $parser->setData($data)->renderString($template);
	// Result: George lives in Red City on Mars.

This example gives different results, depending on cascading::

	$template = '{location}{name} lives in {city} on {planet}{/location}.';

	$data = ['name' => 'George', 
		'location' => [ 'city' => 'Red City', 'planet' => 'Mars' ] ];

	echo $parser->setData($data)->renderString($template, ['cascadeData'=>false]);
	// Result: {name} lives in Red City on Mars.

	echo $parser->setData($data)->renderString($template, ['cascadeData'=>true]);
	// Result: George lives in Red City on Mars.


***********
Usage Notes
***********

If you include substitution parameters that are not referenced in your
template, they are ignored::

	$template = 'Hello, {firstname} {lastname}';
	$data = array(
		'title' => 'Mr',
		'firstname' => 'John',
		'lastname' => 'Doe'
	);
	echo $parser->setData($data)
		->renderString($template);

	// Result: Hello, John Doe

If you do not include a substitution parameter that is referenced in your
template, the original pseudo-variable is shown in the result::

	$template = 'Hello, {firstname} {initials} {lastname}';
	$data = array(
		'title' => 'Mr',
		'firstname' => 'John',
		'lastname' => 'Doe'
	);
	echo $parser->setData($data)
		->renderString($template);

	// Result: Hello, John {initials} Doe

If you provide a string substitution parameter when an array is expected,
i.e. for a variable pair, the substitution is done for the opening variable
pair tag, but the closing variable pair tag is not rendered properly::

	$template = 'Hello, {firstname} {lastname} ({degrees}{degree} {/degrees})';
	$data = array(
		'degrees' => 'Mr',
		'firstname' => 'John',
		'lastname' => 'Doe',
		'titles' => array(
			array('degree' => 'BSc'),
			array('degree' => 'PhD')
		)
	);
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

	$data = array(
		'menuitems' => array(
			array('title' => 'First Link', 'link' => '/first'),
			array('title' => 'Second Link', 'link' => '/second'),
		)
	);
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
	$data1 = array(
		array('title' => 'First Link', 'link' => '/first'),
		array('title' => 'Second Link', 'link' => '/second'),
	);

	foreach ($data1 as $menuitem)
	{
		$temp .= $parser->setData($menuItem)->renderString();
	}

	$template = '<ul>{menuitems}</ul>';
	$data = array(
		'menuitems' => $temp
	);
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


