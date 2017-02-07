<?php namespace CodeIgniter\API;

use CodeIgniter\API\ResponseTrait;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\MockIncomingRequest;
use CodeIgniter\HTTP\MockResponse;
use CodeIgniter\HTTP\URI;

class ResponseTraitTest extends \CIUnitTestCase
{
    protected $request;
    protected $response;


    protected function makeController(array $userConfig = [], string $uri = 'http://example.com', array $userHeaders = [])
    {
        $config = [
            'baseURL'          => 'http://example.com',
            'uriProtocol'      => 'REQUEST_URI',
            'defaultLocale'    => 'en',
            'negotiateLocale'  => false,
            'supportedLocales' => ['en'],
            'CSPEnabled'       => false,
            'cookiePrefix'     => '',
            'cookieDomain'     => '',
            'cookiePath'       => '/',
            'cookieSecure'     => false,
            'cookieHTTPOnly'   => false,
            'proxyIPs'         => []
        ];

        $config = array_merge($config, $userConfig);

        $this->request  = new MockIncomingRequest((object)$config, new URI($uri), null);
        $this->response = new MockResponse((object)$config);

        // Insert headers into request.
        $headers = [
            'Accept' => 'text/html'
        ];
        $headers = array_merge($headers, $userHeaders);

        foreach ($headers as $key => $value)
        {
            $this->request->setHeader($key, $value);
        }

        // Create the controller class finally.
        $controller = new class($this->request, $this->response)
        {
            use ResponseTrait;

            protected $request;
            protected $response;

            public function __construct($request, $response)
            {
                $this->request = $request;
                $this->response = $response;
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
            'status'  => 500,
            'error' => 'WHAT!',
            'messages' => [
                'Failure to Launch'
            ]
        ];

        $this->assertTrue(strpos($this->response->getHeaderLine('Content-Type'), 'application/json') === 0);
        $this->assertEquals(json_encode($expected), $this->response->getBody());
        $this->assertEquals(500, $this->response->getStatusCode());
        $this->assertEquals('A Custom Reason', $this->response->getReason());
    }

    public function testCreated()
    {
        $controller = $this->makeController();
        $controller->respondCreated(['id' => 3], 'A Custom Reason');

        $this->assertEquals('A Custom Reason', $this->response->getReason());
        $this->assertEquals(201, $this->response->getStatusCode());
        $this->assertEquals(json_encode(['id' => 3]), $this->response->getBody());
    }

    public function testDeleted()
    {
        $controller = $this->makeController();
        $controller->respondDeleted(['id' => 3], 'A Custom Reason');

        $this->assertEquals('A Custom Reason', $this->response->getReason());
        $this->assertEquals(200, $this->response->getStatusCode());
        $this->assertEquals(json_encode(['id' => 3]), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
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
        $this->assertEquals(json_encode($expected), $this->response->getBody());
    }

}
