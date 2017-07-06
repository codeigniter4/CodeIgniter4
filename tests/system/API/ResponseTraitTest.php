<?php namespace CodeIgniter\API;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\Format\JSONFormatter;
use CodeIgniter\Format\XMLFormatter;
use CodeIgniter\HTTP\MockIncomingRequest;
use CodeIgniter\HTTP\MockResponse;
use CodeIgniter\HTTP\URI;

class ResponseTraitTest extends \CIUnitTestCase
{

	protected $request;
	protected $response;

	/**
	 * @var Response formatter
	 */
	protected $formatter;

	public function setUp()
	{
		parent::setUp();

		$this->formatter = new JSONFormatter();
	}

	protected function makeController(array $userConfig = [], string $uri = 'http://example.com', array $userHeaders = [])
	{
		$config = [
			'baseURL' => 'http://example.com',
			'uriProtocol' => 'REQUEST_URI',
			'defaultLocale' => 'en',
			'negotiateLocale' => false,
			'supportedLocales' => ['en'],
			'CSPEnabled' => false,
			'cookiePrefix' => '',
			'cookieDomain' => '',
			'cookiePath' => '/',
			'cookieSecure' => false,
			'cookieHTTPOnly' => false,
			'proxyIPs' => []
		];

		$config = array_merge($config, $userConfig);

		$this->request = new MockIncomingRequest((object) $config, new URI($uri), null);
		$this->response = new MockResponse((object) $config);

		// Insert headers into request.
		$headers = [
			'Accept' => 'text/html'
		];
		$headers = array_merge($headers, $userHeaders);

		foreach ($headers as $key => $value)
		{
			$this->request->setHeader($key, $value);
			if (($key == 'Accept') && ! is_array($value))
				$this->response->setContentType($value);
		}

		// Create the controller class finally.
		$controller = new class($this->request, $this->response, $this->formatter)
		{

			use ResponseTrait;

			protected $request;
			protected $response;
			protected $formatter;

			public function __construct(&$request, &$response, &$formatter)
			{
				$this->request = $request;
				$this->response = $response;
				$this->formatter = $formatter;
			}
		};

		return $controller;
	}

	//--------------------------------------------------------------------

	public function testRespondSets404WithNoData()
	{
		$controller = $this->makeController();
		$controller->respond(null, null);

		$this->assertEquals(404, $this->response->getStatusCode());
		$this->assertNull($this->response->getBody());
	}

	//--------------------------------------------------------------------

	public function testRespondSetsStatusWithEmptyData()
	{
		$controller = $this->makeController();
		$controller->respond(null, 201);

		$this->assertEquals(201, $this->response->getStatusCode());
		$this->assertNull($this->response->getBody());
	}

	//--------------------------------------------------------------------

	public function testRespondSetsCorrectBodyAndStatus()
	{
		$controller = $this->makeController();
		$controller->respond('something', 201);

		$this->assertEquals(201, $this->response->getStatusCode());
		$this->assertEquals('something', $this->response->getBody());
		$this->assertTrue(strpos($this->response->getHeaderLine('Content-Type'), 'text/html') === 0);
		$this->assertEquals('Created', $this->response->getReason());
	}

	//--------------------------------------------------------------------

	public function testRespondWithCustomReason()
	{
		$controller = $this->makeController();
		$controller->respond('something', 201, 'A Custom Reason');

		$this->assertEquals(201, $this->response->getStatusCode());
		$this->assertEquals('A Custom Reason', $this->response->getReason());
	}

	public function testFailSingleMessage()
	{
		$controller = $this->makeController();

		$controller->fail('Failure to Launch', 500, 'WHAT!', 'A Custom Reason');

		// Will use the JSON formatter by default
		$expected = [
			'status' => 500,
			'error' => 'WHAT!',
			'messages' => [
				'Failure to Launch'
			]
		];

		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
		$this->assertEquals(500, $this->response->getStatusCode());
		$this->assertEquals('A Custom Reason', $this->response->getReason());
	}

	public function testCreated()
	{
		$controller = $this->makeController();
		$controller->respondCreated(['id' => 3], 'A Custom Reason');

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(201, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format(['id' => 3]), $this->response->getBody());
	}

	public function testDeleted()
	{
		$controller = $this->makeController();
		$controller->respondDeleted(['id' => 3], 'A Custom Reason');

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(200, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format(['id' => 3]), $this->response->getBody());
	}

	public function testUnauthorized()
	{
		$controller = $this->makeController();
		$controller->failUnauthorized('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 401,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(401, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testForbidden()
	{
		$controller = $this->makeController();
		$controller->failForbidden('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 403,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(403, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testNotFound()
	{
		$controller = $this->makeController();
		$controller->failNotFound('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 404,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(404, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testValidationError()
	{
		$controller = $this->makeController();
		$controller->failValidationError('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 400,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(400, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testResourceExists()
	{
		$controller = $this->makeController();
		$controller->failResourceExists('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 409,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(409, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testResourceGone()
	{
		$controller = $this->makeController();
		$controller->failResourceGone('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 410,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(410, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testTooManyRequests()
	{
		$controller = $this->makeController();
		$controller->failTooManyRequests('Nope', 'FAT CHANCE', 'A Custom Reason');

		$expected = [
			'status' => 429,
			'error' => 'FAT CHANCE',
			'messages' => [
				'Nope'
			]
		];

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(429, $this->response->getStatusCode());
		$this->assertEquals($this->formatter->format($expected), $this->response->getBody());
	}

	public function testServerError()
	{
		$controller = $this->makeController();
		$controller->failServerError('Nope.', 'FAT-CHANCE', 'A custom reason.');

		$this::assertEquals('A custom reason.', $this->response->getReason());
		$this::assertEquals(500, $this->response->getStatusCode());
		$this::assertEquals($this->formatter->format([
					'status' => 500,
					'error' => 'FAT-CHANCE',
					'messages' => [
						'Nope.'
					]
				]), $this->response->getBody());
	}

	public function testValidContentTypes()
	{
		$chars = '; charset=UTF-8';
		$goodMimes = ['text/xml', 'text/html', 'application/json', 'application/xml'];
		for ($i = 0; $i < count($goodMimes); $i ++ )
			$this->tryValidContentType($goodMimes[$i], $goodMimes[$i] . $chars);
	}

	private function tryValidContentType($mimeType, $contentType)
	{
		$original = $_SERVER;
		$_SERVER['CONTENT_TYPE'] = $mimeType;

		$controller = $this->makeController([], 'http://codeigniter.com', ['Accept' => $mimeType]);
		$this->assertEquals($mimeType, $this->request->getHeaderLine('Accept'), 'Request header...');
		$this->response->setContentType($contentType);
		$this->assertEquals($contentType, $this->response->getHeaderLine('Content-Type'), 'Response header pre-response...');

		$_SERVER = $original;
	}

	public function testValidResponses()
	{
		$chars = '; charset=UTF-8';
		$goodMimes = ['text/xml', 'text/html', 'application/json', 'application/xml'];
		for ($i = 0; $i < count($goodMimes); $i ++ )
			$this->tryValidContentType($goodMimes[$i], $goodMimes[$i] . $chars);
	}

	private function tryValidResponse($mimeType, $contentType)
	{
		$original = $_SERVER;
		$_SERVER['CONTENT_TYPE'] = $mimeType;

		$controller = $this->makeController([], 'http://codeigniter.com', ['Accept' => $mimeType]);
		$this->assertEquals($mimeType, $this->request->getHeaderLine('Accept'), 'Request header...');

		$this->assertEquals($contentType, $this->response->getHeaderLine('Content-Type'), 'Response header pre-response...');
		$this->response->setContentType($contentType);
		$controller->respond('HTML assumed');

		$this->assertEquals(200, $this->response->getStatusCode());
		$expected = 'HTML assumed';
		$this->assertEquals($expected, $this->response->getBody());

		$_SERVER = $original;
	}

	public function testXMLFormatter()
	{
		$this->formatter = new XMLFormatter();
		$controller = $this->makeController();

		$this->assertEquals('CodeIgniter\Format\XMLFormatter', get_class($this->formatter));

		$controller->respondCreated(['id' => 3], 'A Custom Reason');
		$expected = <<<EOH
<?xml version="1.0"?>
<response><id>3</id></response>

EOH;
		$this->assertEquals($expected, $this->response->getBody());
	}

	public function testNoFormatterHTML()
	{
		$this->formatter = null;
		$controller = $this->makeController();
		$controller->respondCreated('A Custom Reason');

		$this->assertEquals('A Custom Reason', $this->response->getBody());
	}

	public function testNoFormatterJSON()
	{
		$this->formatter = null;
		$controller = $this->makeController([], 'http://codeigniter.com', ['Accept' => 'application/json']);
		$controller->respondCreated(['id' => 3], 'A Custom Reason');

		$this->assertEquals('A Custom Reason', $this->response->getReason());
		$this->assertEquals(201, $this->response->getStatusCode());

		$expected = <<<EOH
{
    "id": 3
}
EOH;
		$this->assertEquals($expected, $this->response->getBody());
	}

}
