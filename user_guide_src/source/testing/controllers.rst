###################
Testing Controllers
###################

Testing your controllers is made convenient with a couple of new helper classes and traits. When testing controllers,
you can execute the code within a controller, without first running through the entire application bootstrap process.
Often times, using the `Feature Testing tools <feature.html>`_ will be simpler, but this functionality is here in
case you need it.

.. note:: Because the entire framework has not been bootstrapped, there will be times when you cannot test a controller
    this way.

The Helper Trait
================

You can use either of the base test classes, but you do need to use the ``ControllerTester`` trait
within your tests::

    <?php namespace CodeIgniter;

    use CodeIgniter\Test\ControllerTester;

    class TestControllerA extends \CIDatabaseTestCase
    {
        use ControllerTester;
    }

Once the trait has been included, you can start setting up the environment, including the request and response classes,
the request body, URI, and more. You specify the controller to use with the ``controller()`` method, passing in the
fully qualified class name of your controller. Finally, call the ``execute()`` method with the name of the method
to run as the parameter::

    <?php namespace CodeIgniter;

    use CodeIgniter\Test\ControllerTester;

    class TestControllerA extends \CIDatabaseTestCase
    {
        use ControllerTester;

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
(i.e. include the namespace)::

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
                    -> withLogger($logger)
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

When the controller is executed, a new **ControllerResponse** instance will be returned that provides a number
of helpful methods, as well as direct access to the Request and Response that were generated.

**isOK()**

This provides a simple check that the response would be considered a "successful" response. This primarily checks that
the HTTP status code is within the 200 or 300 ranges::

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

    if ($results->isOK())
    {
        . . .
    }

**isRedirect()**

Checks to see if the final response was a redirection of some sort::

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

    if ($results->isRedirect())
    {
        . . .
    }

**request()**

You can access the Request object that was generated with this method::

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

    $request = $results->request();

**response()**

This allows you access to the response object that was generated, if any::

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

    $response = $results->response();

**getBody()**

You can access the body of the response that would have been sent to the client with the **getBody()** method. This could
be generated HTML, or a JSON response, etc.::

    $results = $this->withBody($body)
                     ->controller(\App\Controllers\ForumController::class)
                     ->execute('showCategories');

    $body = $results->getBody();

Response Helper methods
-----------------------

The response you get back contains a number of helper methods to inspect the HTML output within the response. These
are useful for using within assertions in your tests.

The **see()** method checks the text on the page to see if it exists either by itself, or more specifically within
a tag, as specified by type, class, or id::

    // Check that "Hello World" is on the page
    $results->see('Hello World');
    // Check that "Hello World" is within an h1 tag
    $results->see('Hello World', 'h1');
    // Check that "Hello World" is within an element with the "notice" class
    $results->see('Hello World', '.notice');
    // Check that "Hello World" is within an element with id of "title"
    $results->see('Hellow World', '#title');

The **dontSee()** method is the exact opposite::

    // Checks that "Hello World" does NOT exist on the page
    $results->dontSee('Hello World');
    // Checks that "Hellow World" does NOT exist within any h1 tag
    $results->dontSee('Hello World', 'h1');

The **seeElement()** and **dontSeeElement()** are very similar to the previous methods, but do not look at the
values of the elements. Instead, they simply check that the elements exist on the page::

    // Check that an element with class 'notice' exists
    $results->seeElement('.notice');
    // Check that an element with id 'title' exists
    $results->seeElement('#title')
    // Verify that an element with id 'title' does NOT exist
    $results->dontSeeElement('#title');

You can use **seeLink()** to ensure that a link appears on the page with the specified text::

    // Check that a link exists with 'Upgrade Account' as the text::
    $results->seeLink('Upgrade Account');
    // Check that a link exists with 'Upgrade Account' as the text, AND a class of 'upsell'
    $results->seeLink('Upgrade Account', '.upsell');

The **seeInField()** method checks for any input tags exist with the name and value::

    // Check that an input exists named 'user' with the value 'John Snow'
    $results->seeInField('user', 'John Snow');
    // Check a multi-dimensional input
    $results->seeInField('user[name]', 'John Snow');

Finally, you can check if a checkbox exists and is checked with the **seeCheckboxIsChecked()** method::

    // Check if checkbox is checked with class of 'foo'
    $results->seeCheckboxIsChecked('.foo');
    // Check if checkbox with id of 'bar' is checked
    $results->seeCheckboxIsChecked('#bar');
