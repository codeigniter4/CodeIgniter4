RESTful Resource Handling
#######################################################

.. contents::
    :local:
    :depth: 2


Resource Routes
===============

You can quickly create a handful of RESTful routes for a single resource with the ``resource()`` method. This
creates the five most common routes needed for full CRUD of a resource: create a new resource, update an existing one,
list all of that resource, show a single resource, and delete a single resource. The first parameter is the resource
name::

    $routes->resource('photos');

    // Equivalent to the following:
    $routes->get('photos',                 'Photos::index');
    $routes->get('photos/new',             'Photos::new');
    $routes->get('photos/(:segment)/edit', 'Photos::edit/$1');
    $routes->get('photos/(:segment)',      'Photos::show/$1');
    $routes->post('photos',                'Photos::create');
    $routes->delete('photos/(:segment)',   'Photos::delete/$1');
    $routes->patch('photos/(:segment)',    'Photos::update/$1');
    $routes->put('photos/(:segment)',      'Photos::update/$1');

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

