<?php

namespace Tests\Support;

use CodeIgniter\Session\Handlers\ArrayHandler;
use CodeIgniter\Session\SessionInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockSession;

/**
 * @internal
 */
final class SessionTestCase extends CIUnitTestCase
{
    /**
     * @var SessionInterface
     */
    protected $session;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockSession();
    }

    /**
     * Pre-loads the mock session driver into $this->session.
     *
     * @var string
     */
    protected function mockSession()
    {
        $config        = config('App');
        $this->session = new MockSession(new ArrayHandler($config, '0.0.0.0'), $config);
        \Config\Services::injectMock('session', $this->session);
    }
}
