Views
#####

A view is simply a web page, or a page fragment, like a header, footer, sidebar, etc. In fact,
views can flexibly be embedded within other views (within other views, etc., etc.) if you need
this type of hierarchy.

Views are never called directly, they must be loaded by a controller. Remember that in an MVC framework,
the Controller acts as the traffic cop, so it is responsible for fetching a particular view. If you have
not read the Controllers page you should do so before continuing.

Using the example controller you created in the controller page, let’s add a view to it.

Creating a View
===============

Using your text editor, create a file called blogview.php and put this in it::

	<html>
	<head>
        <title>My Blog</title>
	</head>
	<body>
        <h1>Welcome to my Blog!</h1>
	</body>
	</html>

Then save the file in your ``application/views`` directory.

Loading a View
==============

To load a particular view file you will use the following function::

	view('name');

Where _name_ is the name of your view file. 

.. important:: The .php file extension does not need to be specified, but all views are expected to end with the .php extension.

Now, open the controller file you made earlier called Blog.php, and replace the echo statement with the view function::

	class Blog extends \CodeIgniter\Controller 
	{
		public function index()
		{
			echo view('blogview');
		}
	}

If you visit your site using the URL you did earlier you should see your new view. The URL was similar to this::

	example.com/index.php/blog/

## Loading Multiple Views
CodeIgniter will intelligently handle multiple calls to ``view()`` from within a controller. If more than one
call happens they will be appended together. For example, you may wish to have a header view, a menu view, a
content view, and a footer view. That might look something like this::

	class Page extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data['page_title'] = 'Your title';
			echo view('header');
			echo view('menu');
			echo view('content', $data);
			echo view('footer');
		}
	}
	
In the example above, we are using "dynamically added data", which you will see below.

Storing Views within Sub-directories
====================================

Your view files can also be stored within sub-directories if you prefer that type of organization.
When doing so you will need to include the directory name loading the view.  Example::

	view('directory_name/file_name');

## Adding Dynamic Data to the View
Data is passed from the controller to the view by way of an array in the second parameter of the view
loading function.  Here's an example::

	$data = [
		'title' => 'My title',
		'heading' => 'My Heading',
		'message' => 'My Message'
	];
	
	echo view('blogview', $data);

Let's try it with your controller file. Open it and add this code::

	class Blog extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data['title'] = "My Real Title";
			$data['heading'] = "My Real Heading";
			
			echo view('blogview', $data);
		}
	}
	
Now open your view file and change the text to variables that correspond to the array keys in your data::

	<html>
	<head>
        <title><?php echo $title;?></title>
	</head>
	<body>
        <h1><?php echo $heading;?></h1>
	</body>
	</html>

Then load the page at the URL you've been using and you should see the variables replaced.

Direct Access To View Class
===========================

The ``view()`` function is a convenience method that grabs an instance of the View class from the DI Container,
sets the data, and renders the view. While this is often exactly what you want, you may find times where you
want to work with it more directly. In that case you can access the View class through the DI Container
with the ``renderer`` alias::

	$renderer = DI('renderer');
	
.. important:: You should only use the DI class within your controllers. If you need access to the View class
from a library, you should set that as a dependency in the constructor.

Then you can use any of the three standard methods that it provides. 

* **render('view_name', array $options)** Performs the rendering of the view and its data. The $options array is
	unused by default, but provided for third-party libraries to use when integrating with different template engines.
* **setVar('name', 'value', $context=null)** Sets a single piece of dynamic data.  $context specifies the context
	to escape for. Defaults to no escaping. Set to empty value to skip escaping.
* **setData($array, $context=null)** Takes an array of key/value pairs for dynamic data and optionally escapes it.
	$context specifies the context to escape for. Defaults to no escaping. Set to empty value to skip escaping.

The `setVar()` and `setData()` methods are chainable, allowing you to combine a number of different calls together in a chain::

	DI('renderer')->setVar('one', $one)
	              ->setVar('two', $two)
	              ->render('myView');

Escaping Data
=============

When you pass data to the ``setVar()`` and ``setData()`` functions you have the option to escape the data to protect
against cross-site scripting attacks. As the last parameter in either method, you can pass the desired context to
escape the data for. See below for context descriptions.

If you don't want the data to be escaped, you can pass `null` or `'raw'` as the final parameter to each function::

	echo $renderer->setVar('one', $one, 'raw');

If you choose not to escape data, or you are passing in an object instance, you can manually escape the data within
the view with the ``esc()`` function. The first parameter is the string to escape. The second parameter is the
context to escape the data for (see below)::

	<?= esc($object->getStat()) ?>

Escaping Contexts
=================

By default, the `esc()` and, in turn, the `setVar()` and `setData()` functions assume that the data you want to
 escape is intended to be used within standard HTML. However, if the data is intended for use in Javascript, CSS,
 or in an href attribute, you would need different escaping rules to be effective. You can pass in the name of the
 context as the second parameter. Valid contexts are 'html', 'js', 'css', 'url'::

	<a href="<?= esc($url, 'url') ?>">Some Link</a>
	
	<script>
		var siteName = '<?= esc($siteName, 'js') ?>';
	</script>
	
	<style>
		body {
			background-color: <?= esc('bgColor', 'css') ?>
		}
	</style>

Creating Loops
==============

The data array you pass to your view files is not limited to simple variables. You can pass multi dimensional
arrays, which can be looped to generate multiple rows. For example, if you pull data from your database it will
typically be in the form of a multi-dimensional array.

Here’s a simple example. Add this to your controller::

	class Blog extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data['todo_list'] = array('Clean House', 'Call Mom', 'Run Errands');

			$data['title'] = "My Real Title";
			$data['heading'] = "My Real Heading";

			echo view('blogview', $data);
		}
	}

Now open your view file and create a loop::

	<html>
	<head>
		<title><?php echo $title;?></title>
	</head>
	<body>
		<h1><?php echo $heading;?></h1>

		<h3>My Todo List</h3>

		<ul>
		<?php foreach ($todo_list as $item):?>

			<li><?php echo $item;?></li>

		<?php endforeach;?>
		</ul>

	</body>
	</html>

