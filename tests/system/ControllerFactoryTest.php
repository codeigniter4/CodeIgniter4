<?php namespace CodeIgniter;

use App\Controllers\Home;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Services;
use Psr\Log\LoggerInterface;

class ControllerFactoryTest extends CIUnitTestCase
{
	/**
	 * @var ControllerFactory
	 */
	private $factory;

	public function setUp(): void
	{
		parent::setUp();

		$request       = Services::request();
		$response      = Services::response();
		$logger        = Services::logger();
		$this->factory = new ControllerFactory($request, $response, $logger);
	}

	public function testConstructor()
	{
		$this->assertInstanceOf(ControllerFactory::class, $this->factory);
	}

	public function testCreate()
	{
		$controller = $this->factory->create(Home::class);

		$this->assertInstanceOf(Controller::class, $controller);
	}

	public function testControllerHasThreeProperties()
	{
		$controller = $this->factory->create(Home::class);

		$this->assertInstanceOf(RequestInterface::class, $this->getPrivateProperty($controller, 'request'));
		$this->assertInstanceOf(ResponseInterface::class, $this->getPrivateProperty($controller, 'response'));
		$this->assertInstanceOf(LoggerInterface::class, $this->getPrivateProperty($controller, 'logger'));
	}
}
