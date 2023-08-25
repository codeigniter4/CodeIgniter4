<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter;

use CodeIgniter\Config\Factories;
use CodeIgniter\HTTP\Exceptions\RedirectException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Request;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\URI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Config\Services;
use Config\Validation as ValidationConfig;
use Psr\Log\LoggerInterface;

/**
 * Exercise our core Controller class.
 * Not a lot of business logic, so concentrate on making sure
 * we can exercise everything without blowing up :-/
 *
 * @backupGlobals enabled
 *
 * @internal
 *
 * @group Others
 */
final class ControllerTest extends CIUnitTestCase
{
    private App $config;
    private ?Controller $controller = null;

    /**
     * Current request.
     */
    private Request $request;

    /**
     * Current response.
     */
    private Response $response;

    private LoggerInterface $logger;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config   = new App();
        $this->request  = new IncomingRequest($this->config, new URI('https://somwhere.com'), null, new UserAgent());
        $this->response = new Response($this->config);
        $this->logger   = Services::logger();
    }

    public function testConstructor(): void
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);
        $this->assertInstanceOf(Controller::class, $this->controller);
    }

    public function testConstructorHTTPS(): void
    {
        $original = $_SERVER;
        $_SERVER  = ['HTTPS' => 'on'];

        // make sure we can instantiate one
        try {
            $this->controller = new class () extends Controller {
                protected $forceHTTPS = 1;
            };
            $this->controller->initController($this->request, $this->response, $this->logger);
        } catch (RedirectException $e) {
        }

        $this->assertInstanceOf(Controller::class, $this->controller);
        $_SERVER = $original; // restore so code coverage doesn't break
    }

    public function testCachePage(): void
    {
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'cachePage');
        $this->assertNull($method(10));
    }

    public function testValidate(): void
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        // and that we can attempt validation, with no rules
        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method([]));
    }

    public function testValidateWithStringRulesNotFound(): void
    {
        $this->expectException(ValidationException::class);

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup'));
    }

    public function testValidateWithStringRulesFoundReadMessagesFromValidationConfig(): void
    {
        $validation = new class () extends ValidationConfig {
            public $signup = [
                'username' => 'required',
            ];
            public $signup_errors = [
                'username' => [
                    'required' => 'You must choose a username.',
                ],
            ];
        };
        Factories::injectMock('config', 'Validation', $validation);

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup'));
        $this->assertSame('You must choose a username.', Services::validation()->getError('username'));
    }

    public function testValidateWithStringRulesFoundUseMessagesParameter(): void
    {
        $validation = new class () extends ValidationConfig {
            public $signup = [
                'username' => 'required',
            ];
        };
        Factories::injectMock('config', 'Validation', $validation);

        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validate');
        $this->assertFalse($method('signup', [
            'username' => [
                'required' => 'You must choose a username.',
            ],
        ]));
        $this->assertSame('You must choose a username.', Services::validation()->getError('username'));
    }

    public function testValidateData(): void
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validateData');

        $data = [
            'username' => 'mike',
            'password' => '123',
        ];
        $rule = [
            'username' => 'required',
            'password' => 'required|min_length[10]',
        ];
        $this->assertFalse($method($data, $rule));
        $this->assertSame(
            'The password field must be at least 10 characters in length.',
            Services::validation()->getError('password')
        );
    }

    public function testHelpers(): void
    {
        $this->controller = new class () extends Controller {
            protected $helpers = [
                'cookie',
                'text',
            ];
        };
        $this->controller->initController($this->request, $this->response, $this->logger);

        $this->assertInstanceOf(Controller::class, $this->controller);
    }
}
