#############
View Renderer
#############

The ``view()`` function is a convenience method that grabs an instance of the ``renderer`` service,
sets the data, and renders the view. While this is often exactly what you want, you may find times where you
want to work with it more directly. In that case you can access the View service directly::

	$renderer = \Config\Services::renderer();

.. important:: You should create services only within controllers. If you need access to the View class
	from a library, you should set that as a dependency in the constructor.

Then you can use any of the three standard methods that it provides: 
**render(viewpath, options, save)**, **setVar(name, value, context)** and **setData(data, context)**.

Method Chaining
===============

The `setVar()` and `setData()` methods are chainable, allowing you to combine a number of different calls together in a chain::

	service('renderer')->setVar('one', $one)
	                   ->setVar('two', $two)
	                   ->render('myView');

Escaping Data
=============

When you pass data to the ``setVar()`` and ``setData()`` functions you have the option to escape the data to protect
against cross-site scripting attacks. As the last parameter in either method, you can pass the desired context to
escape the data for. See below for context descriptions.

If you don't want the data to be escaped, you can pass `null` or `raw` as the final parameter to each function::

	$renderer->setVar('one', $one, 'raw');

If you choose not to escape data, or you are passing in an object instance, you can manually escape the data within
the view with the ``esc()`` function. The first parameter is the string to escape. The second parameter is the
context to escape the data for (see below)::

	<?= esc($object->getStat()) ?>

Escaping Contexts
-----------------

By default, the ``esc()`` and, in turn, the ``setVar()`` and ``setData()`` functions assume that the data you want to
escape is intended to be used within standard HTML. However, if the data is intended for use in Javascript, CSS,
or in an href attribute, you would need different escaping rules to be effective. You can pass in the name of the
context as the second parameter. Valid contexts are 'html', 'js', 'css', 'url', and 'attr'::

	<a href="<?= esc($url, 'url') ?>" data-foo="<?= esc($bar, 'attr') ?>">Some Link</a>

	<script>
		var siteName = '<?= esc($siteName, 'js') ?>';
	</script>

	<style>
		body {
			background-color: <?= esc('bgColor', 'css') ?>
		}
	</style>

***************
Class Reference
***************

.. php:interface:: CodeIgniter\\View\\RendererableInterface

	.. php:method:: render($view[, $options[, $saveData=false]]])

		:param  string  $view: File name of the view source 
		:param  array   $options: Array of options, as key/value pairs
		:param  boolean $saveData: If true, will save data for use with any other calls, if false, will clean the data after rendering the view.
		:returns: The rendered text for the chosen view
		:rtype: string

		Builds the output based upon a file name and any data that has already been set::

			echo $renderer->render('myview');

		Options supported:

	        -   ``cache`` - the time in seconds, to save a view's results
	        -   ``cache_name`` - the ID used to save/retrieve a cached view result; defaults to the viewpath
	        -   ``saveData`` - true if the view data parameter should be retained for subsequent calls


	.. php:method:: setData([$data[, $context=null]])

		:param  array   $data: Array of view data strings, as key/value pairs
		:param  string  $context: The context to use for data escaping. 
		:returns: The Renderer, for method chaining
		:rtype: CodeIgniter\\View\\RenderableInterface.

		Sets several pieces of view data at once::

			$renderer->setData(['name'=>'George', 'position'=>'Boss']);

		Supported escape contexts: html, css, js, url, or attr or raw.
		If 'raw', no escaping will happen.

	.. php:method:: setVar($name[, $value=null[, $context=null]])

		:param  string  $name: Name of the view data variable
		:param  mixed   $value: The value of this view data
		:param  string  $context: The context to use for data escaping. 
		:returns: The Renderer, for method chaining
		:rtype: CodeIgniter\\View\\RenderableInterface.

		Sets a single piece of view data::

			$renderer->setVar('name','Joe','html');

		Supported escape contexts: html, css, js, url, attr or raw.
		If 'raw', no escaping will happen.

