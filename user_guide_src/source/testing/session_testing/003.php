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
