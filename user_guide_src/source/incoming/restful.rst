RESTful Resource Handling
#######################################################

.. contents::
    :local:
    :depth: 2

Representational State Transfer (REST) is an architectural style for
distributed applications, first described by Roy Fielding in his
2000 PhD dissertation, `Architectural Styles and
the Design of Network-based Software Architectures 
<https://www.ics.uci.edu/~fielding/pubs/dissertation/top.htm>`_.
That might be a bit of a dry read, and you might find Martin Fowler's
`Richardson Maturity Model <https://martinfowler.com/articles/richardsonMaturityModel.html>`_ 
a gentler introduction.

REST has been interpreted, and mis-interpreted, in more ways than most
software architectures, and it might be easier to say that the more
of Roy Fielding's principles that you embrace in an architecture, the
most "RESTful" your application would be considered.

CodeIgniter makes it easy to create RESTful APIs for your resources,
with its resource routes and `ResourceController`.

Resource Routes
============================================================

You can quickly create a handful of RESTful routes for a single resource with the ``resource()`` method. This
creates the five most common routes needed for full CRUD of a resource: create a new resource, update an existing one,
list all of that resource, show a single resource, and delete a single resource. The first parameter is the resource
name::

    $routes->resource('photos');

    // Equivalent to the following:
    $routes->get('photos/new',             'Photos::new');
    $routes->post('photos',                'Photos::create');
    $routes->get('photos',                 'Photos::index');
    $routes->get('photos/(:segment)',      'Photos::show/$1');
    $routes->get('photos/(:segment)/edit', 'Photos::edit/$1');
    $routes->put('photos/(:segment)',      'Photos::update/$1');
    $routes->patch('photos/(:segment)',    'Photos::update/$1');
    $routes->delete('photos/(:segment)',   'Photos::delete/$1');

.. note:: The ordering above is for clarity, whereas the actual order the routes are created in, in RouteCollection, ensures proper route resolution

.. important:: The routes are matched in the order they are specified, so if you have a resource photos above a get 'photos/poll' the show action's route for the resource line will be matched before the get line. To fix this, move the get line above the resource line so that it is matched first.

The second parameter accepts an array of options that can be used to modify the routes that are generated. While these
routes are geared toward API-usage, where more methods are allowed, you can pass in the 'websafe' option to have it
generate update and delete methods that work with HTML forms::

    $routes->resource('photos', ['websafe' => 1]);

    // The following equivalent routes are created:
    $routes->post('photos/(:segment)/delete', 'Photos::delete/$1');
    $routes->post('photos/(:segment)',        'Photos::update/$1');

Change the Controller Used
--------------------------

You can specify the controller that should be used by passing in the ``controller`` option with the name of
the controller that should be used::

	$routes->resource('photos', ['controller' =>'App\Gallery']);

	// Would create routes like:
	$routes->get('photos', 'App\Gallery::index');

Change the Placeholder Used
---------------------------

By default, the ``segment`` placeholder is used when a resource ID is needed. You can change this by passing
in the ``placeholder`` option with the new string to use::

	$routes->resource('photos', ['placeholder' => '(:id)']);

	// Generates routes like:
	$routes->get('photos/(:id)', 'Photos::show/$1');

Limit the Routes Made
---------------------

You can restrict the routes generated with the ``only`` option. This should be an array or comma separated list of method names that should
be created. Only routes that match one of these methods will be created. The rest will be ignored::

	$routes->resource('photos', ['only' => ['index', 'show']]);

Otherwise you can remove unused routes with the ``except`` option. This option run after ``only``::

	$routes->resource('photos', ['except' => 'new,edit']);

Valid methods are: index, show, create, update, new, edit and delete.

ResourceController
============================================================

The `ResourceController` provides a convenient starting point for your RESTful API,
with methods that correspond to the resource routes above.

Extend it, over-riding the `modelName` and `format` properties, and then
implement those methods that you want handled.::

	<?php namespace App\Controllers;

	use CodeIgniter\RESTful\ResourceController;

	class Photos extends ResourceController
	{

		protected $modelName = 'App\Models\Photos';
		protected $format    = 'json';

		public function index()
		{
			return $this->respond($this->model->findAll());
		}

                // ...
	}

The routing for this would be::

    $routes->resource('photos');

Presenter Routes
============================================================

You can quickly create a presentation controller which aligns
with a resource controller, using the ``presenter()`` method. This
creates routes for the controller methods that would return views
for your resource, or process forms submitted from those views.

It is not needed, since the presentation can be handled with
a conventional controller - it is a convenience.
Its usage is similar to the resource routing::

    $routes->presenter('photos');

    // Equivalent to the following:
    $routes->get('photos/new',                'Photos::new');
    $routes->post('photos/create',            'Photos::create');
    $routes->post('photos',                   'Photos::create');   // alias
    $routes->get('photos',                    'Photos::index');
    $routes->get('photos/show/(:segment)',    'Photos::show/$1');
    $routes->get('photos/(:segment)',         'Photos::show/$1');  // alias
    $routes->get('photos/edit/(:segment)',    'Photos::edit/$1');
    $routes->post('photos/update/(:segment)', 'Photos::update/$1');
    $routes->get('photos/remove/(:segment)',  'Photos::remove/$1');
    $routes->post('photos/delete/(:segment)', 'Photos::update/$1');

.. note:: The ordering above is for clarity, whereas the actual order the routes are created in, in RouteCollection, ensures proper route resolution

You would not have routes for `photos` for both a resource and a presenter
controller. You need to distinguish them, for instance::

    $routes->resource('api/photo');
    $routes->presenter('admin/photos');


The second parameter accepts an array of options that can be used to modify the routes that are generated. 

Change the Controller Used
--------------------------

You can specify the controller that should be used by passing in the ``controller`` option with the name of
the controller that should be used::

	$routes->presenter('photos', ['controller' =>'App\Gallery']);

	// Would create routes like:
	$routes->get('photos', 'App\Gallery::index');

Change the Placeholder Used
---------------------------

By default, the ``segment`` placeholder is used when a resource ID is needed. You can change this by passing
in the ``placeholder`` option with the new string to use::

	$routes->presenter('photos', ['placeholder' => '(:id)']);

	// Generates routes like:
	$routes->get('photos/(:id)', 'Photos::show/$1');

Limit the Routes Made
---------------------

You can restrict the routes generated with the ``only`` option. This should be an array or comma separated list of method names that should
be created. Only routes that match one of these methods will be created. The rest will be ignored::

	$routes->presenter('photos', ['only' => ['index', 'show']]);

Otherwise you can remove unused routes with the ``except`` option. This option run after ``only``::

	$routes->presenter('photos', ['except' => 'new,edit']);

Valid methods are: index, show, new, create, edit, update, remove and delete.

ResourcePresenter
============================================================

The `ResourcePresenter` provides a convenient starting point for presenting views
of your resource, and processing data from forms in those views,
with methods that align to the resource routes above.

Extend it, over-riding the `modelName` property, and then
implement those methods that you want handled.::

	<?php namespace App\Controllers;

	use CodeIgniter\RESTful\ResourcePresenter;

	class Photos extends ResourcePresenter
	{

		protected $modelName = 'App\Models\Photos';

		public function index()
		{
			return view('templates/list',$this->model->findAll());
		}

                // ...
	}

The routing for this would be::

    $routes->presenter('photos');

Presenter/Controller Comparison
=============================================================

This table presents a comparison of the default routes created by `resource()`
and `presenter()` with their corresponding Controller functions.

================ ========= ====================== ======================== ====================== ======================
Operation        Method    Controller Route       Presenter Route          Controller Function    Presenter Function
================ ========= ====================== ======================== ====================== ======================
**New**          GET       photos/new             photos/new               ``new()``              ``new()``
**Create**       POST      photos                 photos                   ``create()``           ``create()``
Create (alias)   POST                             photos/create                                   ``create()``
**List**         GET       photos                 photos                   ``index()``            ``index()``
**Show**         GET       photos/(:segment)      photos/(:segment)        ``show($id = null)``   ``show($id = null)``
Show (alias)     GET                              photos/show/(:segment)                          ``show($id = null)``
**Edit**         GET       photos/(:segment)/edit photos/edit/(:segment)   ``edit($id = null)``   ``edit($id = null)``
**Update**       PUT/PATCH photos/(:segment)                               ``update($id = null)`` 
Update (websafe) POST      photos/(:segment)      photos/update/(:segment) ``update($id = null)`` ``update($id = null)``
**Remove**       GET                              photos/remove/(:segment)                        ``remove($id = null)``
**Delete**       DELETE    photos/(:segment)                               ``delete($id = null)`` 
Delete (websafe) POST                             photos/delete/(:segment) ``delete($id = null)`` ``delete($id = null)``
================ ========= ====================== ======================== ====================== ======================
