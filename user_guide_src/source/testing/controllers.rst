###################
Testing Controllers
###################

Testing your controllers is made convenient with a couple of new helper classes and traits. When testing controllers,
you can execute the code within a controller, without first running through the entire application bootstrap process.
Often times, using the `Feature Testing tools <feature.html>`_ will be simpler, but this functionality is here in
case you need it.

.. note:: Because the entire framework has not been bootstrapped, there will be times when you cannot test a controller
    this way.

.. contents::
    :local:
    :depth: 2

The Helper Trait
================

To enable Controller Testing you need to use the ``ControllerTester`` trait within your tests::

    <?php

    namespace CodeIgniter;

    use CodeIgniter\Test\ControllerTester;
    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class TestControllerA extends CIUnitTestCase
    {
        use ControllerTester, DatabaseTestTrait;
    }

Once the trait has been included, you can start setting up the environment, including the request and response classes,
the request body, URI, and more. You specify the controller to use with the ``controller()`` method, passing in the
fully qualified class name of your controller. Finally, call the ``execute()`` method with the name of the method
to run as the parameter::

    <?php

    namespace CodeIgniter;

    use CodeIgniter\Test\ControllerTester;
    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\DatabaseTestTrait;

    class TestControllerA extends CIUnitTestCase
    {
        use ControllerTester, DatabaseTestTrait;

        public function testShowCategories()
        {
            $result = $this->withURI('http://example.com/categories')
                           ->controller(\App\Controllers\ForumController::class)
                           ->execute('showCategories');

            $this->assertTrue($result->isOK());
        }
    }

Helper Methods
==============

**controller($class)**

Specifies the class name of the controller to test. The first parameter must be a fully qualified class name
(i.e., include the namespace)::

    $this->controller(\App\Controllers\ForumController::class);

**execute($method)**

Executes the specified method within the controller. The only parameter is the name of the method to run::

    $results = $this->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

This returns a new helper class that provides a number of routines for checking the response itself. See below
for details.

**withConfig($config)**

Allows you to pass in a modified version of **Config\App.php** to test with different settings::

    $config = new Config\App();
    $config->appTimezone = 'America/Chicago';

    $results = $this->withConfig($config)
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

If you do not provide one, the application's App config file will be used.

**withRequest($request)**

Allows you to provide an **IncomingRequest** instance tailored to your testing needs::

    $request = new CodeIgniter\HTTP\IncomingRequest(new Config\App(), new URI('http://example.com'));
    $request->setLocale($locale);

    $results = $this->withRequest($request)
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

If you do not provide one, a new IncomingRequest instance with the default application values will be passed
into your controller.

**withResponse($response)**

Allows you to provide a **Response** instance::

    $response = new CodeIgniter\HTTP\Response(new Config\App());

    $results = $this->withResponse($response)
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

If you do not provide one, a new Response instance with the default application values will be passed
into your controller.

**withLogger($logger)**

Allows you to provide a **Logger** instance::

    $logger = new CodeIgniter\Log\Handlers\FileHandler();

    $results = $this->withResponse($response)
                    ->withLogger($logger)
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

If you do not provide one, a new Logger instance with the default configuration values will be passed
into your controller.

**withURI($uri)**

Allows you to provide a new URI that simulates the URL the client was visiting when this controller was run.
This is helpful if you need to check URI segments within your controller. The only parameter is a string
representing a valid URI::

    $results = $this->withURI('http://example.com/forums/categories')
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

It is a good practice to always provide the URI during testing to avoid surprises.

**withBody($body)**

Allows you to provide a custom body for the request. This can be helpful when testing API controllers where
you need to set a JSON value as the body. The only parameter is a string that represents the body of the request::

    $body = json_encode(['foo' => 'bar']);

    $results = $this->withBody($body)
                    ->controller(\App\Controllers\ForumController::class)
                    ->execute('showCategories');

Checking the Response
=====================

``ControllerTester::execute()`` returns an instance of a ``TestResponse``. See `Testing Responses <response.html>`_ on
how to use this class to perform additional assertions and verification in your test cases.

Filter Testing
==============

Similar to Controller Testing, the framework provides tools to help with creating tests for
custom :doc:`Filters </incoming/filters>` and your projects use of them in routing.

The Helper Trait
----------------

Just like with the Controller Tester you need to include the ``FilterTestTrait`` in your test
cases to enable these features::

    <?php

    namespace CodeIgniter;

    use CodeIgniter\Test\CIUnitTestCase;
    use CodeIgniter\Test\FilterTestTrait;

    class FilterTestCase extends CIUnitTestCase
    {
        use FilterTestTrait;
    }

Configuration
-------------

Because of the logical overlap with Controller Testing ``FilterTestTrait`` is designed to
work together with ``ControllerTester`` should you need both on the same class.
Once the trait has been included ``CIUnitTestCase`` will detect its ``setUp`` method and
prepare all the components needed for your tests. Should you need a special configuration
you can alter any of the properties before calling the support methods:

* ``$request`` A prepared version of the default ``IncomingRequest`` service
* ``$response`` A prepared version of the default ``ResponseInterface`` service
* ``$filtersConfig`` The default ``Config\Filters`` configuration (note: discovery is handle by ``Filters`` so this will not include module aliases)
* ``$filters`` An instance of ``CodeIgniter\Filters\Filters`` using the three components above
* ``$collection`` A prepared version of ``RouteCollection`` which includes the discovery of ``Config\Routes``

The default configuration will usually be best for your testing since it most closely emulates
a "live" project, but (for example) if you wanted to simulate a filter triggering accidentally
on an unfiltered route you could add it to the Config::

    class FilterTestCase extends CIUnitTestCase
    {
        use FilterTestTrait;

		protected function testFilterFailsOnAdminRoute()
		{
			$this->filtersConfig->globals['before'] = ['admin-only-filter'];

			$this->assertHasFilters('unfiltered/route', 'before');
		}
	...

Checking Routes
---------------

The first helper method is ``getFiltersForRoute()`` which will simulate the provided route
and return a list of all Filters (by their alias) that would have run for the given position
("before" or "after"), without actually executing any controller or routing code. This has
a large performance advantage over Controller and HTTP Testing.

.. php:function:: getFiltersForRoute($route, $position)

    :param	string	$route: The URI to check
    :param	string	$position: The filter method to check, "before" or "after"
	:returns:	Aliases for each filter that would have run
	:rtype:	string[]

    Usage example::

		$result = $this->getFiltersForRoute('/', 'after'); // ['toolbar']

Calling Filter Methods
----------------------

The properties describe in Configuration are all set up to ensure maximum performance without
interfering or interference from other tests. The next helper method will return a callable
method using these properties to test your Filter code safely and check the results.

.. php:function:: getFilterCaller($filter, $position)

    :param	FilterInterface|string	$filter: The filter instance, class, or alias
    :param	string	$position: The filter method to run, "before" or "after"
	:returns:	A callable method to run the simulated Filter event
	:rtype:	Closure

    Usage example::

		protected function testUnauthorizedAccessRedirects()
		{
			$caller = $this->getFilterCaller('permission', 'before');
			$result = $caller('MayEditWidgets');

			$this->assertInstanceOf('CodeIgniter\HTTP\RedirectResponse', $result);
		}
	
	Notice how the ``Closure`` can take input parameters which are passed to your filter method.

Assertions
----------

In addition to the helper methods above ``FilterTestTrait`` also comes with some assertions
to streamline your test methods.

The **assertFilter()** method checks that the given route at position uses the filter (by its alias)::

    // Make sure users are logged in before checking their account
    $this->assertFilter('users/account', 'before', 'login');

The **assertNotFilter()** method checks that the given route at position does not use the filter (by its alias)::

    // Make sure API calls do not try to use the Debug Toolbar
    $this->assertNotFilter('api/v1/widgets', 'after', 'toolbar');

The **assertHasFilters()** method checks that the given route at position has at least one filter set::

    // Make sure that filters are enabled
    $this->assertHasFilters('filtered/route', 'after');

The **assertNotHasFilters()** method checks that the given route at position has no filters set::

    // Make sure no filters run for our static pages
    $this->assertNotHasFilters('about/contact', 'before');
