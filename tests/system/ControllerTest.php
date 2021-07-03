<?php

namespace CodeIgniter;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\Mock\MockCodeIgniter;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Psr\Log\LoggerInterface;

/**
 * Exercise our core Controller class.
 * Not a lot of business logic, so concentrate on making sure
 * we can exercise everything without blowing up :-/
 *
 * @backupGlobals enabled
 *
 * @internal
 */
final class ControllerTest extends CIUnitTestCase
{
    /**
     * @var CodeIgniter
     */
    protected $codeigniter;

    /**
     * @var Controller
     */
    protected $controller;

    /**
     * Current request.
     *
     * @var Request
     */
    protected $request;

    /**
     * Current response.
     *
     * @var Response
     */
    protected $response;
    /**
     * @var LoggerInterface
     */
    protected $logger;

    //--------------------------------------------------------------------

    protected function setUp(): void
    {
        parent::setUp();

        $this->config      = new App();
        $this->request     = new IncomingRequest($this->config, new URI('https://somwhere.com'), null, new UserAgent());
        $this->response    = new Response($this->config);
        $this->logger      = \Config\Services::logger();
        $this->codeigniter = new MockCodeIgniter($this->config);
    }

    //--------------------------------------------------------------------

    public function testConstructor()
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testConstructorHTTPS()
    {
        $original = $_SERVER;
        $_SERVER  = ['HTTPS' => 'on'];
        // make sure we can instantiate one
        $this->controller         = new class() extends Controller {
            protected $forceHTTPS = 1;
        };
        $this->controller->initController($this->request, $this->response, $this->logger);

        $this->assertInstanceOf(Controller::class, $this->controller);
        $_SERVER = $original; // restore so code coverage doesn't break
    }

    //--------------------------------------------------------------------
    public function testCachePage()
    {
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'cachePage');
        $this->assertNull($method(10));
    }

    public function testValidate()
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        // and that we can attempt validation, with no rules
        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method([]));
    }

    public function testValidateWithStringRulesNotFound()
    {
        $this->expectException(ValidationException::class);

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup'));
    }

    public function testValidateWithStringRulesFoundReadMessagesFromValidationConfig()
    {
        $validation         = config('Validation');
        $validation->signup = [
            'username' => 'required',
        ];
        $validation->signup_errors = [
            'username' => [
                'required' => 'You must choose a username.',
            ],
        ];

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup'));
        $this->assertSame('You must choose a username.', Services::validation()->getError());
    }

    public function testValidateWithStringRulesFoundUseMessagesParameter()
    {
        $validation         = config('Validation');
        $validation->signup = [
            'username' => 'required',
        ];

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup', [
            'username' => [
                'required' => 'You must choose a username.',
            ],
        ]));
        $this->assertSame('You must choose a username.', Services::validation()->getError());
    }

    //--------------------------------------------------------------------
    public function testHelpers()
    {
        $this->controller      = new class() extends Controller {
            protected $helpers = [
                'cookie',
                'text',
            ];
        };
        $this->controller->initController($this->request, $this->response, $this->logger);

        $this->assertInstanceOf(Controller::class, $this->controller);
    }
}
