<?php

declare(strict_types=1);

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
use CodeIgniter\HTTP\SiteURI;
use CodeIgniter\HTTP\UserAgent;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Validation\Exceptions\ValidationException;
use Config\App;
use Config\Validation as ValidationConfig;
use PHPUnit\Framework\Attributes\BackupGlobals;
use PHPUnit\Framework\Attributes\Group;
use Psr\Log\LoggerInterface;

/**
 * Exercise our core Controller class.
 * Not a lot of business logic, so concentrate on making sure
 * we can exercise everything without blowing up :-/
 *
 * @internal
 */
#[BackupGlobals(true)]
#[Group('Others')]
final class ControllerTest extends CIUnitTestCase
{
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

        $config         = new App();
        $this->request  = new IncomingRequest($config, new SiteURI($config), null, new UserAgent());
        $this->response = new Response($config);
        $this->logger   = service('logger');
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
        } catch (RedirectException) {
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
            /**
             * @var array<string, string>
             */
            public array $signup = [
                'username' => 'required',
            ];

            /**
             * @var array<string, array<string, string>>
             */
            public array $signup_errors = [
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
        $this->assertSame('You must choose a username.', service('validation')->getError('username'));
    }

    public function testValidateWithStringRulesFoundUseMessagesParameter(): void
    {
        $validation = new class () extends ValidationConfig {
            /**
             * @var array<string, string>
             */
            public array $signup = [
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
        $this->assertSame('You must choose a username.', service('validation')->getError('username'));
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
            service('validation')->getError('password')
        );
    }

    public function testValidateDataWithCustomErrorMessage(): void
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validateData');

        $data = [
            'username' => 'a',
            'password' => '123',
        ];
        $rules = [
            'username' => 'required|min_length[3]',
            'password' => 'required|min_length[10]',
        ];
        $errors = [
            'username' => [
                'required'   => 'Please fill "{field}".',
                'min_length' => '"{field}" must be {param} letters or longer.',
            ],
        ];
        $this->assertFalse($method($data, $rules, $errors));
        $this->assertSame(
            '"username" must be 3 letters or longer.',
            service('validation')->getError('username')
        );
        $this->assertSame(
            'The password field must be at least 10 characters in length.',
            service('validation')->getError('password')
        );
    }

    public function testValidateDataWithCustomErrorMessageLabeledStyle(): void
    {
        // make sure we can instantiate one
        $this->controller = new Controller();
        $this->controller->initController($this->request, $this->response, $this->logger);

        $method = $this->getPrivateMethodInvoker($this->controller, 'validateData');

        $data = [
            'username' => 'a',
            'password' => '123',
        ];
        $rules = [
            'username' => [
                'label'  => 'Username',
                'rules'  => 'required|min_length[3]',
                'errors' => [
                    'required'   => 'Please fill "{field}".',
                    'min_length' => '"{field}" must be {param} letters or longer.',
                ],
            ],
            'password' => [
                'required|min_length[10]',
                'label' => 'Password',
                'rules' => 'required|min_length[10]',
            ],
        ];
        $this->assertFalse($method($data, $rules));
        $this->assertSame(
            '"Username" must be 3 letters or longer.',
            service('validation')->getError('username')
        );
        $this->assertSame(
            'The Password field must be at least 10 characters in length.',
            service('validation')->getError('password')
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
