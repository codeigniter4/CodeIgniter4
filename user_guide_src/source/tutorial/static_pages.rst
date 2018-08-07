############
Static pages
############

**Note:** This tutorial assumes you've downloaded CodeIgniter and
:doc:`installed the framework <../installation/index>` in your
development environment.

The first thing you're going to do is set up a **controller** to handle
static pages. A controller is simply a class that helps delegate work.
It is the glue of your web application.

For example, when a call is made to:

	http://example.com/news/latest/10

We might imagine that there is a controller named "news". The method
being called on news would be "latest". The news method's job could be to
grab 10 news items, and render them on the page. Very often in MVC,
you'll see URL patterns that match:

	http://example.com/[controller-class]/[controller-method]/[arguments]

As URL schemes become more complex, this may change. But for now, this
is all we will need to know.

Create a file at *application/Controllers/Pages.php* with the following
code.

::

	<?php namespace App\Controllers;
	class Pages extends CodeIgniter\Controller {

		public function view($page = 'home')
		{
		}
	}

You have created a class named ``Pages``, with a view method that accepts
one argument named ``$page``. The ``Pages`` class is extending the
``CodeIgniter\Controller`` class. This means that the new Pages class can access the
methods and variables defined in the ``CodeIgniter\Controller`` class
(*system/Controller.php*).

The **controller is what will become the center of every request** to
your web application. Like any php class, you refer to
it within your controllers as ``$this``.

Now that you've created your first method, it's time to make some basic page
templates. We will be creating two "views" (page templates) that act as
our page footer and header.

Create the header at *application/Views/templates/header.php* and add
the following code:

::

	<!doctype html>
	<html>
	<head>
		<title>CodeIgniter Tutorial</title>
	</head>
	<body>

		<h1><?= $title; ?></h1>

The header contains the basic HTML code that you'll want to display
before loading the main view, together with a heading. It will also
output the ``$title`` variable, which we'll define later in the controller.
Now, create a footer at *application/Views/templates/footer.php* that
includes the following code:

::

		<em>&copy; 2016</em>
	</body>
	</html>

Adding logic to the controller
------------------------------

Earlier you set up a controller with a ``view()`` method. The method
accepts one parameter, which is the name of the page to be loaded. The
static page templates will be located in the *application/Views/pages/*
directory.

In that directory, create two files named *home.php* and *about.php*.
Within those files, type some text − anything you'd like − and save them.
If you like to be particularly un-original, try "Hello World!".

In order to load those pages, you'll have to check whether the requested
page actually exists:

::

	public function view($page = 'home')
	{
		if ( ! file_exists(APPPATH.'/Views/pages/'.$page.'.php'))
		{
		    // Whoops, we don't have a page for that!
		    throw new \CodeIgniter\Exceptions\PageNotFoundException($page);
		}

		$data['title'] = ucfirst($page); // Capitalize the first letter

		echo view('templates/header', $data);
		echo view('pages/'.$page, $data);
		echo view('templates/footer', $data);
	}

Now, when the page does exist, it is loaded, including the header and
footer, and displayed to the user. If the page doesn't exist, a "404
Page not found" error is shown.

The first line in this method checks whether the page actually exists.
PHP's native ``file_exists()`` function is used to check whether the file
is where it's expected to be. The ``PageNotFoundException`` is a CodeIgniter
exception that causes the default error page to show.

In the header template, the ``$title`` variable was used to customize the
page title. The value of title is defined in this method, but instead of
assigning the value to a variable, it is assigned to the title element
in the ``$data`` array.

The last thing that has to be done is loading the views in the order
they should be displayed. The second parameter in the ``view()`` method is
used to pass values to the view. Each value in the ``$data`` array is
assigned to a variable with the name of its key. So the value of
``$data['title']`` in the controller is equivalent to ``$title`` in the
view.

.. note:: Any files and directory names passed into the **view()** function MUST
	match the case of the actual directory and file itself or the system will
	throw errors on case-sensitive platforms.

Routing
-------

The controller is now functioning! Point your browser to
``[your-site-url]index.php/pages/view`` to see your page. When you visit
``index.php/pages/view/about`` you'll see the about page, again including
the header and footer.

Using custom routing rules, you have the power to map any URI to any
controller and method, and break free from the normal convention:
``http://example.com/[controller-class]/[controller-method]/[arguments]``

Let's do that. Open the routing file located at
*application/Config/Routes.php* and add the following two lines.
Remove all other code that adds any element in the ``$route`` items.

::

	$routes->setDefaultController('Pages/view');
	$routes->add('(:any)', 'Pages::view/$1');

CodeIgniter reads its routing rules from top to bottom and routes the
request to the first matching rule. Each rule is a regular expression
(left-side) mapped to a controller and method name separated by slashes
(right-side). When a request comes in, CodeIgniter looks for the first
match, and calls the appropriate controller and method, possibly with
arguments.

More information about routing can be found in the URI Routing
:doc:`documentation <../general/routing>`.

Here, the second rule in the ``$routes`` array matches **any** request
using the wildcard string ``(:any)``. and passes the parameter to the
``view()`` method of the ``Pages`` class.

In order for the default controller to be used, though, you have to make
sure that no other routes are defined that handle the route. By default,
the Routes file **does** have a route that handles the site root (/).
Delete the following route to make sure that the Pages controller handles
our home page::

	$routes->add('/', 'Home::index');

Now visit ``index.php/about``. Did it get routed correctly to the ``view()``
method in the pages controller? Awesome!
