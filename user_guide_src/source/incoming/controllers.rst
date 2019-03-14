###########
Controllers
###########

Controllers are the heart of your application, as they determine how HTTP requests should be handled.

.. contents::
    :local:
    :depth: 2


What is a Controller?
=====================

A Controller is simply a class file that is named in a way that it can be associated with a URI.

Consider this URI::

	example.com/index.php/blog/

In the above example, CodeIgniter would attempt to find a controller named Blog.php and load it.

**When a controller's name matches the first segment of a URI, it will be loaded.**

Let's try it: Hello World!
==========================

Let's create a simple controller so you can see it in action. Using your text editor, create a file called Blog.php,
and put the following code in it::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class Blog extends Controller
        {
		public function index()
		{
			echo 'Hello World!';
		}
	}

Then save the file to your **/app/Controllers/** directory.

.. important:: The file must be called 'Blog.php', with a capital 'B'.

Now visit your site using a URL similar to this::

	example.com/index.php/blog

If you did it right, you should see::

	Hello World!

.. important:: Class names must start with an uppercase letter.

This is valid::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class Blog extends Controller {

	}

This is **not** valid::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class blog extends Controller {

	}

Also, always make sure your controller extends the parent controller
class so that it can inherit all its methods.

Methods
=======

In the above example, the method name is ``index()``. The "index" method
is always loaded by default if the **second segment** of the URI is
empty. Another way to show your "Hello World" message would be this::

	example.com/index.php/blog/index/

**The second segment of the URI determines which method in the
controller gets called.**

Let's try it. Add a new method to your controller::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class Blog extends Controller
        {

		public function index()
		{
			echo 'Hello World!';
		}

		public function comments()
		{
			echo 'Look at this!';
		}
	}

Now load the following URL to see the comment method::

	example.com/index.php/blog/comments/

You should see your new message.

Passing URI Segments to your methods
====================================

If your URI contains more than two segments they will be passed to your
method as parameters.

For example, let's say you have a URI like this::

	example.com/index.php/products/shoes/sandals/123

Your method will be passed URI segments 3 and 4 ("sandals" and "123")::

	<?php namespace App\Controllers;

        use CodeIgniter\Controller;

	class Products extends Controller
        {

		public function shoes($sandals, $id)
		{
			echo $sandals;
			echo $id;
		}
	}

.. important:: If you are using the :doc:`URI Routing <routing>`
	feature, the segments passed to your method will be the re-routed
	ones.

Defining a Default Controller
=============================

CodeIgniter can be told to load a default controller when a URI is not
present, as will be the case when only your site root URL is requested.
To specify a default controller, open your **app/Config/Routes.php**
file and set this variable::

	$routes->setDefaultController('Blog');

Where 'Blog' is the name of the controller class you want to be used. If you now
load your main index.php file without specifying any URI segments you'll
see your "Hello World" message by default.

For more information, please refer to the "Routes Configuration Options" section of the
:doc:`URI Routing <routing>` documentation.

Remapping Method Calls
======================

As noted above, the second segment of the URI typically determines which
method in the controller gets called. CodeIgniter permits you to override
this behavior through the use of the ``_remap()`` method::

	public function _remap()
	{
		// Some code here...
	}

.. important:: If your controller contains a method named _remap(),
	it will **always** get called regardless of what your URI contains. It
	overrides the normal behavior in which the URI determines which method
	is called, allowing you to define your own method routing rules.

The overridden method call (typically the second segment of the URI) will
be passed as a parameter to the ``_remap()`` method::

	public function _remap($method)
	{
		if ($method === 'some_method')
		{
			$this->$method();
		}
		else
		{
			$this->default_method();
		}
	}

Any extra segments after the method name are passed into ``_remap()``. These parameters can be passed to the method
to emulate CodeIgniter's default behavior.

Example::

	public function _remap($method, ...$params)
	{
		$method = 'process_'.$method;
		if (method_exists($this, $method))
		{
			return $this->$method(...$params);
		}
		throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
	}

Private methods
===============

In some cases, you may want certain methods hidden from public access.
In order to achieve this, simply declare the method as being private
or protected and it will not be served via a URL request. For example,
if you were to have a method like this::

	protected function utility()
	{
		// some code
	}

Trying to access it via the URL, like this, will not work::

	example.com/index.php/blog/utility/

Organizing Your Controllers into Sub-directories
================================================

If you are building a large application you might want to hierarchically
organize or structure your controllers into sub-directories. CodeIgniter
permits you to do this.

Simply create sub-directories under the main *app/Controllers/*
one and place your controller classes within them.

.. note:: When using this feature the first segment of your URI must
	specify the folder. For example, let's say you have a controller located
	here::

		app/Controllers/products/Shoes.php

	To call the above controller your URI will look something like this::

		example.com/index.php/products/shoes/show/123

Each of your sub-directories may contain a default controller which will be
called if the URL contains *only* the sub-directory. Simply put a controller
in there that matches the name of your 'default_controller' as specified in
your *app/Config/Routes.php* file.

CodeIgniter also permits you to remap your URIs using its :doc:`URI Routing <routing>` feature.


Included Properties
===================

Every controller you create should extend ``CodeIgniter\Controller`` class.
This class provides several features that are available to all of your controllers.

**Request Object**

The application's main :doc:`Request Instance </incoming/request>` is always available
as a class property, ``$this->request``.

**Response Object**

The application's main :doc:`Response Instance </outgoing/response>` is always available
as a class property, ``$this->response``.

**Logger Object**

An instance of the :doc:`Logger <../general/logging>` class is available as a class property,
``$this->logger``.

**forceHTTPS**

A convenience method for forcing a method to be accessed via HTTPS is available within all
controllers::

	if (! $this->request->isSecure())
	{
		$this->forceHTTPS();
	}

By default, and in modern browsers that support the HTTP Strict Transport Security header, this
call should force the browser to convert non-HTTPS calls to HTTPS calls for one year. You can
modify this by passing the duration (in seconds) as the first parameter::

	if (! $this->request->isSecure())
	{
		$this->forceHTTPS(31536000);    // one year
	}

.. note:: A number of :doc:`time-based constants </general/common_functions>` are always available for you to use, including YEAR, MONTH, and more.

helpers
-------

You can define an array of helper files as a class property. Whenever the controller is loaded,
these helper files will be automatically loaded into memory so that you can use their methods anywhere
inside the controller::

	namespace App\Controllers;
        use CodeIgniter\Controller;

	class MyController extends Controller
	{
		protected $helpers = ['url', 'form'];
	}

Validating data
======================

The controller also provides a convenience method to make validating data a little simpler, ``validate()`` that
takes an array of rules to test against as the first parameter, and, optionally,
an array of custom error messages to display if the items don't pass. Internally, this uses the controller's
**$this->request** instance to get the data through. The :doc:`Validation Library docs </libraries/validation>`
has details on the format of the rules and messages arrays, as well as available rules.::

    public function updateUser(int $userID)
    {
        if (! $this->validate([
            'email' => "required|is_unique[users.email,id,{$userID}]",
            'name'  => 'required|alpha_numeric_spaces'
        ]))
        {
            return view('users/update', [
                'errors' => $this->validator->getErrors()
            ]);
        }

        // do something here if successful...
    }

If you find it simpler to keep the rules in the configuration file, you can replace the $rules array with the
name of the group, as defined in ``Config\Validation.php``::

    public function updateUser(int $userID)
    {
        if (! $this->validate('userRules'))
        {
            return view('users/update', [
                'errors' => $this->validator->getErrors()
            ]);
        }

        // do something here if successful...
    }

.. note:: Validation can also be handled automatically in the model. Where you handle validation is up to you,
            and you will find that some situations are simpler in the controller than then model, and vice versa.

That's it!
==========

That, in a nutshell, is all there is to know about controllers.
