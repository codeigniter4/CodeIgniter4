#####
Views
#####

.. contents::
    :local:
    :depth: 2

A view is simply a web page, or a page fragment, like a header, footer, sidebar, etc. In fact,
views can flexibly be embedded within other views (within other views, etc.) if you need
this type of hierarchy.

Views are never called directly, they must be loaded by a controller. Remember that in an MVC framework,
the Controller acts as the traffic cop, so it is responsible for fetching a particular view. If you have
not read the :doc:`Controllers </incoming/controllers>` page, you should do so before continuing.

Using the example controller you created in the controller page, let’s add a view to it.

Creating a View
===============

Using your text editor, create a file called ``BlogView.php`` and put this in it::

	<html>
        <head>
            <title>My Blog</title>
        </head>
        <body>
            <h1>Welcome to my Blog!</h1>
        </body>
	</html>

Then save the file in your **app/Views** directory.

Displaying a View
=================

To load and display a particular view file you will use the following function::

	echo view('name');

Where *name* is the name of your view file.

.. important:: If the file extension is omitted, then the views are expected to end with the .php extension.

Now, open the controller file you made earlier called ``Blog.php``, and replace the echo statement with the view function::

	<?php namespace App\Controllers;

        class Blog extends \CodeIgniter\Controller
	{
		public function index()
		{
			echo view('BlogView');
		}
	}

If you visit your site using the URL you did earlier you should see your new view. The URL was similar to this::

	example.com/index.php/blog/

.. note:: While all of the examples show echo the view directly, you can also return the output from the view, instead,
    and it will be appended to any captured output.

Loading Multiple Views
======================

CodeIgniter will intelligently handle multiple calls to ``view()`` from within a controller. If more than one
call happens they will be appended together. For example, you may wish to have a header view, a menu view, a
content view, and a footer view. That might look something like this::

	<?php namespace App\Controllers;

	class Page extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data = [
				'page_title' => 'Your title'
			];

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
When doing so you will need to include the directory name loading the view. Example::

	echo view('directory_name/file_name');

Namespaced Views
================

You can store views under a **View** directory that is namespaced, and load that view as if it was namespaced. While
PHP does not support loading non-class files from a namespace, CodeIgniter provides this feature to make it possible
to package your views together in a module-like fashion for easy re-use or distribution.

If you have ``Blog`` directory that has a PSR-4 mapping setup in the :doc:`Autoloader </concepts/autoloader>` living
under the namespace ``Example\Blog``, you could retrieve view files as if they were namespaced also. Following this
example, you could load the **BlogView** file from **/blog/views** by prepending the namespace to the view name::

    echo view('Example\Blog\Views\BlogView');

Caching Views
=============

You can cache a view with the ``view`` command by passing a ``cache`` option with the number of seconds to cache
the view for, in the third parameter::

    // Cache the view for 60 seconds
    echo view('file_name', $data, ['cache' => 60]);

By default, the view will be cached using the same name as the view file itself. You can customize this by passing
along ``cache_name`` and the cache ID you wish to use::

    // Cache the view for 60 seconds
    echo view('file_name', $data, ['cache' => 60, 'cache_name' => 'my_cached_view']);

Adding Dynamic Data to the View
===============================

Data is passed from the controller to the view by way of an array in the second parameter of the view function.
Here's an example::

	$data = [
		'title'   => 'My title',
		'heading' => 'My Heading',
		'message' => 'My Message'
	];

	echo view('blogview', $data);

Let's try it with your controller file. Open it and add this code::

	<?php namespace App\Controllers;

	class Blog extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data['title']   = "My Real Title";
			$data['heading'] = "My Real Heading";

			echo view('blogview', $data);
		}
	}

Now open your view file and change the text to variables that correspond to the array keys in your data::

	<html>
        <head>
            <title><?= $title ?></title>
        </head>
        <body>
            <h1><?= $heading ?></h1>
        </body>
	</html>

Then load the page at the URL you've been using and you should see the variables replaced.

The data passed in is only available during one call to `view`. If you call the function multiple times
in a single request, you will have to pass the desired data to each view. This keeps any data from "bleeding" into
other views, potentially causing issues. If you would prefer the data to persist, you can pass the `saveData` option
into the `$option` array in the third parameter.
::

	$data = [
		'title'   => 'My title',
		'heading' => 'My Heading',
		'message' => 'My Message'
	];

	echo view('blogview', $data, ['saveData' => true]);

Additionally, if you would like the default functionality of the view method to be that it does save the data
between calls, you can set ``$saveData`` to **true** in **app/Config/Views.php**.

Creating Loops
==============

The data array you pass to your view files is not limited to simple variables. You can pass multi dimensional
arrays, which can be looped to generate multiple rows. For example, if you pull data from your database it will
typically be in the form of a multi-dimensional array.

Here’s a simple example. Add this to your controller::

	<?php namespace App\Controllers;

	class Blog extends \CodeIgniter\Controller
	{
		public function index()
		{
			$data = [
				'todo_list' => ['Clean House', 'Call Mom', 'Run Errands'],
				'title'     => "My Real Title",
				'heading'   => "My Real Heading"
			];

			echo view('blogview', $data);
		}
	}

Now open your view file and create a loop::

	<html>
	<head>
		<title><?= $title ?></title>
	</head>
	<body>
		<h1><?= $heading ?></h1>

		<h3>My Todo List</h3>

		<ul>
		<?php foreach ($todo_list as $item):?>

			<li><?= $item ?></li>

		<?php endforeach;?>
		</ul>

	</body>
	</html>
