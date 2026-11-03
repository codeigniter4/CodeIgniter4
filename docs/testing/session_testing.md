# Session Testing

Testing session behavior in your application is made simple with the ArrayHandler session driver. Unlike other session drivers, ArrayHandler does not persist data to disk, database, or external storage. This allows you to simulate session interactions safely during unit or integration tests, without affecting real session data.

Using this driver, you can set, retrieve, and assert session data entirely in memory, making your tests faster and more isolated. While in most production scenarios you would use file, database, or cache-backed sessions, ArrayHandler exists specifically to support testing workflows and prevent side effects.

## Initializing Sessions

You can initialize a session using the ArrayHandler driver for testing. This example shows how to create a session instance with a proper configuration:

```php
<?php

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Session;
use Config\Session as SessionConfig;

// Load session config
$config = config(SessionConfig::class);

// Initialize ArrayHandler with config and optional IP
$arrayHandler = new ArrayHandler($config, '127.0.0.1');

// Create session instance for testing
$testSession = new Session($arrayHandler, $config);
```

## Setting and Retrieving Data

Once initialized, you can set session values and retrieve them as usual:

```php
<?php

// Set session data
$testSession->set('framework', 'CodeIgniter4');

// Retrieve session data
echo $testSession->get('framework'); // outputs 'CodeIgniter4'

// Remove session data
$testSession->remove('framework');
```

!!! note "Note"
    Session data is stored in memory and lasts as long as the ArrayHandler object exists; after the object is destroyed (typically at the end of a request or test), the data is lost.

## Example Test Case

Here's a simple example demonstrating usage of the ArrayHandler in a PHPUnit test:

```php
<?php

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\Session;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Session as SessionConfig;

class SessionTest extends CIUnitTestCase
{
    protected Session $testSession;

    protected function setUp(): void
    {
        parent::setUp();

        // Load session configuration
        $config = new SessionConfig();

        // Initialize ArrayHandler with config
        $arrayHandler = new ArrayHandler($config, '127.0.0.1');

        // Create session instance
        $this->testSession = new Session($arrayHandler, $config);
    }

    public function testFrameworkNameInSession(): void
    {
        // Set a session value
        $this->testSession->set('framework', 'CodeIgniter');

        // Assert the value exists and is correct
        $this->assertSame('CodeIgniter', $this->testSession->get('framework'));

        // Remove the session value
        $this->testSession->remove('framework');
        $this->assertNull($this->testSession->get('framework'));
    }
}
```

## Session Assertions

### Using PHPUnit Assertions with ArrayHandler

When testing sessions directly with Session and ArrayHandler in a unit test, use standard PHPUnit assertions. `assertSessionHas()` and `assertSessionMissing()` are not available in this context because you are interacting directly with the session object, not a response object.

```php
<?php

// Set a session value
$testSession->set('framework', 'CodeIgniter4');

// Assert the state of the session using PHPUnit assertions
$this->assertSame('CodeIgniter4', $testSession->get('framework')); // Value exists

// Not empty
$this->assertNotEmpty($testSession->get('framework'));

// Remove the value and assert it's gone
$testSession->remove('framework');

// Should be null
$this->assertNull($testSession->get('framework'));
```

### Session Assertions via TestResponse

When testing controllers or HTTP responses, you can use CodeIgniter 4's session assertion helpers, such as `assertSessionHas()` and `assertSessionMissing()`, which are available on the `TestResponse` object. These helpers allow you to assert the state of the session during the HTTP request/response lifecycle. See [Session Assertions](response.md#session-assertions) for more details.

## Custom Session Values

In Feature Tests, you can provide custom session data for a single test using the `withSession()` method. This allows you to simulate session states such as logged-in users or specific roles during the request. For full details and examples, see [Setting Session Values](feature.md#setting-session-values).
